<?php

namespace App\Contracts\Services;


/**
 * Interface UserServiceInterface
 * @package App\Contracts\Services
 */
interface UserServiceInterface
{
    public function createByEmail(string $email);

    /**
     * @param string $name
     * @param string $email
     * @param int $id
     * @param string $phone
     * @param string $first_name
     * @param string $last_name
     * @return mixed
     */
    public function create(string $name, string $email, int $id, string $phone, string $first_name, string $last_name);

    /**
     * @param int $id
     * @return mixed
     */
    public function findByExternalId(int $id);

    /**
     * @return mixed
     */
    public function handleUserLogout();

    /**
     * @param string $email
     * @return mixed
     */
    public function findByEmail(string $email);

    /**
     * @param $webinar
     * @param string $role
     * @param string $email
     * @param string $phone
     * @param bool $requires_phone_verification
     * @param bool $requires_email_verification
     * @param string|null $defaultPassword
     * @return array
     */
    public function autologinByEmail($webinar, string $role, string $email, string $phone, bool $requires_phone_verification, bool $requires_email_verification, string $defaultPassword = null): array;
}
