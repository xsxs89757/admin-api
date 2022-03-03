<?php

namespace Qifen\WebmanAdmin\controller;

use Qifen\WebmanApiResponse\Code;
use Qifen\WebmanApiResponse\ApiResponse;
use Qifen\WebmanAdmin\exception\ApiErrorException;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator;
use support\Request;

class Base {
    use ApiResponse;

    // 正整数正则
    const ID_REG = '/^[1-9][0-9]*$/';

    /**
     * 验证参数
     *
     * @param array $params
     * @param array $rules
     * @param $withError
     * @return array
     * @throws ApiErrorException
     */
    protected function validateParams(array $params, array $rules, $withError = false) {
        try {
            $data = Validator::input($params, $rules);

            return $data;
        } catch (ValidationException $e) {
            $msg = $withError ? $e->getMessage() : '参数错误';

            throw new ApiErrorException($msg, Code::STATUS_ERROR_PARAM);
        } catch (\Exception $e) {
            throw new ApiErrorException('参数错误', Code::STATUS_ERROR_PARAM);
        }
    }

    /**
     * 验证ID
     *
     * @param $id
     * @param bool $withResponse
     * @return bool
     * @throws ApiErrorException
     */
    protected function validateId($id, bool $withResponse = false) {
        $res = preg_match(self::ID_REG, $id) === 1;

        if ($withResponse && !$res) throw new ApiErrorException('参数错误');

        return $res;
    }

    /**
     * 验证ID并返回错误
     *
     * @param $id
     * @return bool
     * @throws ApiErrorException
     */
    protected function validateIdWithResponse($id) {
        return $this->validateId($id, true);
    }

    /**
     * 获取分页大小
     *
     * @param Request $request
     * @return int
     * @throws ApiErrorException
     */
    protected function getPageSize(Request $request) {
        $limit = $request->input('limit', 20);

        if (!$this->validateId($limit)) throw new ApiErrorException('分页参数错误');
        if ($limit > 100) throw new ApiErrorException('每页最多100条');

        return $limit;
    }

    /**
     * 获取分页参数
     *
     * @param Request $request
     * @return array
     * @throws ApiErrorException
     */
    protected function getPageParams(Request $request) {
        $page = $request->input('page', 1);

        if (!$this->validateId($page)) $page = 1;

        $limit = $this->getPageSize($request);

        $offset = ($page - 1) * $limit;

        return compact('page', 'limit', 'offset');
    }
}