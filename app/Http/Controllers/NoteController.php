<?php

namespace App\Http\Controllers;

use App\Filters\BookFilter;
use App\Filters\NoteFilter;
use App\Http\Requests\NoteRequest;
use App\Http\Resources\BookResource;
use App\Http\Resources\NoteResource;
use App\Models\Book;
use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $notes = Note::latest()
            ->filter(app(NoteFilter::class)->only(['edit_mode']))
            ->with(['book' => function ($query) {
                $query->withTrashed();
            }])
            ->whereHas('book', function ($query) use ($request) {
                $query->filter(app(BookFilter::class)->only(['edit_mode']));
            })
            ->paginate();

        return NoteResource::collection($notes)->except(['updated_at', 'created_at', 'content', 'html_content']);
    }

    public function show(Note $note)
    {
        $book = $note->book;
        abort_if(!$book, 404);

        return [
            'note' => NoteResource::make($note),
            'book' => BookResource::make($book),
        ];
    }

    public function store(NoteRequest $request, Book $book)
    {
        $note = $book->notes()->create($request->all());

        return $this->created([
            'id' => $note->id,
        ]);
    }

    public function destroy(Note $note)
    {
        $note->delete();

        return $this->noContent();
    }
}
