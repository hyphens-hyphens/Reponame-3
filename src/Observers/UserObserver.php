<?php

namespace T2G\Common\Observers;

use T2G\Common\Models\AbstractUser;
use T2G\Common\Repository\UserRepository;
use T2G\Common\Services\JXApiClient;
use T2G\Common\Util\GameApiLog;

class UserObserver
{
    public static $updatedPasswordFlag = [];

    public static $updatedPassword2Flag = [];

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var JXApiClient
     */
    protected $gameApiClient;

    /**
     * @var GameApiLog
     */
    protected $logger;

    public function __construct()
    {
        $this->userRepository = app(UserRepository::class);
        $this->gameApiClient = app(JXApiClient::class);
        $this->logger = app(GameApiLog::class);
    }

    private function _setPasswordForGame(AbstractUser $user)
    {
        $apiResult = $this->gameApiClient->setPassword($user->name, $user->getRawPassword());
        if (!$apiResult) {
            //log error
            $this->logger->info("Cannot set password for user `{$user->name}`", [
                'api_response' => $this->gameApiClient->getLastResponse(),
                'user' => array_only($user->toArray(), ['id', 'name'])
            ]);
        }

        return true;
    }

    /**
     * @param \T2G\Common\Models\AbstractUser $user
     *
     * @return bool
     */
    private function _setPassword2ForGame(AbstractUser $user)
    {
        $apiResult = $this->gameApiClient->setSecondaryPassword($user->name, $user->getRawPassword2());
        if (!$apiResult) {
            //log error
            $this->logger->info("Cannot set password 2 for user `{$user->name}`", [
                'api_response' => $this->gameApiClient->getLastResponse(),
                'user' => array_only($user->toArray(), ['id', 'name'])
            ]);
        }

        return true;
    }

    /**
     * @param \T2G\Common\Models\AbstractUser $user
     *
     * @return bool
     */
    private function _createUserForGame(AbstractUser $user)
    {
        $apiResult = $this->gameApiClient->createUser($user->name, $user->getRawPassword());
        if (!$apiResult) {
            $this->logger->info("Cannot create account for user `{$user->name}`", [
                'api_response' => $this->gameApiClient->getLastResponse(),
                'user' => array_only($user->toArray(), ['id', 'name'])
            ]);
        }

        return true;
    }

    /**
     * Handle the user "created" event.
     *
     * @param \T2G\Common\Models\AbstractUser $user
     *
     * @return void
     */
    public function created(AbstractUser $user)
    {
        if (in_array($user->name, self::$updatedPasswordFlag)) {
            return null;
        }
        $this->_createUserForGame($user);
        self::$updatedPasswordFlag[] = $user->name;
    }

    /**
     * Handle the user "updated" event.
     *
     * @param \T2G\Common\Models\AbstractUser $user
     *
     * @return void
     */
    public function updated(AbstractUser $user)
    {
        $changes = $user->getChanges();
        $this->checkForUpdatingPassword($user, $changes);
        $this->checkForUpdatingPassword2($user, $changes);
    }

    /**
     * @param \T2G\Common\Models\AbstractUser $user
     * @param array                           $changes
     *
     * @return bool
     */
    private function checkForUpdatingPassword(AbstractUser $user, array $changes)
    {
        if (in_array($user->name, self::$updatedPasswordFlag)) {
            return false;
        }

        if (isset($changes['raw_password'])) {
            $hasher = app(\Illuminate\Support\Facades\Hash::class);
            $newPassword = $hasher->make(base64_decode($changes['raw_password']));
            $this->_setPasswordForGame($user);
            self::$updatedPasswordFlag[] = $user->name;
            if ($newPassword != $user->getAuthPassword()) {
                $user->password = $newPassword;
                $user->save();
            }
        }
    }

    /**
     * @param \T2G\Common\Models\AbstractUser $user
     * @param array                           $changes
     *
     * @return bool
     */
    private function checkForUpdatingPassword2(AbstractUser $user, array $changes)
    {
        if (in_array($user->name, self::$updatedPassword2Flag)) {
            return false;
        }
        if (isset($changes['password2'])) {
            $this->_setPassword2ForGame($user);
            self::$updatedPassword2Flag[] = $user->name;
        }
    }
}
