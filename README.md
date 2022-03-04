# webman admin 后台基础权限功能
``` 
composer require qifen/admin
```
# 依赖
```
composer qifen/jwt
composer qifen/phinx
composer qifen/route
composer qifen/casbin
composer qifen/helper
composer workerman/validation
```
### 数据库配置

 (1)修改数据库 `database` 配置

 (2) 执行 `php phinx migrate -e dev -t 20210000000001_create_admin_center` 创建表

 (3) 执行 `php phinx seed:run -e dev -s AdminUserSeeder` 初始化用户数据

 (4) 执行 `php phinx seed:run -e dev -s RuleSeeder` 初始化菜单数据

 (5) 配置 `config/redis` 配置

 (6) 修改 `config/exception.php` 配置   
 ```php
        use Qifen\WebmanAdmin\exception\Handler;

        return [
            '' => support\exception\Handler::class,
            'WebmanAdmin' => Handler::class,
        ];
```

# 接口

```php

```