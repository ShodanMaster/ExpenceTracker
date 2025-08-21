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
        'frequency_value',
        'day_of_week',
        'day_of_month',
        'month_of_year',
        'description',
        'last_occurence',
        'next_occurence',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_occurence' => 'datetime',
        'next_occurence' => 'datetime',
    ];

    public function reason()
    {
        return $this->belongsTo(Reason::class);
    }

}
