<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $books = Book::getVisibleBooks();

        return BookResource::collection($books)->except(['deleted_at', 'hidden']);
    }

    public function store(BookRequest $request)
    {
        $book = Book::addBook($request->all());
    }
}