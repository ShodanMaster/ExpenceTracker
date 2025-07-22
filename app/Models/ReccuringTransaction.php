<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReccuringTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'reason_id',
        'amount',
        'type',
        'frequency',
        'day_of_week',
        'day_of_month',
        'month_of_year',
        'last_run_date',
        'next_run_date',
        'is_active'
    ];

    // In ReccuringTransaction.php
    public function reason()
    {
        return $this->belongsTo(Reason::class);
    }

}
