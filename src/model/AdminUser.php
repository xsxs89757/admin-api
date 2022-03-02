<?php

namespace Qifen\Admin\model;

use support\Model;
use Qifen\Jwt\JwtToken;
use Qifen\Admin\exception\UnauthorizedException;
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
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date) {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
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
}