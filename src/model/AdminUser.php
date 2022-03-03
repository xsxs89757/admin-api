<?php

namespace Qifen\WebmanAdmin\model;

use support\Db;
use support\Model;
use Qifen\Jwt\JwtToken;
use Qifen\helpers\Bcrypt;
use Qifen\Casbin\Permission;
use Qifen\WebmanAdmin\exception\ApiErrorException;
use Qifen\WebmanAdmin\exception\UnauthorizedException;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class AdminUser extends Model {
    const TOKEN_KEY = 'id';

    const STATUS_DISABLED = 0;
    const STATUS_AVAILABLE = 1;

    const SUPER_ADMIN_ROLE = [
        ['roleName' => '超级管理员', 'value' => '超级管理员'],
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_user';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var string[]
     */
    protected $hidden = ['password'];

    /**
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date) {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    /**
     * 创建人信息
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator() {
        return $this->belongsTo(AdminUser::class, 'create_uid');
    }

    /**
     * 所拥有的角色
     *
     * @return MorphToMany
     */
    public function roles(): MorphToMany {
        return $this->morphToMany(
            Roles::class,
            'model',
            'admin_model_has_roles',
            'model_id',
            'role_id'
        );
    }

    /**
     * 获取当前登录用户ID
     *
     * @param bool $safeMode
     * @return int
     * @throws UnauthorizedException
     */
    public static function getCurrentUserId(bool $safeMode = true) {
        try {
            return JwtToken::init('admin')->getCurrentId();
        } catch (\Exception $exception) {
            if ($safeMode) throw new UnauthorizedException();

            return 0;
        }
    }

    /**
     * 获取当前登录用户
     *
     * @return mixed
     * @throws UnauthorizedException
     */
    public static function getCurrentUser() {
        $id = self::getCurrentUserId();
        return self::find($id);
    }

    /**
     * 保存
     *
     * @param array $data
     * @param int $id
     * @return void
     * @throws ApiErrorException
     */
    public static function store(array $data, int $id = 0) {
        Db::beginTransaction();

        try {
            $roles = $data['roles'];

            if (!Roles::checkUserRoles($roles)) {
                throw new ApiErrorException('越级赋权');
            }

            if (self::where('id', '<>', $id)->where('username', $data['username'])->count() > 0) {
                throw new ApiErrorException('用户名已存在');
            }

            $uid = AdminUser::getCurrentUserId();

            if ($id > 0) {
                if ($id == 1) {
                    throw new ApiErrorException();
                }

                $sql = self::where('create_uid', $uid)->findOrFail($id);
            } else {
                $sql = new self;

                $sql->create_uid = $uid;
                $sql->create_time = time();
                $sql->last_login_time = time();
                $sql->last_login_ip = '127.0.0.1';
            }

            $sql->status = $data['status'] ? 1 : 0;
            $sql->username = $data['username'];
            $sql->nickname = $data['nickname'];

            if (!empty($data['password'])) {
                $sql->password = Bcrypt::hashPassword($data['password']);
            }

            $sql->save();

            // 赋权
            if ($id > 0) {
                Permission::deleteUser(Roles::USER_PREFIX . $id);
                AdminModelHasRoles::where('model_id', $sql->id)->where('model_type', 'app\admin\model\AdminUser')->delete();
            }

            foreach ($roles as $role) {
                Permission::addRoleForUser(Roles::USER_PREFIX . $sql->id, Roles::ROLE_PREFIX . $role);
                AdminModelHasRoles::create(['role_id' => $role, 'model_type' => 'app\admin\model\AdminUser', 'model_id' => $sql->id]);
            }

            Db::commit();
        } catch (ApiErrorException $exception) {
            Db::rollBack();
            throw $exception;
        } catch (\Exception $exception) {
            Db::rollBack();
            throw new ApiErrorException();
        }
    }

    /**
     * 删除用户
     *
     * @param int $id
     * @return void
     * @throws ApiErrorException
     */
    public static function del(int $id) {
        Db::beginTransaction();

        try {
            $uid = AdminUser::getCurrentUserId();

            $user = self::when($uid !== 1, function ($query) use ($uid) {
                $query->where('create_uid', $uid);
            })->findOrFail($id);

            $user->delete();

            Permission::deleteUser(Roles::USER_PREFIX . $id);
            AdminModelHasRoles::where('model_id', $id)->where('model_type', 'app\admin\model\AdminUser')->delete();

            Db::commit();
        } catch (\Exception $exception) {
            Db::rollBack();
            throw new ApiErrorException();
        }
    }
}