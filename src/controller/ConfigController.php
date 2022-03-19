<?php

namespace Qifen\WebmanAdmin\controller;

use support\Request;
use Qifen\WebmanAdmin\model\SystemConfig;
use Respect\Validation\Validator as v;


class ConfigController extends Base
{
    /**
     * 获取配置字典
     *
     * @return \support\Response
     */
    public function getMap()
    {
        $type = SystemConfig::getConstConfig('type');
        $group = SystemConfig::getConstConfig('group');

        return $this->success(compact('type', 'group'));
    }

    /**
     * 配置列表
     * 
     * @param Request $request
     * @return \support\Response
     * @throws \Qifen\WebmanAdmin\exception\ApiErrorException
     */
    public function configList(Request $request)
    {
        $params = $this->getPageParams($request);

        $group = $request->input('group');
        $keyword = $request->input('keyword');

        $sql = SystemConfig::when(array_key_exists($group, SystemConfig::GROUP), function ($query) use ($group) {
                $query->where('group', $group);
            })
            ->when(!empty($keyword), function ($query) use ($keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('title', 'like', '%' . $keyword . '%');
                });
            });

        $total = $sql->count();

        $list = $sql->orderBy('sort', 'asc')
            ->orderBy('id', 'desc')
            ->offset($params['offset'])
            ->limit($params['limit'])
            ->get()
            ->map(function ($item) {
                $options = [];

                if (!empty($item->extra)) {
                    $options = explode(',', $item->extra);
                }

                $item->options = $options;
                $item->value = SystemConfig::getValue($item->type, $item->value);

                return $item;
            })
            ->toArray();

        return $this->success(compact('list', 'total'));
    }

    /**
     * 配置排序
     * 
     * @param Request $request
     * @return \support\Response
     * @throws \Qifen\WebmanAdmin\exception\ApiErrorException
     */
    public function configSort(Request $request)
    {
        $data = $request->input('data');

        $this->validateParams(compact('data'), [
            'data' => v::arrayType()->length(1, 255),
        ]);

        SystemConfig::updateSort($data);

        return $this->success();
    }

    /**
     * 配置创建
     *
     * @param Request $request
     * @return \support\Response
     * @throws \Qifen\WebmanAdmin\exception\ApiErrorException
     */
    public function configCreate(Request $request)
    {
        $data = $this->validateParams($request->all(), [
            'name' => v::stringType()->length(1, 255),
            'title' => v::stringType()->length(1, 255),
            'sort' => v::nullable(v::intVal()->min(1)),
            'status' => v::intVal()->min(0)->max(1),
            'extra' => v::nullable(v::stringType()->length(null, 255)),
            'remark' => v::nullable(v::stringType()->length(null, 255)),
            'type' => v::in(SystemConfig::getConstConfig('type', true)),
            'group' => v::in(SystemConfig::getConstConfig('group', true)),
        ]);
        
        SystemConfig::store($data);

        return $this->success();
    }

    /**
     * 配置修改
     * 
     * @param Request $request
     * @param int $id
     * @return \support\Response
     * @throws \Qifen\WebmanAdmin\exception\ApiErrorException
     */
    public function configEdit(Request $request, int $id)
    {
        $this->validateIdWithResponse($id);

        $data = $this->validateParams($request->all(), [
            'name' => v::stringType()->length(1, 255),
            'title' => v::stringType()->length(1, 255),
            'sort' => v::nullable(v::intVal()->min(1)),
            'status' => v::intVal()->min(0)->max(1),
            'extra' => v::nullable(v::stringType()->length(null, 255)),
            'remark' => v::nullable(v::stringType()->length(null, 255)),
            'type' => v::in(SystemConfig::getConstConfig('type', true)),
            'group' => v::in(SystemConfig::getConstConfig('group', true)),
        ]);

        SystemConfig::store($data, $id);

        return $this->success();
    }

    /**
     * 配置删除
     * 
     * @param Request $request
     * @param int $id
     * @return \support\Response
     * @throws \Qifen\WebmanAdmin\exception\ApiErrorException
     */
    public function configDel(Request $request, int $id)
    {
        $this->validateIdWithResponse($id);
        
        SystemConfig::del($id);

        return $this->success();
    }

    /**
     * 获取已有配置项分组
     *
     * @return \support\Response
     */
    public function configGroup()
    {
        $list = SystemConfig::select(['group'])->groupBy('group')->get()->toArray();

        return $this->success($list);
    }

    /**
     * 根据分组获取配置项
     * 
     * @param Request $request
     * @return \support\Response
     */
    public function configListByGroup(Request $request)
    {
        $group = $request->input('group');
        
        if (empty($group)) return $this->errorParam();
        
        $list = SystemConfig::where('group', $group)
            ->get()
            ->map(function ($item) {
                $options = [];

                if (!empty($item->extra)) {
                    $options = explode(',', $item->extra);
                }

                $item->options = $options;
                $item->value = SystemConfig::getValue($item->type, $item->value);

                return $item;
            })
            ->toArray();

        return $this->success($list);
    }

    /**
     * 更新配置内容
     * 
     * @param Request $request
     * @return \support\Response
     * @throws \Qifen\WebmanAdmin\exception\ApiErrorException
     */
    public function configBatchStore(Request $request)
    {
        $data = $request->input('data');

        $this->validateParams(compact('data'), [
            'data' => v::arrayType()->length(1, 255),
        ]);
        
        SystemConfig::updateValue($data);
        
        return $this->success();
    }
}