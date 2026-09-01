<?php

declare(strict_types=1);

namespace app\db\Model;

use nova\plugin\orm\object\Model;

class QuickLinkModel extends Model
{
    public int $user_id = 0;
    public string $url = '';
    public string $title = '';
    public int $sort = 0;

    public function getUnique(): array
    {
        return [['user_id', 'url']];
    }
}
