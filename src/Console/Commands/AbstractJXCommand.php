<?php

namespace T2G\Common\Console\Commands;

use Illuminate\Console\Command;
use T2G\Common\Observers\UserObserver;
use T2G\Common\Repository\UserRepository;
use T2G\Common\Services\JXApiClient;
use TCG\Voyager\Models\Setting;

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

    protected function saveLastRunSetting($settingKey, $lastRunTimestamp)
    {
        $lastRunSetting = Setting::where('key', $settingKey)->first();
        if (!$lastRunSetting) {
            $lastRunSetting = new Setting();
            $lastRunSetting->key = $settingKey;
            $lastRunSetting->display_name = $settingKey;
            $lastRunSetting->type = 'number';
            $lastRunSetting->group = 'System';
        }
        $lastRunSetting->value = $lastRunTimestamp;
        $lastRunSetting->save();
    }

    /**
     * @param $rawHwid
     *
     * @return string
     */
    protected function getFilteredHwid($rawHwid)
    {
        $hwidPieces = explode('-', $rawHwid);
        $newHwidArray = ['XXX', 'XXX', $hwidPieces[2], 'XXX', $hwidPieces[4], 'XXX', $hwidPieces[6], 'XXX'];

        return implode('-', $newHwidArray);
    }
}
