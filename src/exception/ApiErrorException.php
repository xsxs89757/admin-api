<?php

namespace app\exception;

use Qifen\WebmanApiResponse\Code;
use Exception;
use Throwable;

class ApiErrorException extends Exception {
    public function __construct($message = '操作失败', $code = Code::STATUS_ERROR, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}