<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LSDailyStorageTankStatus extends Model
{
    protected $table = 'v_daily_storage_tank_status';

    public $timestamps = false;

    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'tank_no',
        'name',
        'capacity',
        'today_date',
        'oil_type',
        'last_used_date',
        'status',
    ];

    protected $casts = [
        'today_date' => 'datetime',
        'last_used_date' => 'datetime',
    ];
}
