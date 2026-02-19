<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualityReportRefinery extends Model
{
    use HasFactory;

    protected $table = 't_quality_report_refinery_eup';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    // Tambahkan ini untuk menonaktifkan created_at dan updated_at
    public $timestamps = false;

    protected $fillable = [
        'id',
        'report_date',
        'time',
        'p_cat',
        'p_tank_source',
        'p_flowrate',
        'p_ffa',
        'p_iv',
        'p_pv',
        'p_anv',
        'p_dobi',
        'p_carotene',
        'p_m&i',
        'p_color',
        'c_cat',
        'c_pa',
        'c_be',
        'b_cat',
        'b_color_r',
        'b_color_y',
        'b_break_test',
        'r_cat',
        'r_ffa',
        'r_color_r',
        'r_color_y',
        'r_color_b',
        'r_pv',
        'r_m&i',
        'r_product_tank_no',
        'fp_cat',
        'fp_purity',
        'fp_product_tank_no',
        'spent_earth_oic',
        'pic',
        'remarks',
        'checked_by',
        'checked_date',
        'checked_time',
        'approved_by',
        'approved_date',
        'approved_time',
        'flag',
        'company',
        'plant',
        'entry_by',
        'entry_date',
    ];

    protected $casts = [
        'report_date' => 'datetime',
        'time' => 'datetime:H:i:s',
        'checked_date' => 'date',
        'checked_time' => 'datetime:H:i:s',
        'approved_date' => 'date',
        'approved_time' => 'datetime:H:i:s',
        'entry_date' => 'datetime',
        'p_tank_source' => 'decimal:0',
        'p_flowrate' => 'decimal:0',
        'p_ffa' => 'decimal:2',
        'p_iv' => 'decimal:2',
        'p_pv' => 'decimal:2',
        'p_anv' => 'decimal:2',
        'p_dobi' => 'decimal:2',
        'p_carotene' => 'decimal:2',
        'p_m&i' => 'decimal:2',
        'c_pa' => 'decimal:2',
        'c_be' => 'decimal:2',
        'b_color_r' => 'decimal:0',
        'b_color_y' => 'decimal:0',
        'r_ffa' => 'decimal:2',
        'r_color_r' => 'decimal:0',
        'r_color_y' => 'decimal:0',
        'r_color_b' => 'decimal:0',
        'r_pv' => 'decimal:0',
        'r_m&i' => 'decimal:0',
        'r_product_tank_no' => 'decimal:0',
        'fp_purity' => 'decimal:2',
        'fp_product_tank_no' => 'decimal:0',
        'spent_earth_oic' => 'decimal:2',
    ];

      protected static function booted()
    {
        static::addGlobalScope('plant', function ($query) {
            if ($plant = session('plant_code')) {
                $query->where('plant', $plant);
            }
        });
    }
}
