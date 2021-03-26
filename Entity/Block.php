<?php

namespace Cms\BlockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * group all blocks data to one table
 *
 * @ORM\Table(name="block")
 * @ORM\Entity(repositoryClass="Cms\BlockBundle\Repository\BlockRepository")
 */
class Block
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="string")
     */
    protected $id;

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
     *
     * @return Block
     */
    public function setId(string $id): self
    {
        $this->id = $id;

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
     *
     * @return Block
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }
}