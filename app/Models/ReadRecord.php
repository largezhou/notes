<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class ReadRecord extends Model
{
    const UPDATED_AT = null;
    protected $fillable = ['book_id', 'read'];

    public function setUpdatedAt($value)
    {
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * 获取格式化的时间线，包含分页
     *
     * @param int $currentPage 当前页
     *
     * @return array
     */
    public function getTimeline($currentPage)
    {
        $perPage = $this->perPage;

        $dataFormatSQL = DB::raw('date_format(created_at, "%Y-%m-%d") as day');

        $days = ReadRecord::query()
            ->select($dataFormatSQL)
            ->groupBy('day')
            ->orderBy('day', 'desc')
            ->offset($perPage * ($currentPage - 1))
            ->limit($perPage)
            ->pluck('day');

        $paginator = new Paginator($days, $perPage, $currentPage);

        $timeline = ReadRecord::query()
            ->with('book:id,title')
            ->select([
                'book_id',
                DB::raw('sum(`read`) as sum'),
                $dataFormatSQL,
            ])
            ->groupBy(['day', 'book_id'])
            ->orderBy('day', 'desc')
            ->whereBetween('created_at', [
                $days->last(),
                date('Y-m-d H:i:s', strtotime('+1 day', strtotime($days[0]) - 1)),
            ])
            ->get()
            ->each(function ($t) {
                // 如果书籍不存在，则隐藏书籍 id
                if ($t->book == null) {
                    $t->book_id = null;
                }
            });

        return [
            'data' => $timeline,
            'meta' => array_except($paginator->toArray(), 'data'),
        ];
    }
}
