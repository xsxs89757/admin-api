<?php

namespace Qifen\Admin;

use Webman\Route;
use Qifen\Route\Route as AdminRoute;
use Qifen\Admin\middleware\Auth;
use Qifen\Admin\middleware\ActionLog;
use Qifen\Admin\middleware\Access;
use Qifen\Admin\middleware\Cors;

class StartAdminRoute{
    public static function start(){
        /**
         * 跨域
         */
        Route::options('/admin/{path:.+}' , function() {return response('');})->middleware([
            Cors::class
        ]);

        /**
         * 后台登录
         */
        AdminRoute::post('/admin/login', 'Qifen\Admin\controller\AuthController@login')->middleware([
            ActionLog::class,
        ])->rule('login');

        /**
         * 后台公共路由
         */
        AdminRoute::group('/admin', function () {
            AdminRoute::get('/logout', 'Qifen\Admin\controller\AuthController@logout')->rule('logout');
            AdminRoute::get('/me', 'Qifen\Admin\controller\AuthController@me')->rule('userInfo');
            AdminRoute::get('/menu', 'Qifen\Admin\controller\AuthController@menu')->rule('menu');
            AdminRoute::get('/permissions', 'Qifen\Admin\controller\AuthController@permissions')->rule('permissions');
        })->middleware([
            Auth::class,
            ActionLog::class,
        ]);

        /**
         * 后台权限路由
         */
        AdminRoute::group('/admin', function () {
            AdminRoute::get('/menus','Qifen\Admin\controller\RoleController@menuTree')->rule('adminUsers.role');
            AdminRoute::get('/role','Qifen\Admin\controller\RoleController@list')->rule('adminUsers.role');
            AdminRoute::get('/role/{id:\d+}','Qifen\Admin\controller\RoleController@detail')->rule('adminUsers.role');
            AdminRoute::post('/role','Qifen\Admin\controller\RoleController@create')->rule('adminUsers.role.addRole');
            AdminRoute::put('/role/{id:\d+}','Qifen\Admin\controller\RoleController@edit')->rule('adminUsers.role.editRole');
            AdminRoute::delete('/role/{id:\d+}','Qifen\Admin\controller\RoleController@del')->rule('adminUsers.role.deleteRole');
        })->middleware([
            Auth::class,
            ActionLog::class,
            Access::class,
        ]);
    }
} 

