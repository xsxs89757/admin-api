<?php

namespace Qifen\WebmanAdmin\controller;

use Qifen\WebmanAdmin\model\SystemConfig;
use support\Request;

class UploadController extends Base
{
    /**
     * 通用上传
     *
     * @param Request $request
     * @return \support\Response
     */
    public function upload(Request $request)
    {
        $file = $request->file('file');
        $uploadPath = $request->input('path', 'uploads');
        
        if (!$file) return $this->errorParam();
        
        try {
            $path = $this->getDatePath($file->getUploadExtension(), $uploadPath);
            $file->move(SystemConfig::getUploadFullPath($path));

            $name = $file->getUploadName();
            $url = SystemConfig::getUploadUrl($path);

            return $this->success(compact('name', 'path', 'url'));
        } catch (\Exception $exception) {}

        return $this->error('上传失败');
    }
}