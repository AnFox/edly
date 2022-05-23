<?php

namespace App\Observers;

use App\Models\User;
use App\Notifications\EmailVerified;
use App\Notifications\MustVerifyEmail;
use App\Notifications\MustVerifyPhone;
use App\Notifications\PhoneVerified;

/**
 * Class UserObserver
 * @package App\Observers
 */
class UserObserver
{
    /**
     * Handle the user "created" event.
     *
     * @param User $user
     * @return void
     */
    public function created(User $user)
    {
        //
    }

    /**
     * Handle the user "updated" event.
     *
     * @param User $user
     * @return void
     */
    public function updated(User $user)
    {
        if ($user->isDirty('email_verified_at') && $user->email_verified_at) {
            $user->notify(new EmailVerified());
        }

        if ($user->isDirty('phone_verified_at') && $user->phone_verified_at) {
            $user->notify(new PhoneVerified());
        }

        if ($user->isDirty('email_verified_at') && !$user->email_verified_at) {
            $user->fresh()->notify(new MustVerifyEmail());
        }

        if (User::OWNER_MUST_VERIFY_PHONE) {
            if ($user->isDirty('phone_verified_at') && !$user->phone_verified_at) {
                $user->notify(new MustVerifyPhone());
            }
        }
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param User $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the user "restored" event.
     *
     * @param User $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @param User $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
