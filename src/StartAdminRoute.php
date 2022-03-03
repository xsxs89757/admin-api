<?php

namespace Qifen\WebmanAdmin;

use Webman\Route;
use Qifen\Route\Route as AdminRoute;
use Qifen\WebmanAdmin\middleware\Auth;
use Qifen\WebmanAdmin\middleware\ActionLog;
use Qifen\WebmanAdmin\middleware\Access;
use Qifen\WebmanAdmin\middleware\Cors;

class StartAdminRoute{
    public static function start() {
        /**
         * 跨域
         */
        Route::options('/admin/{path:.+}' , function() {return response('');})->middleware([
            Cors::class
        ]);

        /**
         * 后台登录
         */
        AdminRoute::post('/admin/login', 'Qifen\WebmanAdmin\controller\AuthController@login')->middleware([
            ActionLog::class,
        ])->rule('login');

        /**
         * 后台公共路由
         */
        AdminRoute::group('/admin', function () {
            AdminRoute::get('/logout', 'Qifen\WebmanAdmin\controller\AuthController@logout')->rule('logout');
            AdminRoute::get('/me', 'Qifen\WebmanAdmin\controller\AuthController@me')->rule('userInfo');
            AdminRoute::get('/menu', 'Qifen\WebmanAdmin\controller\AuthController@menu')->rule('menu');
            AdminRoute::get('/permissions', 'Qifen\WebmanAdmin\controller\AuthController@permissions')->rule('permissions');
            AdminRoute::post('/reset_password','Qifen\WebmanAdmin\controller\AuthController@resetPassword')->rule('resetPassword');
            AdminRoute::get('/menu_cache_clear','Qifen\WebmanAdmin\controller\UserController@clearMenuCache')->rule('clearMenuCache');
        })->middleware([
            Auth::class,
            ActionLog::class,
        ]);

        /**
         * 后台权限路由
         */
        AdminRoute::group('/admin', function () {
            AdminRoute::get('/menus','Qifen\WebmanAdmin\controller\RoleController@menuTree')->rule('adminUsers.role');
            AdminRoute::get('/role','Qifen\WebmanAdmin\controller\RoleController@list')->rule('adminUsers.role');
            AdminRoute::get('/role/{id:\d+}','Qifen\WebmanAdmin\controller\RoleController@detail')->rule('adminUsers.role');
            AdminRoute::post('/role','Qifen\WebmanAdmin\controller\RoleController@create')->rule('adminUsers.role.addRole');
            AdminRoute::put('/role/{id:\d+}','Qifen\WebmanAdmin\controller\RoleController@edit')->rule('adminUsers.role.editRole');
            AdminRoute::delete('/role/{id:\d+}','Qifen\WebmanAdmin\controller\RoleController@del')->rule('adminUsers.role.deleteRole');

            AdminRoute::get('/roles','Qifen\WebmanAdmin\controller\RoleController@allRoles')->rule('adminUsers.list');
            AdminRoute::get('/user','Qifen\WebmanAdmin\controller\UserController@list')->rule('adminUsers.list');
            AdminRoute::post('/user','Qifen\WebmanAdmin\controller\UserController@create')->rule('adminUsers.list.addAdminUser');
            AdminRoute::put('/user/{id:\d+}','Qifen\WebmanAdmin\controller\UserController@edit')->rule('adminUsers.list.editAdminUser');
            AdminRoute::delete('/user/{id:\d+}','Qifen\WebmanAdmin\controller\UserController@del')->rule('adminUsers.list.deleteAdminUser');

            AdminRoute::get('/logs','Qifen\WebmanAdmin\controller\UserController@logs')->rule('adminControllerLogs');
            AdminRoute::post('/logs_clear','Qifen\WebmanAdmin\controller\UserController@clearLogs')->rule('adminControllerLogs.clearAdminLogs');
        })->middleware([
            Auth::class,
            ActionLog::class,
            Access::class,
        ]);
    }
}
