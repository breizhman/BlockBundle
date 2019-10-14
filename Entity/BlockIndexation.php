<?php

namespace BlockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * group all blocks data to one table
 *
 * @ORM\Table(name="block_indexation")
 * @ORM\Entity(repositoryClass="BlockBundle\Entity\BlockIndexationRepository")
 */
class BlockIndexation implements BlockIndexationInterface
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="string")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="name", type="string")
     */
    protected $name;

    /**
     * @var array
     * @ORM\Column(name="data", type="json")
     */
    protected $data = [];

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return BlockIndexationInterface
     */
    public function setId(string $id): BlockIndexationInterface
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return BlockIndexationInterface
     */
    public function setName(string $name): BlockIndexationInterface
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return BlockIndexationInterface
     */
    public function setData(array $data): BlockIndexationInterface
    {
        $this->data = $data;
        return $this;
    }
}