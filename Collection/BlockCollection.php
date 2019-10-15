<?php

namespace Cms\BlockBundle\Collection;

use Cms\BlockBundle\Model\Entity\BlockEntityInterface;
use Cms\BlockBundle\Service\Entity\BlockEntityManagerInterface;
use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;

/**
 * Class BlockCollection
 * @package BlockBundle\Collection
 */
class BlockCollection implements Collection, Selectable
{
    /**
     * @var ArrayCollection
     */
    private $collection;

    /**
     * A snapshot of the collection at the moment it was fetched from the database.
     *
     * @var array
     */
    private $snapshot = [];

    /**
     * The EntityManager that manages the persistence of the collection.
     *
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $blockEntityManager;

    /**
     * BlockCollection constructor.
     * @param BlockEntityManagerInterface $blockEntityManager
     * @param array $elements
     */
    public function __construct(BlockEntityManagerInterface $blockEntityManager, array $elements = [])
    {
        $this->blockEntityManager = $blockEntityManager;
        $this->collection = new ArrayCollection($elements);

        $this->takeSnapshot();
    }

    /**
     * one clone owner entity, clone collection to break reference
     */
    public function __clone()
    {
        $this->collection = clone $this->collection;
    }

    /**
     * on serialize, return only collection
     *
     * @return array
     */
    public function __sleep()
    {
        return ['collection'];
    }

    /**
     * INTERNAL:
     * Tells this collection to take a snapshot of its current state.
     *
     * @return void
     */
    public function takeSnapshot()
    {
        $this->snapshot = $this->collection->toArray();
    }

    /**
     * INTERNAL:
     * Returns the last snapshot of the elements in the collection.
     *
     * @return array The last snapshot of the elements.
     */
    public function getSnapshot()
    {
        return $this->snapshot;
    }

    /**
     * @return ArrayCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        $removed = $this->collection->remove($key);

        $this->removeBlock($removed);

        return $removed;
    }

    /**
     * {@inheritdoc}
     */
    public function removeElement($element)
    {
        $removed = $this->collection->removeElement($element);

        $this->removeBlock($removed);

        return $removed;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $this->collection->set($key, $value);

        if (!$value instanceof BlockEntityInterface) {
            $this->removeBlock($this->snapshot[$key] ?? null);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->collection->clear();

        foreach ($this->snapshot as $oldElement) {
            $this->removeBlock($oldElement);
        }
    }

    /**
     * remove by block entity manager if instance of BlockEntityInterface
     *
     * @param mixed $entity
     */
    protected function removeBlock($entity)
    {
        if ($entity instanceof BlockEntityInterface) {
            $this->blockEntityManager->remove($entity);
            $this->takeSnapshot();
        }
    }

    /**
     * @inheritdoc
     */
    public function add($element)
    {
        return $this->collection->add($element);
    }

    /**
     * @inheritdoc
     */
    public function contains($element)
    {
        return $this->collection->contains($element);
    }


    /**
     * @inheritdoc
     */
    public function isEmpty()
    {
        return $this->collection->isEmpty();
    }

    /**
     * @inheritdoc
     */
    public function containsKey($key)
    {
        return $this->collection->containsKey($key);
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        return $this->collection->get($key);
    }

    /**
     * @inheritdoc
     */
    public function getKeys()
    {
        return $this->collection->getKeys();
    }

    /**
     * @inheritdoc
     */
    public function getValues()
    {
        return $this->collection->getValues();
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return $this->collection->toArray();
    }

    /**
     * @inheritdoc
     */
    public function first()
    {
        return $this->collection->first();
    }

    /**
     * @inheritdoc
     */
    public function last()
    {
        return $this->collection->last();
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->collection->key();
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return $this->collection->current();
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        return $this->collection->next();
    }

    /**
     * @inheritdoc
     */
    public function exists(Closure $p)
    {
        return $this->collection->exists($p);
    }

    /**
     * @inheritdoc
     */
    public function filter(Closure $p)
    {
        return $this->collection->filter($p);
    }

    /**
     * @inheritdoc
     */
    public function forAll(Closure $p)
    {
        return $this->collection->forAll($p);
    }

    /**
     * @inheritdoc
     */
    public function map(Closure $func)
    {
        return $this->collection->map($p);
    }

    /**
     * @inheritdoc
     */
    public function partition(Closure $p)
    {
        return $this->collection->partition($p);
    }

    /**
     * @inheritdoc
     */
    public function indexOf($element)
    {
        return $this->collection->indexOf($element);
    }

    /**
     * @inheritdoc
     */
    public function slice($offset, $length = null)
    {
        return $this->collection->slice($offset, $length);
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return $this->collection->getIterator();
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        return $this->containsKey($offset);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        if ( ! isset($offset)) {
            $this->add($value);
            return;
        }

        $this->set($offset, $value);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return $this->collection->count();
    }

    /**
     * @inheritdoc
     */
    public function matching(Criteria $criteria)
    {
        return $this->collection->matching($criteria);
    }
}
