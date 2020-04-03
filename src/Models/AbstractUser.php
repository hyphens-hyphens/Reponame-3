<?php

namespace T2G\Common\Models;

use Illuminate\Notifications\Notifiable;
use Venturecraft\Revisionable\RevisionableTrait;

/**
 * Class User
 *
 * @package \T2G\Common\Models
 * @property int $id
 * @property int|null $role_id
 * @property string $name
 * @property string $email
 * @property string|null $avatar
 * @property string|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property array|null $settings
 * @property string|null $utm_source
 * @property string|null $utm_medium
 * @property string|null $utm_campaign
 * @property string|null $registered_ip
 * @property string|null $phone
 * @property string|null $raw_password
 * @property string|null $password2
 * @property string|null $raw_password2
 * @property \Illuminate\Support\Carbon|null                                                                                $created_at
 * @property \Illuminate\Support\Carbon|null                                                                                $updated_at
 * @property string|null                                                                                                    $note
 * @property mixed                                                                                                          $locale
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \TCG\Voyager\Models\Role|null                                                                             $role
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Role[]                                       $roles
 * @property-read \Illuminate\Database\Eloquent\Collection|\T2G\Common\Models\Payment[]                                     $payments
 * @property-read \Illuminate\Database\Eloquent\Collection|\T2G\Common\Models\Revision[] $advancedRevisionHistory
 * @property-read \Illuminate\Database\Eloquent\Collection|\T2G\Common\Models\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|AbstractUser whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbstractUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbstractUser whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbstractUser whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbstractUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbstractUser whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbstractUser whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbstractUser wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbstractUser wherePassword2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbstractUser wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbstractUser whereRawPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbstractUser whereRawPassword2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbstractUser whereRegisteredIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbstractUser whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbstractUser whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbstractUser whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbstractUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbstractUser whereUtmCampaign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbstractUser whereUtmMedium($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbstractUser whereUtmSource($value)
 * @mixin \Eloquent
 */
class AbstractUser extends \TCG\Voyager\Models\User
{
    use Notifiable, RevisionableTrait, AdvanceRevisionable;

    protected static $vipLevel;
    /** @var bool  */
    protected $systemUpdating = false;

    protected $table = 'users';

    protected $dontKeepRevisionOf = ['password', 'updated_at', 'created_at', 'utm_source', 'utm_medium', 'utm_campaign', 'registered_ip', 'remember_token'];
    protected $revisionCleanup = true; //Remove old revisions (works only when used with $historyLimit)
    protected $historyLimit = 300; //Maintain a maximum of 500 changes at any point of time, while cleaning up old revisions.
    protected $revisionFormattedFieldNames = [
        'name'         => 'Username',
        'email'        => 'Email',
        'phone'        => 'Phone',
        'note'         => 'Note',
        'raw_password' => 'Mật khẩu cấp 1',
        'password2'    => 'Mật khẩu cấp 2',
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'avatar', 'email', 'password', 'utm_source', 'utm_medium', 'utm_campaign', 'phone', 'raw_password', 'role_id', 'note'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'raw_password', 'password2', 'raw_password2'
    ];

    /**
     * @param \DateTime|null $from
     *
     * @return int|mixed
     */
    public function getTotalPaid(\DateTime $from = null)
    {
        $query = $this->payments()->where('status', true);
        if ($from) {
            $query->where('created_at', '>', $from);
        }

        return $query->sum('amount');
    }

    /**
     * @return int|mixed
     */
    public function getTotalDebt()
    {
        return $this->payments()
            ->where([
                'payment_type' => Payment::PAYMENT_TYPE_ADVANCE_DEBT,
                'gold_added'   => true,
            ])
            ->sum('amount');
    }

    /**
     * @return string
     */
    public function getRawPassword()
    {
        return self::decodePassword($this->raw_password);
    }

    public function displayPhone()
    {
        $phone = $this->phone ? str_pad(substr($this->phone, -4), 10, '*', STR_PAD_LEFT) : '';
        return $phone ? "<span class='text-success'>{$phone}</span>" : "<span class=\"c-red\">Chưa cập nhật</span>";
    }

    public function displayPass2()
    {
        return $this->password2 ? "<span class='text-success'>Đã cập nhật</span>" : "<span class=\"c-red\">Chưa cập nhật</span>";
    }

    /**
     * @param $password
     *
     * @return bool
     */
    public function validatePassword($password)
    {
        return $password== $this->getRawPassword();
    }

    /**
     * @return string
     */
    public function getRawPassword2()
    {
        return self::decodePassword($this->password2);
    }

    /**
     * @param $password2
     *
     * @return bool
     */
    public function validatePassword2($password2)
    {
        return $password2 == $this->getRawPassword2();
    }

    /**
     * @return bool
     */
    public function isNormalUser()
    {
        return empty($this->role_id);
    }

    public function isSystemUpdating()
    {
        return boolval($this->systemUpdating);
    }

    public function markSystemUpdatingAs(bool $status)
    {
        $this->systemUpdating = $status;
    }

    public static function decodePassword($password)
    {
        return base64_decode($password);
    }

    public static function encodePassword($password)
    {
        return base64_encode($password);
    }

    /**
     * @return bool
     */
    public function isVipMember()
    {
        return $this->getVipLevel() > 0;
    }

    /**
     * @return int|mixed|string
     */
    public function getVipLevel()
    {
        if (!is_null(self::$vipLevel)) {
            return self::$vipLevel;
        }
        $startOfVipSystem = config('t2g_common.vip_system.start_date', null);
        $totalPaid = $this->getTotalPaid($startOfVipSystem);
        $vipLevels = config('t2g_common.vip_system.levels');
        $vip = 0;
        $bonusAccs = config('t2g_common.vip_system.bonus_accs');
        $bonus = $bonusAccs[$this->name] ?? 0;
        $totalPaid += $bonus;
        foreach ($vipLevels as $level => $amount) {
            if ($totalPaid < $amount) {
                $vip = $level;
                break;
            }
        }
        self::$vipLevel = $vip;

        return self::$vipLevel;
    }
}
