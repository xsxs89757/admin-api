<?php

namespace Qifen\WebmanAdmin\middleware;

use Qifen\WebmanAdmin\model\AdminActionLog;
use Qifen\WebmanAdmin\model\AdminUser;
use Qifen\Route\Route;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class ActionLog implements MiddlewareInterface {
    public function process(Request $request, callable $handler): Response {
        $response = $handler($request);

        $method = $request->method();

        if ($method !== 'GET') {
            $log = new AdminActionLog();

            $user = '';
            $uid = AdminUser::getCurrentUserId(false);

            if ($uid > 0) {
                $info = AdminUser::find($uid);
                $user = serialize(['userid' => $info->id, 'username' => $info->username, 'nickname' => $info->nickname]);
            }

            $status = 0;

            try {
                $body = $response->rawBody();

                $data = json_decode($body, true);

                if (isset($data['code']) && $data['code'] == 0) {
                    $status = 1;
                }
            } catch (\Exception $e) {}

            $content = $request->all();

            $log->status = $status;
            $log->path = $request->path();
            $log->path_name = Route::getRule($request->controller . '@' . $request->action);
            $log->ip = $request->getRealIp();
            $log->method = $method;
            $log->action_uid = $uid;
            $log->action_user = $user;
            $log->content = empty($content) ? '' : serialize($content);

            $log->save();
        }

        return $response;
    }
}