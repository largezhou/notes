<?php

namespace App\Console\Commands;

use App\Interfaces\XSIndexable;
use App\Models\Book;
use App\Models\Note;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use XSDocument;

class XSIndex extends Command
{
    protected $xs;
    protected $index;
    protected $doc;
    protected $signature = 'xs:index';
    protected $description = '重建所有索引';

    public function __construct()
    {
        parent::__construct();

        $this->xs = app('XS');
    }

    public function handle()
    {
        $this->index = $this->xs->index;
        $this->doc = new XSDocument();

        $this->index->clean();

        $this->index(Note::with([
            'book' => function ($query) {
                $query->showAll();
            },
        ])->showAll()->get());
        $this->index(Post::showAll()->get());
        $this->index(Book::showAll()->get());

        $this->index->close();
    }

    protected function index(Collection $items)
    {
        $items->each(function (XSIndexable $model) {
            $this->doc->setFields($model->xsDocData());

            $this->index->add($this->doc);
        });
    }
}
