<?php

namespace Cms\BlockBundle\Model\Entity;

abstract class AbstractEntity implements BlockEntityInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * on clone, clone sub objects
     */
    public function __clone() {
        foreach($this as $key => $val) {
            if (is_object($val)) {
                $this->{$key} = clone $val;
            } else if (is_array($val)) {
                $this->{$key} = unserialize(serialize($val));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id): BlockEntityInterface
    {
        $this->id = $id;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name): BlockEntityInterface
    {
        $this->name = $name;
        return $this;
    }
}