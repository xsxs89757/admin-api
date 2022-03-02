<?php

namespace Qifen\admin\controller;

use Qifen\admin\model\AdminMenu;
use Qifen\admin\model\AdminUser;
use Qifen\admin\model\Roles;
use support\Request;
use Respect\Validation\Validator as v;

class RoleController extends Base {
    /**
     * 获取菜单树
     *
     * @return \support\Response
     */
    public function menuTree() {
        $tree = AdminMenu::getRoleMenu(false);

        return $this->success($tree);
    }
    /**
     * 角色列表
     *
     * @param Request $request
     * @return \support\Response
     * @throws \app\exception\ApiErrorException
     * @throws \app\exception\UnauthorizedException
     */
    public function list(Request $request) {
        $params = $this->getPageParams($request);

        $name = $request->input('name');

        $uid = AdminUser::getCurrentUserId();

        $list = Roles::with(['creator:id,username,nickname'])
            ->when($uid !== 1, function ($query) use ($uid) {
                $query->where('create_uid', $uid);
            })->when(!empty($name), function ($query) use ($name) {
                $query->where('name', 'like', '%' . $name . '%');
            })->paginate($params['limit'], ['*'], 'page', $params['page']);

        return $this->page($list);
    }

    /**
     * 角色详情
     *
     * @param Request $request
     * @param int $id
     * @return \support\Response
     * @throws \app\exception\ApiErrorException
     */
    public function detail(Request $request, int $id) {
        $detail = Roles::detail($id);

        return $this->success($detail);
    }

    /**
     * 添加角色
     *
     * @param Request $request
     * @return \support\Response
     * @throws \app\exception\ApiErrorException
     * @throws \app\exception\UnauthorizedException
     */
    public function create(Request $request) {
        $name = $request->input('name');
        $permission = $request->input('permission');

        $this->validateParams(compact('name', 'permission'), [
            'name' => v::stringType()->length(1, 255),
            'permission' => v::arrayType()->length(1),
        ]);

        Roles::store($name, $permission);

        return $this->success();
    }

    /**
     * 修改角色
     *
     * @param Request $request
     * @param int $id
     * @return \support\Response
     * @throws \Throwable
     * @throws \app\exception\ApiErrorException
     * @throws \app\exception\UnauthorizedException
     */
    public function edit(Request $request, int $id) {
        $name = $request->input('name');
        $permission = $request->input('permission');

        $this->validateParams(compact('name', 'permission'), [
            'name' => v::stringType()->length(1, 255),
            'permission' => v::arrayType()->length(1),
        ]);

        Roles::store($name, $permission, $id);

        return $this->success();
    }

    /**
     * 删除角色
     *
     * @param Request $request
     * @param int $id
     * @return \support\Response
     * @throws \app\exception\ApiErrorException
     */
    public function del(Request $request, int $id) {
        Roles::del($id);

        return $this->success();
    }
}