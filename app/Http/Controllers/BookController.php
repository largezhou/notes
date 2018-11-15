<?php

namespace App\Http\Controllers;

use App\Filters\BookFilter;
use App\Filters\NoteFilter;
use App\Http\Requests\BookRequest;
use App\Http\Resources\BookResource;
use App\Http\Resources\NoteResource;
use App\Models\Book;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::orderBy('updated_at', 'desc')->filter(app(BookFilter::class))->get();

        return BookResource::collection($books);
    }

    public function store(BookRequest $request)
    {
        $files = $this->handleUploadFile($request);
        $data = $request->all();
        $data = array_merge($data, $files);

        $book = Book::create($data);

        return $this->created(['id' => $book->id]);
    }

    public function show(Book $book)
    {
        $notes = $book->notes()->filter(app(NoteFilter::class))->orderBy('created_at', 'desc')->get();
        $book->setAttribute('notes_count', $notes->count());

        return [
            'book'  => BookResource::make($book),
            'notes' => NoteResource::collection($notes)->except(['updated_at', 'content', 'html_content']),
        ];
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return $this->noContent();
    }

    public function forceDestroy($id)
    {
        Book::onlyTrashed()->findOrFail($id)->forceDelete();

        return $this->noContent();
    }

    public function update(BookRequest $request, $id)
    {
        $book = $request->getBook();
        $book->update($request->all());

        return BookResource::make($book);
    }
}
