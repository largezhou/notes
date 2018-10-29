<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;

class BookController extends Controller
{
    public function index()
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

    public function show(Book $book)
    {
        return BookResource::make($book);
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return $this->noContent();
    }

    public function forceDestroy($id)
    {
        Book::editMode()->findOrFail($id)->forceDelete();

        return $this->noContent();
    }

    public function update(BookRequest $request, $id)
    {
        $book = $request->getBook();
        $book->update($request->all());

        return BookResource::make($book);
    }
}
