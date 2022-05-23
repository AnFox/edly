<?php

namespace App\Exceptions;

use Exception;

class AttemptToRegisterAlreadyRegisteredUser extends Exception
{
    public function __construct()
    {
        $this->message = 'Вы уже зарегистрированы в одном из наших приложений. Войдите используя ваш пароль.';
        $this->code = ExceptionCode::ATTEMPT_TO_REGISTER_ALREADY_REGISTERED_USER;
    }
}
