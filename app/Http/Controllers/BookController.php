<?php

namespace App\Http\Controllers;

use App\Filters\BookFilter;
use App\Filters\NoteFilter;
use App\Http\Requests\BookRequest;
use App\Http\Resources\BookResource;
use App\Http\Resources\NoteResource;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show', 'notes']);
    }

    public function index()
    {
        $books = Book::withCount('notes')->orderBy('updated_at', 'desc')->filter(app(BookFilter::class))->get();

        return BookResource::collection($books);
    }

    public function store(BookRequest $request)
    {
        $data = $this->handleUploadFile($request);

        $book = Book::create($data);

        return $this->created(['id' => $book->id]);
    }

    public function show(Book $book)
    {
        $book->setAttribute('notes_count', $book->notes()->count());

        return BookResource::make($book);
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return $this->noContent();
    }

    public function forceDestroy(Book $deletedBook)
    {
        $deletedBook->forceDelete();

        return $this->noContent();
    }

    public function update(BookRequest $request, Book $book)
    {
        $data = $this->handleUploadFile($request);
        $book->update($data);

        $book->setAttribute('notes_count', $book->notes()->count());

        return BookResource::make($book);
    }

    public function notes(Request $request, Book $book)
    {
        $sortType = $request->get('_sort_type', 'desc');
        if (!in_array($sortType, ['desc', 'asc'])) {
            $sortType = 'desc';
        }

        $notes = $book
            ->notes()
            ->with('tags')
            ->filter(app(NoteFilter::class))
            ->orderBy('page', $sortType)
            ->orderBy('id', $sortType)
            ->paginate();

        return NoteResource::collection($notes)->except(['updated_at', 'content', 'html_content']);
    }
}
