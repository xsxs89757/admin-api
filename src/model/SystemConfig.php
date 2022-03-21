<?php

namespace Qifen\WebmanAdmin\model;

use Qifen\WebmanAdmin\exception\ApiErrorException;
use support\Db;
use support\Model;
use support\Redis;

class SystemConfig extends Model
{
    const CACHE_KEY = 'system_config_cache';

    const GROUP = [
        'base' => '基础配置',
        'system' => '系统配置',
        'pay' => '支付配置',
        'other' => '其他配置'
    ];

    const TYPES = [
        'number' => '数字',
        'string' => '字符',
        'url' => '链接',
        'textarea' => '文本',
        'password' => '密码',
        'select' => '下拉',
        'radio' => '单选',
        'checkbox' => '多选',
        'oneimage' => '单图',
        'multipleimage' => '多图',
        'onefile' => '单文件',
        'multiplefile' => '多文件'
    ];

    const UPLOAD_BASE_PATH = '/storage/';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'system_config';

    /**
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    /**
     * 获取上传基础路径
     *
     * @return string
     */
    public static function getUploadBasePath()
    {
        return public_path() . self::UPLOAD_BASE_PATH;
    }

    /**
     * 获取上传完整路径
     *
     * @param string $path
     * @return string
     */
    public static function getUploadFullPath(string $path)
    {
        return self::getUploadBasePath() . $path;
    }

    /**
     * 获取文件 url
     * 
     * @param string $path
     * @return string
     */
    public static function getUploadUrl(string $path)
    {
        return config('plugin.qifen.admin.config.index_url', 'http://127.0.0.1:8787') . self::UPLOAD_BASE_PATH . $path;
    }

    /**
     * 获取静态配置项
     *
     * @param string $type
     * @param bool $onlyKey
     * @return array
     */
    public static function getConstConfig(string $type, bool $onlyKey = false)
    {
        $list = $type == 'group' ? self::GROUP : self::TYPES;

        $data = [];

        foreach ($list as $key => $label) {
            if ($onlyKey) $data[] = $key;
            else $data[] = compact('key', 'label');
        }

        return $data;
    }

    /**
     * 清除缓存
     *
     * @return void
     */
    public static function clearCache()
    {
        Redis::del(self::CACHE_KEY);
    }

    /**
     * 保存
     *
     * @param array $data
     * @param int $id
     * @return void
     * @throws ApiErrorException
     */
    public static function store(array $data, int $id = 0)
    {
        try {
            $isExtra = in_array($data['type'], ['select', 'radio', 'checkbox']);

            if ($isExtra && (!isset($data['extra']) || empty($data['extra']))) {
                throw new ApiErrorException();
            }

            if (self::where('id', '<>', $id)->where('name', $data['name'])->count() > 0) {
                throw new ApiErrorException('已存在相同配置项');
            }

            if ($id > 0) {
                $config = self::findOrFail($id);

                if ($config->type !== $data['type']) {
                    $config->value = '';
                }
            } else {
                $config = new self;
            }

            $config->name = $data['name'];
            $config->type = $data['type'];
            $config->title = $data['title'];
            $config->group = $data['group'];
            $config->status = $data['status'];
            $config->sort = $data['sort'] ?? 1;
            $config->extra = $data['extra'] ?? '';
            $config->remark = $data['remark'] ?? '';
            $config->extra = $isExtra ? $data['extra'] : '';

            $config->saveOrFail();

            if ($id > 0) self::clearCache();
        } catch (ApiErrorException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new ApiErrorException();
        }
    }

    /**
     * 删除配置
     *
     * @param int $id
     * @return void
     */
    public static function del(int $id)
    {
        self::where('id', $id)->delete();

        self::clearCache();
    }

    /**
     * 更新排序
     *
     * @param array $data
     * @return void
     * @throws ApiErrorException
     */
    public static function updateSort(array $data)
    {
        Db::beginTransaction();

        try {
            foreach ($data as $item) {
                if (isset($item['id']) && $item['sort']) {
                    self::where('id', $item['id'])->update(['sort' => $item['sort']]);
                }
            }

            Db::commit();
        } catch (\Exception $exception) {
            Db::rollBack();
            throw new ApiErrorException();
        }
    }

    /**
     * 格式化展示值
     *
     * @param string $type
     * @param $value
     * @return array|sring
     */
    public static function getValue(string $type, $value)
    {
        $res = '';

        switch ($type) {
            case 'checkbox':
                $res = empty($value) ? [] : json_decode($value, true);
                break;
            case 'oneimage':
            case 'multipleimage':
            case 'onefile':
            case 'multiplefile':
                $list = empty($value) ? [] : json_decode($value, true);

                $res = [];

                foreach ($list as $item) {
                    $name = $item['name'];
                    $path = $item['path'];
                    $url = self::getUploadUrl($path);

                    $res[] = compact('name', 'path', 'url');
                }
                break;
            default:
                $res = $value;
                break;
        }

        return $res;
    }

    /**
     * 格式化保存值
     *
     * @param string $type
     * @param mixed $value
     * @return string
     */
    public static function setValue(string $type, $value)
    {
        $res = '';
        
        switch ($type) {
            case 'checkbox':
            case 'oneimage':
            case 'multipleimage':
            case 'onefile':
            case 'multiplefile':
                $res = json_encode($value ?: [], JSON_UNESCAPED_SLASHES);
                break;
            default:
                $res = $value ?: '';
                break;
        }
        
        return $res;
    }

    /**
     * 更新内容
     *
     * @param array $data
     * @return void
     * @throws ApiErrorException
     */
    public static function updateValue(array $data)
    {
        Db::beginTransaction();

        try {
            foreach ($data as $item) {
                if (isset($item['id']) && isset($item['type'])) {
                    $value = self::setValue($item['type'], $item['value'] ?? null);
                    self::where('id', $item['id'])->update(['value' => $value]);
                }
            }

            Db::commit();

            self::clearCache();
        } catch (\Exception $exception) {
            Db::rollBack();
            throw new ApiErrorException();
        }
    }

    /**
     * 获取配置
     *
     * @param string $key
     * @return mixed|string|null
     */
    public static function getConfig(string $key)
    {
        if (empty($key)) return '';

        $cache = Redis::get(self::CACHE_KEY);

        if (!$cache) {
            $list = self::select(['id', 'name', 'title', 'type', 'value'])
                ->get()
                ->map(function ($item) {
                    $item->value = self::getValue($item->type, $item->value);

                    return $item;
                })
                ->toArray();

            $config = [];

            foreach ($list as $item) {
                $config[$item['name']] = $item;
            }

            Redis::set(self::CACHE_KEY, json_encode($config, JSON_UNESCAPED_SLASHES));
        } else {
            $config = json_decode($cache, true);
        }

        return $config[$key] ?? null;
    }
}