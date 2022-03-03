<?php

namespace Qifen\WebmanAdmin\model;

use Qifen\Casbin\Permission;
use support\Model;
use support\Redis;

class AdminMenu extends Model {
    const CACHE_MENU_KEY = 'all_menus';

    const STATIC_MENU = ['login' => '登录', 'resetPassword' => '修改密码'];
    
    const DASHBOARD = [
        'path' => '/',
        'component' => 'LAYOUT',
        'redirect' => '/dashboard',
        'meta' => [
            'title' => '首页',
            'icon' => 'bx:bx-home',
            'affix' => true,
            'hideChildrenInMenu' => true,
        ],
        'children' => [
            [
                'path' => 'dashboard',
                'name' => 'Dashboard',
                'component' => '/dashboard/workbench/index',
                'meta' => [
                    'title' => '首页',
                    'hideMenu' => true,
                    'currentActiveMenu' => '/'
                ],
            ]
        ]
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_menu';

    /**
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date) {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    /**
     * 获取用户菜单
     *
     * @param bool $withHandle
     * @return array
     * @throws \Qifen\WebmanAdmin\exception\UnauthorizedException
     */
    public static function getRoleMenu(bool $withHandle = true) {
        $uid = AdminUser::getCurrentUserId();

        if ($uid == 1) {
            $menus = self::orderBy('sort', 'asc')->orderBy('id', 'asc')->get();
        } else {
            $rule = [];
            $permissions = Permission::getImplicitPermissionsForUser('adminUser_' . $uid);

            foreach ($permissions as $value) {
                if ($value[1] === 'admin') {
                    $rule[] = $value[2];
                }
            }

            $menus = self::whereIn('key', $rule)->orderBy('sort', 'asc')->orderBy('id', 'asc')->get();
        }

        $tree = self::getTree($menus->toArray(), $withHandle);

        if (!$withHandle) return $tree;

        return array_merge([self::DASHBOARD], $tree);
    }

    /**
     * 获取树形结构
     *
     * @param array $list
     * @param int $pid
     * @return array
     */
    public static function getTree(array $list, bool $withHandle = true, int $pid = 0, string $prefix = '/') {
        $tree = [];

        foreach ($list as $item) {
            if ($item['pid'] == $pid && (!$withHandle || ($withHandle && $item['hidden'] == 0))) {
                $children = self::getTree($list, $withHandle, $item['id'], $prefix . $item['name'] . '/');

                $data = $withHandle ? self::handleLine($item, $prefix) : $item;

                if (!empty($children)) {
                    $data['children'] = $children;
                } else if ($withHandle && $pid == 0) {
                    $data['meta']['hideChildrenInMenu'] = true;
                    $data['children'] = [
                        [
                            'path' => 'index',
                            'name' => $item['key'] . '.index',
                            'component' => $prefix . $item['name'] . '/index',
                            'meta' => [
                                'title' => $item['introduction'],
                                'hideMenu' => true,
                            ]
                        ]
                    ];
                }

                $tree[] = $data;
            }
        }

        return $tree;
    }

    /**
     * 处理菜单结构
     *
     * @param array $line
     * @param string $prefix
     * @return array
     */
    private static function handleLine(array $line, string $prefix) {
        $meta = [
            'title' => $line['introduction'],
            'hideMenu' => $line['hidden'] == 1,
            'hideBreadcrumb' => $line['breadcrumb'] == 1,
        ];

        if (!empty($line['icon'])) $meta['icon'] = $line['icon'];

        $isRoot = $prefix == '/';

        return [
            'path' => ($isRoot ? '/' : '') . $line['name'],
            'name' => $line['key'],
            'component' => $isRoot ? 'LAYOUT' : ($prefix . $line['name']),
            'redirect' => $line['redirect'],
            'meta' => $meta,
        ];
    }

    /**
     * 清空菜单缓存
     * 
     * @return void
     */
    public static function clearCache() {
        Redis::del(self::CACHE_MENU_KEY);
    }

    /**
     * 获取全部菜单
     * 
     * @param string $index
     * @return array|mixed
     */
    public static function getAllMenu(string $index) {
        $menus = Redis::get(self::CACHE_MENU_KEY);

        if (!$menus) {
            $list = self::orderBy('sort', 'asc')
                ->orderBy('id', 'asc')
                ->get()
                ->toArray();

            $k = [];
            $id = [];
            $parent = [];
            $children = [];

            foreach ($list as $item) {
                if ($item['pid'] == 0) {
                    $parent[$item['id']] = $item;
                } else {
                    $children[$item['pid']][] = $item;
                }

                $k[$item['key']] = $item;
                $id[$item['id']] = $item;
            }

            $menus = compact('k', 'id', 'parent', 'children');

            Redis::set(self::CACHE_MENU_KEY, json_encode($menus));
        } else {
            $menus = json_decode($menus, true);
        }

        return empty($index) ? $menus : $menus[$index];
    }

    /**
     * 获取完整路径
     * 
     * @param string $key
     * @return array
     */
    public static function getFullPathName(string $key) {
        $path = [];

        $keys = self::getAllMenu('k');

        if (array_key_exists($key, $keys)) {
            $item = $keys[$key];

            $path[] = $item['introduction'];

            if ($item['pid'] !== 0) {
                $parent = self::getParentPathName($item['pid']);
                $path = array_merge($path, $parent);
            }

            $path = array_reverse($path);
        } else if (isset(self::STATIC_MENU[$key])) {
            $path[] = self::STATIC_MENU[$key];
        } else {
            $path[] = '上传文件{' . $key . '}';
        }

        return $path;
    }

    /**
     * 获取所有父节点名称
     * 
     * @param int $id
     * @return array
     */
    private static function getParentPathName(int $id) {
        $path = [];

        $ids = self::getAllMenu('id');

        if (array_key_exists($id, $ids)) {
            $item = $ids[$id];
            $path[] = $item['introduction'];

            if ($item['pid'] !== 0) {
                $parent = self::getParentPathName($item['pid']);
                $path = array_merge($path, $parent);
            }
        }

        return $path;
    }
}