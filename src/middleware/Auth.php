<?php

namespace Qifen\Admin\middleware;

use Qifen\WebmanApiResponse\ApiResponse;
use Qifen\WebmanApiResponse\Code;
use Qifen\Jwt\JwtToken;
use support\Redis;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class Auth implements MiddlewareInterface {
    use ApiResponse;

    public function process(Request $request, callable $handler): Response {
        try {
            $authorization = $request->header('authorization');
            if (blank($authorization)) throw new \Exception('请求未携带Authorization信息');

            $token = trim(str_ireplace('bearer', '', $authorization));
            if (blank($token)) throw new \Exception('请求未携带token信息');

            JwtToken::init('admin')->verify($token);

            if (Redis::get(config('app.blacklist_token_prefix') . $token)) throw new \Exception('token已禁用');

            return $handler($request);
        } catch (\Exception $e) {
            return $this->errorWithCode(Code::STATUS_UNAUTHORIZED);
        }
    }
}