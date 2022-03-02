<?php

namespace Qifen\Admin\controller;

use Qifen\Admin\model\AdminMenu;
use Qifen\Admin\model\AdminUser;
use Qifen\Admin\model\Roles;
use app\exception\ApiErrorException;
use Qifen\helpers\Bcrypt;
use Qifen\Jwt\JwtToken;
use support\Redis;
use support\Request;
use Respect\Validation\Validator as v;

class AuthController extends Base {
    /**
     * 登录
     *
     * @param Request $request
     * @return \support\Response
     * @throws ApiErrorException
     */
    public function login(Request $request) {
        $username = $request->input('username');
        $password = $request->input('password');

        $this->validateParams(compact('username', 'password'), [
            'username' => v::stringType()->length(1, 255),
            'password' => v::stringType()->length(1, 255),
        ]);

        $user = AdminUser::where('username', $username)->first();

        if (!$user) throw new ApiErrorException('用户名或密码错误');
        if ($user->status == AdminUser::STATUS_DISABLED) throw new ApiErrorException('用户已禁用');

        try {
            if (Bcrypt::checkPassword($password, $user->password)) {
                $tokenData = JwtToken::init('admin')->generateToken([AdminUser::TOKEN_KEY => $user->id]);

                // 记录登录信息
                $user->last_login_ip = $request->getRealIp();
                $user->last_login_time = time();
                $user->save();

                return $this->success($tokenData);
            }
        } catch (\Exception $e) {}

        return $this->error('用户名或密码错误');
    }

    /**
     * 登出
     *
     * @param Request $request
     * @return \support\Response
     */
    public function logout(Request $request) {
        $authorization = $request->header('authorization');

        $token = trim(str_ireplace('bearer', '', $authorization));

        $data = JwtToken::init('admin')->verify($token);

        $extend = (array)$data['extend'];

        Redis::setEx(config('app.blacklist_token_prefix') . $token, $data['exp'] - time(), $extend[AdminUser::TOKEN_KEY]);

        return $this->success();
    }

    /**
     * 获取当前登录用户信息
     *
     * @return \support\Response
     * @throws \app\exception\UnauthorizedException
     */
    public function me() {
        $user = AdminUser::getCurrentUser();

        $roles = [];

        if ($user->id == 1) {
            $roles = AdminUser::SUPER_ADMIN_ROLE;
        } else {
            $roleList = $user->roles->pluck('name');

            foreach ($roleList as $item) {
                $roles[] = ['roleName' => $item, 'value' => $item];
            }
        }

        $data = [
            'userId' => $user->id,
            'avatar' => $user->avatar,
            'username' => $user->username,
            'realName' => $user->nickname,
            'roles' => $roles,
        ];

        return $this->success($data);
    }

    /**
     * 获取当前用户菜单
     *
     * @return \support\Response
     * @throws \app\exception\UnauthorizedException
     */
    public function menu() {
        $menus = AdminMenu::getRoleMenu();

        return $this->success($menus);
    }

    /**
     * 获取当前用户权限
     *
     * @return \support\Response
     * @throws \app\exception\UnauthorizedException
     */
    public function permissions() {
        $permissions = Roles::getCurrentUserRules();

        return $this->success($permissions);
    }
}