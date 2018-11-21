<?php

namespace App\Http\Controllers;

use App\Filters\NoteFilter;
use App\Http\Requests\NoteRequest;
use App\Http\Resources\BookResource;
use App\Http\Resources\NoteResource;
use App\Models\Book;
use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index()
    {
        $notes = Note::latest()
            ->filter(app(NoteFilter::class)->only(['edit_mode']))
            ->with(['book', 'tags'])
            ->whereHas('book')
            ->paginate();

        return NoteResource::collection($notes)->except(['updated_at', 'created_at', 'content', 'html_content']);
    }

    public function show(Note $note)
    {
        $book = $note->book()->withCount('notes')->first();
        abort_if(!$book, 404);
        $note->load('tags');

        return [
            'note' => NoteResource::make($note),
            'book' => BookResource::make($book),
        ];
    }

    public function store(NoteRequest $request, Book $book)
    {
        /** @var Note $note */
        $note = $book->notes()->create($request->validated());

        if ($tags = $request->get('tags')) {
            $note->handleSyncTags($tags);
        }

        if ($request->get('mark_read')) {
            $book->read = $request->get('page');
            $book->save();
        }

        return $this->created([
            'id' => $note->id,
        ]);
    }

    public function destroy(Note $note)
    {
        $note->delete();

        return $this->noContent();
    }

    public function forceDestroy(Note $deletedNote)
    {
        $deletedNote->forceDelete();

        return $this->noContent();
    }

    public function update(NoteRequest $request, Note $note)
    {
        $note->update($request->all());

        if ($tags = $request->get('tags')) {
            $note->handleSyncTags($tags);
        }

        return NoteResource::make($note);
    }
}
