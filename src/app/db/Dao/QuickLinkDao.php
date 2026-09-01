<?php

declare(strict_types=1);

namespace app\db\Dao;

use app\db\Model\QuickLinkModel;
use nova\plugin\orm\object\Dao;

class QuickLinkDao extends Dao
{
    public function listByUser(int $userId): array
    {
        return $this->select()
            ->where(['user_id' => $userId])
            ->orderBy('sort', 'ASC')
            ->orderBy('id', 'ASC')
            ->commit();
    }

    public function findById(int $id): ?QuickLinkModel
    {
        /** @var QuickLinkModel|null $row */
        $row = $this->find(null, ['id' => $id]);

        return $row;
    }
}
