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
```
### 数据库配置

 (1)修改数据库 `database` 配置

 (2) 执行 `php phinx migrate -e dev -t 20210000000001_create_admin_center` 导入数据库

 (3) 执行 `php phinx migrate -e dev -t 20210000000000_create_casbin_rule` 导入数据库

 (4) 配置 `config/redis` 配置

 (5) 修改 `config/exception.php` 配置   
    ```php
        use Qifen\Admin\exception\Handler;

        return [
            '' => support\exception\Handler::class,
            'Admin' => Handler::class,
        ];
    ```

# 接口

```php

```