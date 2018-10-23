<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $books = Book::getBooks();

        return BookResource::collection($books);
    }

    public function store(BookRequest $request)
    {
        $files = $this->handleUploadFile($request);
        $data = $request->all();
        $data = array_merge($data, $files);

        $book = Book::addBook($data);

        return $this->created(['id' => $book->id]);
    }

    /**
     * 返回 201已创建 响应
     *
     * @param null $data
     *
     * @return \Symfony\Component\HttpFoundation\Response;
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

            $filename = $md5.($ext ? ".{$ext}" : '');

            $path = $driver->putFileAs('uploads', $file, $filename);

            return $driver->url($path);
        }, $files);

        return $files;
    }
}
