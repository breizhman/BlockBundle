<?php

namespace Cms\BlockBundle\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityRepository;

/**
 * Class BlockRepository
 *
 * @package Cms\BlockBundle\Repository
 */
class BlockRepository extends EntityRepository
{
    /**
     * @param string $id
     *
     * @return array
     */
    public function findDataById(string $id): array
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $result = $queryBuilder
            ->select('data')
            ->from('block')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->execute()
            ->fetchColumn(0);

        $data = json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        return $data;
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function isExist(string $id): bool
    {
        return !empty($this->findDataById($id));
    }

    /**
     * @param array $data
     */
    public function insert(array $data): void
    {
        if (empty($data['blockId'])) {
            return;
        }

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $queryBuilder
            ->insert('block')
            ->values([
                'id'   => ':id',
                'data' => ':data',
            ])
            ->setParameter('id', $data['blockId'])
            ->setParameter('data', json_encode($data))
            ->execute();
    }

    /**
     * @param array $data
     */
    public function update(array $data): void
    {
        if (empty($data['blockId'])) {
            return;
        }

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $queryBuilder
            ->update('block')
            ->set('data', ':data')
            ->where('id = :id')
            ->setParameter('id', $data['blockId'])
            ->setParameter('data', json_encode($data))
            ->execute();
    }

    /**
     * @param array $data
     */
    public function delete(array $data): void
    {
        if (empty($data['blockId'])) {
            return;
        }

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $queryBuilder
            ->delete('block')
            ->where('id = :id')
            ->setParameter('id', $data['blockId'])
            ->execute();
    }
}