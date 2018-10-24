<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * 204 no content 响应
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function noContent()
    {
        return response(null, 204);
    }

    /**
     * 返回 201 已创建 响应
     *
     * @param null $data
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function created($data = null)
    {
        if (is_null($data)) {
            return response(null, 201);
        } else {
            return response()->json($data)->setStatusCode(201);
        }
    }

    /**
     * 保存文件到本地，并返回 键 => 保存路径 的键值对
     *
     * @param Request $request
     *
     * @return array
     */
    protected function handleUploadFile(Request $request)
    {
        $files = $request->file();
        $driver = \Storage::drive('public');

        $files = array_map(function (UploadedFile $file) use ($driver) {
            $md5 = md5_file($file);
            $ext = $file->getClientOriginalExtension();

            $filename = $md5 . ($ext ? ".{$ext}" : '');

            $path = $driver->putFileAs('uploads', $file, $filename);

            return $driver->url($path);
        }, $files);

        return $files;
    }
}
