<?php

namespace BlockBundle\Model\Voter;

use BlockBundle\Model\Type\BlockTypeInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

interface BlockVoterInterface extends VoterInterface
{
    /**
     * check if current user can view block
     *
     * @return bool
     */
    public function canView(): bool;

    /**
     * check if current user can create block
     *
     * @return bool
     */
    public function canCreate(): bool;

    /**
     * check if current user can edit block
     *
     * @return bool
     */
    public function canEdit(): bool;

    /**
     * check if current user can sortable block
     *
     * @return bool
     */
    public function canSortable(): bool;

    /**
     * check if current user can delete block
     *
     * @return bool
     */
    public function canDelete(): bool;

    /**
     * @return BlockTypeInterface
     */
    public function getBlock(): BlockTypeInterface;

    /**
     * @param BlockTypeInterface $block
     * @return mixed
     */
    public function setBlock(BlockTypeInterface $block);
}