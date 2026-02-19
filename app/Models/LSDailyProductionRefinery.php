<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LSDailyProductionRefinery extends Model
{
    use HasFactory;

    protected $table = 't_daily_production_refinery';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'company',
        'plant',
        'transaction_date',
        'posting_date',
        'work_center',
        'shift',
        'cpo_tank',
        'oil_type_rm_id',
        'oil_type_rm',
        'oil_type_rm_awal_jam',
        'oil_type_rm_awal_flowmeter',
        'oil_type_rm_akhir_jam',
        'oil_type_rm_akhir_flowmeter',
        'oil_type_rm_total',
        'oil_type_fg_id',
        'oil_type_fg',
        'oil_type_fg_awal_jam',
        'oil_type_fg_awal_flowmeter',
        'oil_type_fg_akhir_jam',
        'oil_type_fg_akhir_flowmeter',
        'oil_type_fg_total',
        'oil_type_fg_to_tank',
        'bp_awal_jam',
        'bp_awal_flowmeter',
        'bp_akhir_jam',
        'bp_akhir_flowmeter',
        'bp_oil_type_id',
        'bp_oil_type',
        'bp_total',
        'bp_to_tank',
        'be_ref_tank',
        'be_ref_qty',
        'be_total_bag',
        'be_total_jenis',
        'be_lot_batch_number',
        'be_yield_percent',
        'pa_ref_tank',
        'pa_ref_qty',
        'pa_total',
        'pa_lot_batch_number',
        'pa_yield_percent',
        'remarks',
        'flag',
        'uu_item',
        'uu_budget_ref_tank',
        'uu_budget_qty',
        'uu_total_cpo',
        'uu_total_steam',
        'uu_steam_cpo',
        'uu_yield_percent',
        'entry_by',
        'entry_date',
        'prepared_by',
        'prepared_date',
        'prepared_status',
        'prepared_status_remarks',
        'verified_by',
        'verified_date',
        'verified_status',
        'checked_by',
        'checked_date',
        'checked_status',
        'checked_status_remarks',
        'form_no',
        'date_issued',
        'revision_no',
        'revision_date',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'posting_date' => 'datetime',

        'oil_type_rm_awal_jam' => 'datetime:H:i:s',
        'oil_type_rm_awal_flowmeter' => 'decimal:3',
        'oil_type_rm_akhir_jam' => 'datetime:H:i:s',
        'oil_type_rm_akhir_flowmeter' => 'decimal:3',
        'oil_type_rm_total' => 'decimal:3',

        'oil_type_fg_awal_jam' => 'datetime:H:i:s',
        'oil_type_fg_awal_flowmeter' => 'decimal:3',
        'oil_type_fg_akhir_jam' => 'datetime:H:i:s',
        'oil_type_fg_akhir_flowmeter' => 'decimal:3',
        'oil_type_fg_total' => 'decimal:3',

        'bp_awal_jam' => 'datetime:H:i:s',
        'bp_awal_flowmeter' => 'decimal:3',
        'bp_akhir_jam' => 'datetime:H:i:s',
        'bp_akhir_flowmeter' => 'decimal:3',
        'bp_total' => 'decimal:3',

        'be_lot_batch_number' => 'integer',
        'be_yield_percent' => 'decimal:3',

        'pa_lot_batch_number' => 'integer',
        'pa_yield_percent' => 'float',

        'uu_total_cpo' => 'decimal:3',
        'uu_total_steam' => 'decimal:3',
        'uu_yield_percent' => 'decimal:3',

        'entry_date' => 'datetime',
        'prepared_date' => 'datetime',
        'verified_date' => 'datetime',
        'checked_date' => 'datetime',
        'date_issued' => 'datetime',
        'revision_no' => 'integer',
        'revision_date' => 'datetime',
    ];


    protected static function booted()
    {
        static::addGlobalScope('plant', function ($query) {
            if ($plant = session('plant_code')) {
                $query->where('plant', $plant);
            }
        });
    }

    public function qualityReports()
    {
        // Parameter: (Model Child, Foreign Key di tabel child, Local Key di tabel ini)
        return $this->hasMany(LSQualityReportQc::class, 'daily_production_refinery_id', 'id');
    }
}
