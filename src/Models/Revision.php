<?php

namespace T2G\Common\Models;

/**
 * Class Revision
 *
 * @package \T2G\Common\Models
 */
class Revision extends \Venturecraft\Revisionable\Revision
{
    protected function displayField($value)
    {
        switch ($this->key) {
            case 'raw_password':
            case 'password2':
                return AbstractUser::decodePassword($value);
        }

        return $value;
    }

    public function oldValue()
    {
        return $this->displayField(parent::oldValue());
    }

    public function newValue()
    {
        return $this->displayField(parent::newValue());
    }
}
