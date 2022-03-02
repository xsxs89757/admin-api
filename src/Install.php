<?php
namespace Qifen\Admin;

class Install
{
    const WEBMAN_PLUGIN = true;

    /**
     * @var array
     */
    protected static $pathRelation = array (
        'config/plugin/qifen/admin'             => 'config/plugin/qifen/admin',
        'exception/ApiErrorException.php'       => 'exception/ApiErrorException.php',
        'exception/Handler.php'                 => 'exception/Handler.php',
        'exception/UnauthorizedException.php'   => 'exception/UnauthorizedException.php',
        'app/admin/controller/Base.php'         => 'app/admin/controller/Base.php',
        'database/migrations/20210000000001_create_admin_center.php' => 'database/migrations/20210000000001_create_admin_center.php'
    );

    /**
     * Install
     * @return void
     */
    public static function install()
    {
        static::installByRelation();
    }

    /**
     * Uninstall
     * @return void
     */
    public static function uninstall()
    {
        self::uninstallByRelation();
    }

    /**
     * installByRelation
     * @return void
     */
    public static function installByRelation()
    {
        foreach (static::$pathRelation as $source => $dest) {
            if ($pos = strrpos($dest, '/')) {
                $parent_dir = base_path().'/'.substr($dest, 0, $pos);
                if (!is_dir($parent_dir)) {
                    mkdir($parent_dir, 0777, true);
                }
            }
            //symlink(__DIR__ . "/$source", base_path()."/$dest");
            copy_dir(__DIR__ . "/$source", base_path()."/$dest");
        }
    }

    /**
     * uninstallByRelation
     * @return void
     */
    public static function uninstallByRelation()
    {
        foreach (static::$pathRelation as $source => $dest) {
            $path = base_path()."/$dest";
            if (!is_dir($path) && !is_file($path)) {
                continue;
            }
            if (is_link($path)) {
                unlink($path);
            }else{
                remove_dir($path);
            }   
            
        }
    }
    
}