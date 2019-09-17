<?php

namespace T2G\Common\Policy;

use T2G\Common\Models\AbstractUser;
use TCG\Voyager\Policies\BasePolicy;

/**
 * Class PaymentPolicy
 *
 * @package \T2G\Common\Policy
 */
class PaymentPolicy extends BasePolicy
{
    public function read(AbstractUser $user, $model)
    {
        return false;
    }
}
