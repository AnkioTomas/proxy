<?php

declare(strict_types=1);

namespace app\plugins;

use nova\plugin\login\db\Model\UserModel;

class Panel extends BasePlugin
{

    public function authorize(?UserModel $user): bool
    {
        return $user instanceof UserModel && $user->hasPermission('all');
    }

    public function login(UserModel $user): array
    {
        if (!$this->authorize($user)) {
            return [];
        }

        return [];
    }
}