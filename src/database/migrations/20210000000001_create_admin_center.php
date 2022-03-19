<?php
declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class CreateAdminCenter extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        // 用户
        $adminUser = $this->table('admin_user', ['signed' => false]);
        $adminUser->addColumn('username', 'string', ['comment' => '用户名'])
            ->addColumn('status', 'integer', ['default' => 1, 'limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'comment' => '状态：0-禁用，1-启用'])
            ->addColumn('password', 'string', ['limit' => 60, 'comment' => '密码'])
            ->addColumn('avatar', 'string', ['default' => '', 'comment' => '头像'])
            ->addColumn('nickname', 'string', ['limit' => 50, 'comment' => '昵称'])
            ->addColumn('create_uid', 'integer', ['signed' => false, 'comment' => '创建者'])
            ->addColumn('create_time', 'integer', ['signed' => false, 'comment' => '创建时间'])
            ->addColumn('last_login_time', 'integer', ['signed' => false, 'comment' => '最后登录时间'])
            ->addColumn('last_login_ip', 'string', ['limit' => 15, 'comment' => '最后登录ip'])
            ->addTimestamps()
            ->addIndex(['username'], ['unique' => true])
            ->create();

        // 操作日志
        $adminUserLog = $this->table('admin_action_log', ['signed' => false]);
        $adminUserLog->addColumn('status', 'integer', ['default' => 1, 'limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'comment' => '是否成功'])
            ->addColumn('path', 'string', ['comment' => '路由'])
            ->addColumn('path_name', 'string', ['comment' => '路由名称'])
            ->addColumn('ip', 'string', ['limit' => 15, 'comment' => '操作ip'])
            ->addColumn('method', 'string', ['limit' => 20, 'comment' => '请求方式'])
            ->addColumn('action_uid', 'integer', ['comment' => '操作人', 'signed' => false])
            ->addColumn('action_user', 'text', ['comment' => '操作人详细资料'])
            ->addColumn('content', 'text', ['comment' => '操作内容'])
            ->addTimestamps()
            ->create();

        // 菜单
        $adminMenu = $this->table('admin_menu', ['signed' => false]);
        $adminMenu->addColumn('key', 'string', ['comment' => '菜单索引'])
            ->addColumn('name', 'string', ['comment' => '菜单名称'])
            ->addColumn('introduction', 'string', ['default' => '', 'comment' => '菜单中文名称'])
            ->addColumn('redirect', 'string', ['default' => '', 'comment' => '路由重定向'])
            ->addColumn('hidden', 'integer', ['default' => 0, 'limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'comment' => '是否隐藏边栏：0-否，1-是'])
            ->addColumn('always_show', 'integer', ['default' => 0, 'limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'comment' => '是否一直显示根路由:0-否，1-是'])
            ->addColumn('no_cache', 'integer', ['default' => 0, 'limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'comment' => '是否缓存：0-否，1-是'])
            ->addColumn('breadcrumb', 'integer', ['default' => 0, 'limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'comment' => '是否在面包屑中隐藏：0-否，1-是'])
            ->addColumn('is_external_link', 'integer', ['default' => 0, 'limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'comment' => '是否外联：0-否，1-是'])
            ->addColumn('affix', 'integer', ['default' => 0, 'limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'comment' => '是否附加到导航：0-否，1-是'])
            ->addColumn('external_link', 'string', ['default' => '', 'comment' => '外联地址'])
            ->addColumn('icon', 'string', ['default' => '', 'comment' => '图标'])
            ->addColumn('pid', 'integer', ['default' => 0, 'comment' => '上级菜单'])
            ->addColumn('params', 'string', ['default' => '', 'comment' => '附加参数'])
            ->addColumn('sort', 'integer', ['default' => 1, 'signed' => false, 'comment' => '排序'])
            ->addTimestamps()
            ->addIndex(['key'], ['unique' => true])
            ->create();

        // 角色
        $roles = $this->table('roles', ['signed' => false]);
        $roles->addColumn('name', 'string', ['comment' => '角色名称'])
            ->addColumn('create_uid', 'integer', ['signed' => false, 'comment' => '角色创建用户ID'])
            ->addTimestamps()
            ->create();

        // 用户权限关联
        $adminUserRoles = $this->table('admin_model_has_roles', ['id' => false, 'primary_key' => ['role_id', 'model_type', 'model_id']]);
        $adminUserRoles->addColumn('role_id', 'integer', ['signed' => false, 'comment' => '角色id'])
            ->addColumn('model_type', 'string', ['comment' => '关联模型'])
            ->addColumn('model_id', 'string', ['comment' => '模型id'])
            ->addIndex(['model_id', 'model_type'])
            ->create();

        // 系统设置
        $systemConfig = $this->table('system_config', ['signed' => false]);
        $systemConfig->addColumn('name', 'string', ['comment' => '配置名称'])
            ->addColumn('type', 'string', ['comment' => '配置类型'])
            ->addColumn('title', 'string', ['comment' => '配置说明'])
            ->addColumn('group', 'string', ['comment' => '配置分组'])
            ->addColumn('value', 'text', ['null' => true, 'comment' => '配置值 - 文本,枚举,编辑器'])
            ->addColumn('extra', 'string', ['null' => true, 'comment' => '配置值 - 数字,字符串,密码'])
            ->addColumn('remark', 'string', ['null' => true, 'comment' => '配置说明'])
            ->addColumn('status', 'integer', ['default' => 1, 'limit' => MysqlAdapter::INT_TINY, 'signed' => false,  'comment' => '状态'])
            ->addColumn('sort', 'integer', ['default' => 1, 'limit' => MysqlAdapter::INT_TINY, 'signed' => false,  'comment' => '排序'])
            ->addTimestamps()
            ->addIndex(['type'])
            ->addIndex(['group'])
            ->addIndex(['name'], ['unique' => true])
            ->create();
    }
}
