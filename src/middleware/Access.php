<?php

namespace Qifen\Admin\middleware;

use Qifen\Admin\model\AdminUser;
use Qifen\Admin\model\Roles;
use Qifen\Route\Route;
use Qifen\WebmanApiResponse\ApiResponse;
use Qifen\WebmanApiResponse\Code;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class Access implements MiddlewareInterface {
    use ApiResponse;

    public function process(Request $request, callable $handler): Response {
        $uid = AdminUser::getCurrentUserId();

        if ($uid == 1) return $handler($request);

        $callback = $request->controller . '@' . $request->action;
        $rule = Route::getRule($callback);
        $rules = Roles::getCurrentUserRules();

        if (!in_array($rule, $rules)) {
            return $this->errorWithCode(Code::STATUS_PERMISSION_DENIED);
        }

        return $handler($request);
    }
}