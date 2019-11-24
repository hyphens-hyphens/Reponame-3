<?php

namespace T2G\Common\Models;

/**
 * Class Revision
 *
 * @package \T2G\Common\Models
 * @property int $id
 * @property string $revisionable_type
 * @property int $revisionable_id
 * @property int|null $user_id
 * @property string $key
 * @property string|null $old_value
 * @property string|null $new_value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $revisionable
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereNewValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereOldValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereRevisionableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereRevisionableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereUserId($value)
 * @mixin \Eloquent
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
