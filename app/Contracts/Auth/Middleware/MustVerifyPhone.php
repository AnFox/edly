<?php

namespace App\Contracts\Auth\Middleware;

interface MustVerifyPhone
{
    /**
     * Determines whether user must have phone verified
     *
     * @return bool
     */
    public function requiresPhoneVerification(): bool;

    /**
     * Determine if the user has verified their phone address.
     *
     * @return bool
     */
    public function hasVerifiedPhone();

    /**
     * Mark the given user's phone as verified.
     *
     * @return bool
     */
    public function markPhoneAsVerified();

    /**
     * Send the phone verification notification.
     *
     * @return void
     */
    public function sendPhoneVerificationNotification();

    /**
     * Get the phone address that should be used for verification.
     *
     * @return string
     */
    public function getPhoneForVerification();
}
