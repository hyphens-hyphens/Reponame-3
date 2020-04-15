<?php

namespace T2G\Common\Observers;

use T2G\Common\Models\AbstractUser;
use T2G\Common\Repository\UserRepository;

class UserObserver
{
    public static $isDisabled = false;

    public static $updatedPasswordFlag = [];

    public static $updatedPassword2Flag = [];

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var \T2G\Common\Services\GameApiClientInterface
     */
    protected $gameApiClient;

    public function __construct()
    {
        $this->userRepository = app(UserRepository::class);
        $this->gameApiClient = getGameApiClient();
    }

    private function _setPasswordForGame(AbstractUser $user)
    {
        return $this->gameApiClient->setPassword($user->name, $user->getRawPassword());
    }

    /**
     * @param \T2G\Common\Models\AbstractUser $user
     *
     * @return bool
     */
    private function _setPassword2ForGame(AbstractUser $user)
    {
        return $this->gameApiClient->setSecondaryPassword($user->name, $user->getRawPassword2());
    }

    /**
     * @param \T2G\Common\Models\AbstractUser $user
     *
     * @return bool
     */
    private function _createUserForGame(AbstractUser $user)
    {
        return $this->gameApiClient->createUser($user->name, $user->getRawPassword());
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
        if (self::isDisabled() || $user->isSystemUpdating() || in_array($user->name, self::$updatedPasswordFlag)) {
            return;
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
        if (self::isDisabled() || $user->isSystemUpdating()) {
            return;
        }

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
            $this->_setPasswordForGame($user);
            self::$updatedPasswordFlag[] = $user->name;
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

            return true;
        }

        return false;
    }

    /**
    * @return bool
    */
    public static function isDisabled(): bool
    {
        return self::$isDisabled;
    }

    /**
     * @param bool $isDisabled
     */
    public static function setIsDisabled(bool $isDisabled): void {
        self::$isDisabled = $isDisabled;
    }
}
