<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReadRecord extends Model
{
    const UPDATED_AT = null;
    protected $fillable = ['book_id', 'read'];

    public function setUpdatedAt($value)
    {
    }
}
