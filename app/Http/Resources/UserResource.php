<?php

namespace App\Http\Resources;

use App\Http\Resources\Admin\AccountResource;
use App\Models\Account;
use Bouncer;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class UserResource
 * @package App\Http\Resources
 *
 * @property integer $id
 * @property integer $status
 * @property integer $external_id
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
 * @property-read  Account $account
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'status' => $this->status,
            'verified' => (bool)$this->email_verified_at,
            'account' => new AccountResource($this->account),
            'admin' => Bouncer::is($this->resource)->a('admin'),
            'owner' => Bouncer::is($this->resource)->a('owner'),
            'moderator' => Bouncer::is($this->resource)->a('moderator'),
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'social_network_accounts' => $this->social_network_accounts,
            'phone' => $this->phone,
            'new_phone' => $this->new_phone,
            'phone_verified_at' => $this->phone_verified_at,
            'email' => $this->email,
            'new_email' => $this->new_email,
            'email_verified_at' => $this->email_verified_at,
        ];
    }
}
