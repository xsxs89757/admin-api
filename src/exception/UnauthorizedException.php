<?php

namespace Qifen\WebmanAdmin\exception;

use Qifen\WebmanApiResponse\Code;
use Exception;
use Throwable;

class UnauthorizedException extends Exception {
    public function __construct($message = '', $code = Code::STATUS_UNAUTHORIZED, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}