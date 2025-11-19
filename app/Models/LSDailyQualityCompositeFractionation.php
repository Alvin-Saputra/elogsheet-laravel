<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LSDailyQualityCompositeFractionation extends Model
{
    use HasFactory;

    protected $table = 't_daily_quality_composite_fractionation_500_mt';
    protected $primaryKey = 'id';

    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'transaction_date',
        'time',
        'work_center',
        'crystalizer',
        'rm_mni',
        'rm_iv',
        'rm_color_r',
        'rm_color_y',
        'rm_color_w',
        'rm_color_b',
        'fg_ffa',
        'fg_mni',
        'fg_iv',
        'fg_color_r',
        'fg_color_y',
        'fg_color_w',
        'fg_color_b',
        'fg_cp',
        'fg_clarity',
        'fg_to_tank',
        'bp_ffa',
        'bp_mni',
        'bp_iv',
        'bp_pv',
        'bp_color_r',
        'bp_color_y',
        'bp_color_w',
        'bp_color_b',
        'bp_to_tank',
        'remarks',
        'flag',
        'entry_by',
        'entry_date',
        'prepared_by',
        'prepared_date',
        'prepared_status',
        'prepared_status_remarks',
        'checked_by',
        'checked_date',
        'checked_status',
        'checked_status_remarks',
        'updated_by',
        'updated_date',
        'form_no',
        'date_issued',
        'revision_no',
        'revision_date',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'prepared_date' => 'datetime',
        'checked_date' => 'datetime',
        'updated_date' => 'datetime'
    ];
}
