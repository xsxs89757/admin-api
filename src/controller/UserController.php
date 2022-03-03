<?php

namespace Qifen\WebmanAdmin\controller;

use support\Request;
use Qifen\WebmanAdmin\model\AdminMenu;
use Qifen\WebmanAdmin\model\AdminUser;
use Qifen\WebmanAdmin\model\AdminActionLog;
use Respect\Validation\Validator as v;

class UserController extends Base {
    /**
     * 用户列表
     * 
     * @param Request $request
     * @return \support\Response
     * @throws \Qifen\WebmanAdmin\exception\ApiErrorException
     */
    public function list(Request $request) {
        $id = $request->input('id');
        $username = $request->input('username');
        $nickname = $request->input('nickname');

        $params = $this->getPageParams($request);

        $sql = AdminUser::with(['creator', 'roles'])
            ->where('id', '>', 1)
            ->when(!empty($id), function ($query) use ($id) {
                $query->where('id', $id);
            })
            ->when(!empty($username), function ($query) use ($username) {
                $query->where('username', 'like', '%' . $username . '%');
            })
            ->when(!empty($nickname), function ($query) use ($nickname) {
                $query->where('nickname', 'like', '%' . $nickname . '%');
            });

        $total = $sql->count();
        $list = $sql->offset($params['offset'])
            ->limit($params['limit'])
            ->get();

        return $this->success(compact('list', 'total'));
    }

    /**
     * 添加用户
     * 
     * @param Request $request
     * @return \support\Response
     * @throws \Qifen\WebmanAdmin\exception\ApiErrorException
     */
    public function create(Request $request) {
        $data = $this->validateParams($request->all(), [
            'roles' => v::arrayType()->length(1),
            'status' => v::boolType(),
            'username' => v::stringType()->length(1, 255),
            'nickname' => v::stringType()->length(1, 255),
            'password' => v::stringType()->length(8, 20),
        ]);

        AdminUser::store($data);

        return $this->success();
    }

    /**
     * 编辑用户
     * 
     * @param Request $request
     * @param int $id
     * @return \support\Response
     * @throws \Qifen\WebmanAdmin\exception\ApiErrorException
     */
    public function edit(Request $request, int $id) {
        $this->validateIdWithResponse($id);

        $data = $this->validateParams($request->all(), [
            'roles' => v::arrayType()->length(1),
            'status' => v::boolType(),
            'username' => v::stringType()->length(1, 255),
            'nickname' => v::stringType()->length(1, 255),
            'password' => v::nullable(v::stringType()->length(8, 20)),
        ]);

        AdminUser::store($data, $id);

        return $this->success();
    }

    /**
     * 删除用户
     * 
     * @param Request $request
     * @param int $id
     * @return \support\Response
     * @throws \Qifen\WebmanAdmin\exception\ApiErrorException
     */
    public function del(Request $request, int $id) {
        if (!$this->validateId($id) || $id == 1) {
            return $this->errorParam();
        }

        AdminUser::del($id);

        return $this->success();
    }

    /**
     * 操作日志列表
     * 
     * @param Request $request
     * @return \support\Response
     * @throws \Qifen\WebmanAdmin\exception\ApiErrorException
     * @throws \Qifen\WebmanAdmin\exception\UnauthorizedException
     */
    public function logs(Request $request) {
        $uid = $request->input('uid');

        $params = $this->getPageParams($request);

        $ids = [];
        $currentUid = AdminUser::getCurrentUserId();
        $isSuperAdmin = $currentUid === 1;
        if (!$isSuperAdmin) {
            $ids = AdminUser::where('create_uid', $currentUid)
                ->orWhere('id', $currentUid)
                ->pluck('id')
                ->toArray();
        }

        $sql = AdminActionLog::with(['operator'])
            ->when(!$isSuperAdmin, function ($query) use ($ids) {
                $query->whereIn('action_uid', $ids);
            })
            ->when(!empty($uid), function ($query) use ($uid) {
                $query->where('action_uid', $uid);
            });

        $total = $sql->count();
        $list = $sql->offset($params['offset'])
            ->limit($params['limit'])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($item) {
                if (empty($item->operator)) {
                    $item->operator = unserialize($item->action_user);
                }

                $item->content = empty($item->content) ? '' : unserialize($item->content);
                $item->operator_name = $item->action_uid == 0 ? '未登录' : $item->operator['nickname'];
                $item->full_path = implode('/', AdminMenu::getFullPathName($item->path_name));

                return $item;
            });

        return $this->success(compact('list', 'total'));
    }

    /**
     * 清空操作日志
     * 
     * @return \support\Response
     * @throws \Qifen\WebmanAdmin\exception\UnauthorizedException
     */
    public function clearLogs() {
        if (AdminUser::getCurrentUserId() === 1) {
            AdminActionLog::truncate();
            return $this->success();
        }

        return $this->error();
    }

    /**
     * 清空菜单
     * 
     * @return \support\Response
     */
    public function clearMenuCache() {
        AdminMenu::clearCache();

        return $this->success();
    }
}