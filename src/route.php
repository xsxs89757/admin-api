<?php

namespace Qifen\admin;

use Webman\Route;
use Qifen\Route\Route as AdminRoute;
use Qifen\admin\middleware\Auth;
use Qifen\admin\middleware\ActionLog;
use Qifen\admin\middleware\Access;

class StartAdminRoute{
    public static function start(){
        /**
         * 跨域
         */
        Route::options('/admin/{path:.+}' , function() {return response('');})->middleware([
            app\admin\middleware\Cors::class
        ]);

        /**
         * 后台登录
         */
        AdminRoute::post('/admin/login', 'Qifen\admin\controller\AuthController@login')->middleware([
            ActionLog::class,
        ])->rule('login');

        /**
         * 后台公共路由
         */
        AdminRoute::group('/admin', function () {
            AdminRoute::get('/logout', 'Qifen\admin\controller\AuthController@logout')->rule('logout');
            AdminRoute::get('/me', 'Qifen\admin\controller\AuthController@me')->rule('userInfo');
            AdminRoute::get('/menu', 'Qifen\admin\controller\AuthController@menu')->rule('menu');
            AdminRoute::get('/permissions', 'Qifen\admin\controller\AuthController@permissions')->rule('permissions');
        })->middleware([
            Auth::class,
            ActionLog::class,
        ]);

        /**
         * 后台权限路由
         */
        AdminRoute::group('/admin', function () {
            AdminRoute::get('/menus','Qifen\admin\controller\RoleController@menuTree')->rule('adminUsers.role');
            AdminRoute::get('/role','Qifen\admin\controller\RoleController@list')->rule('adminUsers.role');
            AdminRoute::get('/role/{id:\d+}','Qifen\admin\controller\RoleController@detail')->rule('adminUsers.role');
            AdminRoute::post('/role','Qifen\admin\controller\RoleController@create')->rule('adminUsers.role.addRole');
            AdminRoute::put('/role/{id:\d+}','Qifen\admin\controller\RoleController@edit')->rule('adminUsers.role.editRole');
            AdminRoute::delete('/role/{id:\d+}','Qifen\admin\controller\RoleController@del')->rule('adminUsers.role.deleteRole');
        })->middleware([
            Auth::class,
            ActionLog::class,
            Access::class,
        ]);
    }
} 

