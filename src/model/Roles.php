<?php

namespace Qifen\admin\model;

use app\exception\ApiErrorException;
use Qifen\Casbin\Permission;
use support\Model;

class Roles extends Model {
    const ROLE_PREFIX = 'adminRole_';
    const USER_PREFIX = 'adminUser_';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date) {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    /**
     * 创建人
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator() {
        return $this->belongsTo(AdminUser::class, 'create_uid');
    }

    /**
     * 获取当前登录用户权限
     *
     * @return array
     * @throws \app\exception\UnauthorizedException
     */
    public static function getCurrentUserRules() {
        $uid = AdminUser::getCurrentUserId();

        if ($uid == 1) {
            return AdminMenu::pluck('key')->toArray();
        }

        $rules = [];
        $list = Permission::getImplicitPermissionsForUser(self::USER_PREFIX . $uid);

        foreach ($list as $item) {
            if ($item[1] === 'admin') {
                $rules[] = $item[2];
            }
        }

        return array_unique($rules);
    }

    /**
     * 检查用户是否有权限
     *
     * @param array $permissions
     * @return bool
     * @throws \app\exception\UnauthorizedException
     */
    public static function checkUserPermissions(array $permissions) {
        if (empty($permissions)) return true;

        $flag = true;
        $allPermissions = self::getCurrentUserRules();

        foreach ($permissions as $permission) {
            if (!in_array($permission, $allPermissions)) {
                $flag = false;
                break;
            }
        }

        return $flag;
    }

    /**
     * 角色详情
     *
     * @param int $id
     * @return array
     * @throws ApiErrorException
     */
    public static function detail(int $id) {
        try {
            $role = self::findOrFail($id);

            $list = [];
            $permissions = Permission::getImplicitPermissionsForUser(self::ROLE_PREFIX . $id);

            foreach ($permissions as $permission) {
                if ($permission[1] === 'admin') {
                    $list[] = $permission[2];
                }
            }

            $role->permission = $list;

            return $role->toArray();
        } catch (\Exception $exception) {
            throw new ApiErrorException();
        }
    }

    /**
     * 保存
     *
     * @param string $name
     * @param array $permissions
     * @param int $id
     * @return void
     * @throws ApiErrorException
     * @throws \Throwable
     */
    public static function store(string $name, array $permissions, int $id = 0) {
        try {
            if (!self::checkUserPermissions($permissions)) {
                throw new ApiErrorException('越级赋权');
            }

            if ($id == 0) {
                $role = new self;
            } else {
                $role = self::findOrFail($id);
            }

            $role->name = $name;
            $role->create_uid = AdminUser::getCurrentUserId();

            $role->saveOrFail();

            if ($id > 0) {
                Permission::deleteRole(self::ROLE_PREFIX . $role->id);
            }

            foreach ($permissions as $permission) {
                Permission::addPermissionForUser(self::ROLE_PREFIX . $role->id, 'admin', $permission);
            }
        } catch (\Exception $exception) {
            throw new ApiErrorException();
        }
    }

    /**
     * 删除
     *
     * @param int $id
     * @return void
     * @throws ApiErrorException
     */
    public static function del(int $id) {
        try {
            $count = AdminModelHasRoles::where('role_id', $id)->where('model_type', 'app\admin\model\AdminUser')->count();

            if ($count > 0) {
                throw new ApiErrorException('当前角色已分配给用户，无法删除');
            }

            $uid = AdminUser::getCurrentUserId();

            $role = self::findOrFail($id);

            if ($role->create_uid != $uid && $uid != 1) {
                throw new ApiErrorException('只能删除自己创建的角色');
            }

            $role->delete();

            Permission::deleteRole(self::ROLE_PREFIX . $id);
        } catch (ApiErrorException $exception) {
            throw new ApiErrorException($exception->getMessage());
        } catch (\Exception $exception) {
            throw new ApiErrorException();
        }
    }
}