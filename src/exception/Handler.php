<?php

namespace app\exception;

use Qifen\WebmanApiResponse\ApiResponse;
use Throwable;
use Webman\Exception\ExceptionHandler;
use Webman\Http\Request;
use Webman\Http\Response;

class Handler extends ExceptionHandler {
    use ApiResponse;

    public $dontReport = [
        ApiErrorException::class,
        UnauthorizedException::class,
    ];

    /**
     * 返回
     *
     * @param Request $request
     * @param Throwable $exception
     * @return Response
     */
    public function render(Request $request, Throwable $exception): Response {
        if ($exception instanceof ApiErrorException || $exception instanceof UnauthorizedException) {
            return $this->errorWithCode($exception->getCode(), $exception->getMessage());
        }

        return parent::render($request, $exception);
    }
}