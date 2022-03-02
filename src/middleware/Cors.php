<?php

namespace Qifen\WebmanAdmin\middleware;

use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class Cors implements MiddlewareInterface {
    public function process(Request $request, callable $next): Response {
        $response = $request->method() == 'OPTIONS' ? response('') : $next($request);

        $response->withHeaders([
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET,POST,PUT,DELETE,OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type,Authorization,X-Requested-With,Accept,Origin'
        ]);

        return $response;
    }
}