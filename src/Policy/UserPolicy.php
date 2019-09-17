<?php

namespace T2G\Common\Policy;

use T2G\Common\Models\AbstractUser;
use TCG\Voyager\Contracts\User;

/**
 * Class UserPolicy
 *
 * @package \T2G\Common\Policy
 */
class UserPolicy extends \TCG\Voyager\Policies\BasePolicy
{
    /**
     * @param \T2G\Common\Models\AbstractUser $user
     * @param                                 $model
     *
     * @return bool
     */
    public function read(AbstractUser $user, $model)
    {
        return false;
    }

    /**
     * Determine if the given model can be edited by the user.
     *
     * @param \T2G\Common\Models\AbstractUser $user
     * @param                                 $model
     *
     * @return bool
     */
    public function edit(AbstractUser $user, $model)
    {
        /** @var AbstractUser $model */
        // Does this record belong to the current user?
        $current = $user->id === $model->id;

        return $current
            || (
                ( $user->hasRole('admin') && !$model->hasRole('admin'))
                || ($user->hasRole('dev') && !$model->hasRole('dev') && !$model->hasRole('admin'))
                || ($user->hasRole('operator') && $model->isNormalUser())
                && $this->checkPermission($user, $model, 'edit')
            );
    }

    /**
     * Determine if the given user can change a user a role.
     *
     * @param \T2G\Common\Models\AbstractUser $user
     * @param                                 $model
     *
     * @return bool
     */
    public function editRoles(AbstractUser $user, $model)
    {
        // Does this record belong to the current user?
        $current = $user->id === $model->id;

        return (
            $user->hasRole('admin')
            && (
                $user->id == 1 || $current || !$model->hasRole('admin')
                // admin cannot change role of other admin
            )
        );
    }

    /**
     * Determine if the given user can change a user a role.
     *
     * @param \TCG\Voyager\Contracts\User $user
     * @param  $model
     *
     * @return bool
     */
    public function editPassword(User $user, $model)
    {
        // Does this record belong to the current user?
        $current = $user->id === $model->id;

        return (
            $current
            || (
                $user->hasRole('admin')
                && (
                    $user->id == 1 || !$model->hasRole('admin')
                )
                // super admin can change role of other admin
                // admin cannot change role of other admin
            )
            || (
                $user->role_id && $user->role_id != 3 && empty($model->role_id)
                // other role despite `marketer` can change normal user password
            )
        );
    }

}
