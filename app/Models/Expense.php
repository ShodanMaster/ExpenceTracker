<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'description',
        'date',
        'reason_id',
        'type',
    ];

    public function reason(){
        return $this->belongsTo(Reason::class);
    }
}
