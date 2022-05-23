<?php

namespace App\Models;

use App\Auth\MustVerifyPhone as MustVerifyPhoneTrait;
use App\Contracts\Auth\Middleware\MustVerifyPhone;
use Carbon\Carbon;
use Illion\UserSync\Interfaces\SynchronizableInterface;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Silber\Bouncer\Database\HasRolesAndAbilities;

/**
 * Class User
 * @package App\Models
 *
 * @property integer $id
 * @property integer $account_id
 * @property integer $external_id
 * @property integer $status
 * @property boolean $account_suspended
 * @property string $timezone
 * @property string $name
 * @property string $first_name
 * @property string $last_name
 * @property string $social_network_accounts
 * @property string $phone
 * @property string $new_phone
 * @property Carbon $phone_verified_at
 * @property string $email
 * @property string $new_email
 * @property Carbon $email_verified_at
 * @property string $password
 * @property string $remember_token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Account $account
 * @property-read Account[] $linkedAccounts
 * @property-read Webinar[] $webinarsVisited
 * @property-read Order[] $orders
 */
class User extends Authenticatable implements MustVerifyEmail, MustVerifyPhone, SynchronizableInterface
{
    use Notifiable;
    use MustVerifyPhoneTrait;
    use MustVerifyEmailTrait;
    use HasRolesAndAbilities;

    const ROLE_ADMIN = 1;

    const STATUS_ACTIVE = 1;
    const STATUS_CHAT_BLOCKED = 2;
    const STATUS_BANNED = 3;

    const PERMISSION_ABILITY_VIEW_WEBINAR_OWNED_BY = 'view-webinar-owned-by';
    const PERMISSION_ABILITY_VIEW_ROOMS = 'view-rooms';
    const PERMISSION_ABILITY_VIEW_ROOM = 'view-room';
    const PERMISSION_ABILITY_UPDATE = 'update';

    const OWNER_MUST_VERIFY_PHONE = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'external_id',
        'status',
        'timezone',
        'name',
        'first_name',
        'last_name',
        'social_network_accounts',
        'phone',
        'new_phone',
        'phone_verified_at',
        'email',
        'new_email',
        'email_verified_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
    ];

    protected $dates = [
        'email_confirmed_at',
        'phone_confirmed_at',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return BelongsToMany
     */
    public function linkedAccounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class)->withPivot(['role_id'])->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function webinarsVisited(): BelongsToMany
    {
        return $this->belongsToMany(Webinar::class)->withPivot([
            'is_user_online',
            'joined_at',
            'left_at'
        ])->withTimestamps();
    }

    /**
     * @return string
     */
    public function getAuthIdentifierName(): string
    {
        return 'external_id';
    }

    /**
     * @return string
     */
    public function getSyncIdField(): string
    {
        return $this->getAuthIdentifierName();
    }

    /**
     * Determines whether user must have phone verified
     *
     * @return bool
     */
    public function requiresPhoneVerification(): bool
    {
        return $this->isAn('owner');
    }

    /**
     * The channels the user receives notification broadcasts on.
     *
     * @return string
     */
    public function receivesBroadcastNotificationsOn()
    {
        return 'user.' . $this->id;
    }

    public function getAccountSuspendedAttribute()
    {
        if (!$this->isAn('owner')) {
            return null;
        }

        return !app(\App\Contracts\Repositories\AccountRepository::class)->setModel($this->linkedAccounts()->first())->canCreateWebinar();
    }

    public function getAccountAttribute()
    {
        if ($this->isA('owner', 'moderator')) {
            return $this->linkedAccounts()->first();
        }

        return null;
    }
}
