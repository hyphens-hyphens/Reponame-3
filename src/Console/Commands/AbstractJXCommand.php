<?php

namespace T2G\Common\Console\Commands;

use Illuminate\Console\Command;
use T2G\Common\Observers\UserObserver;
use T2G\Common\Repository\UserRepository;
use T2G\Common\Services\JXApiClient;

/**
 * Class AbstractJXCommand
 *
 * @package \T2G\Common\Console\Commands
 */
abstract class AbstractJXCommand extends Command
{
    protected function banUser($username, $bannedPassword = 'keoxe_PM_bikhoa')
    {
        $api = app(JXApiClient::class);
        $userRepository = app(UserRepository::class);
        /** @var \T2G\Common\Models\AbstractUser $user */
        $user = $userRepository->findUserByUsername($username);
        if ($user->getRawPassword() != $bannedPassword) {
            $userRepository->updatePassword($user, $bannedPassword);
            UserObserver::setIsDisabled(true);
            $api->setPassword($username, $bannedPassword);
        }
    }
}
