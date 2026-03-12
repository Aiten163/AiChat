<?php

namespace App\Exceptions;

use Exception;

class ChatAccessDeniedException extends Exception
{
    protected $message = 'Доступ к чату запрещен';
    protected $code = 403;
}
