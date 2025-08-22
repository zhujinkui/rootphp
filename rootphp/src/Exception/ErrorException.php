<?php

namespace Zhujinkui\Rootphp\Exception;

use Zhujinkui\Rootphp\Constants\ErrorCode;
use Hyperf\Server\Exception\ServerException;

class ErrorException extends ServerException
{
    public function __construct(int $code = 0, string $message = null, \Throwable $previous = null)
    {
        if (is_null($message)) {
            $message = ErrorCode::getMessage($code);
        }

        parent::__construct($message, $code, $previous);
    }
}