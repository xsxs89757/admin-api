<?php


use Phinx\Seed\AbstractSeed;

class RuleSeeder extends AbstractSeed {
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run() {
        $adminMenu = $this->table('admin_menu');

        $data = [
            'name' => 'system',
            'key' => 'system',
            'introduction' => '系统设置',
            'redirect' => '/system/config',
            'icon' => 'ant-design:setting-filled',
            'pid' => 0,
            'sort' => 99,
        ];

        $adminMenu->insert($data)->saveData();

        $systemId = $this->getAdapter()->getConnection()->lastInsertId();

        $data = [
            'name' => 'config',
            'key' => 'system.config',
            'introduction' => '系统设置',
            'pid' => $systemId,
        ];

        $adminMenu->insert($data)->saveData();

        $configId = $this->getAdapter()->getConnection()->lastInsertId();

        $data = [
            [
                'name' => 'store',
                'key' => 'system.config.store',
                'introduction' => '保存',
                'hidden' => 1,
                'pid' => $configId,
            ]
        ];
        $adminMenu->insert($data)->save();

        $data = [
            'name' => 'set',
            'key' => 'system.configSet',
            'introduction' => '配置管理',
            'pid' => $systemId,
        ];

        $adminMenu->insert($data)->saveData();

        $data = [
            'name' => 'users',
            'key' => 'adminUsers',
            'introduction' => '管理员',
            'redirect' => '/users/list',
            'icon' => 'ant-design:user-outlined',
            'pid' => 0,
            'sort' => 99,
        ];

        $adminMenu->insert($data)->saveData();

        $usersId = $this->getAdapter()->getConnection()->lastInsertId();

        $data = [
            'name' => 'role',
            'key' => 'adminUsers.role',
            'introduction' => '角色管理',
            'pid' => $usersId,
        ];

        $adminMenu->insert($data)->saveData();

        $roleId = $this->getAdapter()->getConnection()->lastInsertId();

        $data = [
            [
                'name' => 'add',
                'key' => 'adminUsers.role.addRole',
                'introduction' => '添加角色',
                'hidden' => 1,
                'pid' => $roleId,
            ],
            [
                'name' => 'edit',
                'key' => 'adminUsers.role.editRole',
                'introduction' => '编辑角色',
                'hidden' => 1,
                'pid' => $roleId,
            ],
            [
                'name' => 'delete',
                'key' => 'adminUsers.role.deleteRole',
                'introduction' => '删除角色',
                'hidden' => 1,
                'pid' => $roleId,
            ]
        ];
        $adminMenu->insert($data)->save();

        $data = [
            'name' => 'list',
            'key' => 'adminUsers.list',
            'introduction' => '管理员管理',
            'pid' => $usersId,
        ];
        $adminMenu->insert($data)->saveData();

        $usersListId = $this->getAdapter()->getConnection()->lastInsertId();

        $data = [
            [
                'name' => 'add',
                'key' => 'adminUsers.list.addAdminUser',
                'introduction' => '添加',
                'hidden' => 1,
                'pid' => $usersListId,
            ],
            [
                'name' => 'edit',
                'key' => 'adminUsers.list.editAdminUser',
                'introduction' => '编辑',
                'hidden' => 1,
                'pid' => $usersListId,
            ],
            [
                'name' => 'delete',
                'key' => 'adminUsers.list.deleteAdminUser',
                'introduction' => '删除',
                'hidden' => 1,
                'pid' => $usersListId,
            ]
        ];
        $adminMenu->insert($data)->save();

        $data = [
            'name' => 'logs',
            'key' => 'adminControllerLogs',
            'introduction' => '操作日志',
            'redirect' => '/logs/index',
            'icon' => 'akar-icons:eye-closed',
            'pid' => 0,
            'sort' => 99];
        $adminMenu->insert($data)->saveData();

        $logsId = $this->getAdapter()->getConnection()->lastInsertId();

        $data = [
            'name' => 'clear',
            'key' => 'adminControllerLogs.clearAdminLogs',
            'introduction' => '清空日志',
            'hidden' => 1,
            'pid' => $logsId,
        ];
        $adminMenu->insert($data)->saveData();
    }
}
