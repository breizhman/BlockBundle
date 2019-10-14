<?php

namespace BlockBundle\Service;

/**
 * Class BlockVoterAttributes
 * @package BlockBundle\Service
 */
class BlockVoterAttributes
{
    const VIEW     = 'block_view';
    const CREATE   = 'block_create';
    const EDIT     = 'block_edit';
    const SORTABLE = 'block_sortable';
    const DELETE   = 'block_delete';

    /**
     * @return array
     */
    static public function getAvailableAttributes(): array
    {
        return [
            static::VIEW,
            static::CREATE,
            static::EDIT,
            static::SORTABLE,
            static::DELETE,
        ];
    }
}