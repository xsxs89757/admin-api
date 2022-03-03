<?php


use app\helpers\Bcrypt;
use Phinx\Seed\AbstractSeed;

class AdminUserSeeder extends AbstractSeed {
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run() {
        $data = [
            'username' => 'admin',
            'nickname' => '超级管理员',
            'password' => Bcrypt::hashPassword('12345678'),
            'create_uid' => 0,
            'create_time' => time(),
            'last_login_time' => time(),
            'last_login_ip' => '127.0.0.1',
        ];

        $this->table('admin_user')->insert($data)->saveData();
    }
}
