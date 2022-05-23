<?php

namespace App\Http\Resources\Admin;

use App\Models\Account;
use App\Models\Webinar;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class BannedUserResource
 * @package App\Http\Resources\Admin
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
 * @property string $phone
 * @property Carbon $phone_verified_at
 * @property string $email
 * @property Carbon $email_verified_at
 * @property string $password
 * @property string $remember_token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Account $account
 * @property-read Account[] $linkedAccounts
 * @property-read Webinar[] $webinarsVisited
 */
class BannedUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'first_name' => $this->first_name,
            'email' => $this->email,
        ];
    }
}
