<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LSDailyProductionFractionation extends Model
{
    use HasFactory;


    protected $table = 't_daily_production_fractionation';
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
        'oil_type_rm_id',
        'oil_type_rm',
        'oil_type_rm_no',
        'oil_type_rm_cr',
        'oil_type_rm_from_tank',
        'oil_type_rm_awal_jam',
        'oil_type_rm_awal_flowmeter',
        'oil_type_rm_akhir_jam',
        'oil_type_rm_akhir_flowmeter',
        'oil_type_rm_total',
        'oil_type_fgs_id',
        'oil_type_fgs',
        'oil_type_fgs_no',
        'oil_type_fgs_cr',
        'oil_type_fgs_awal_jam',
        'oil_type_fgs_awal_flowmeter',
        'oil_type_fgs_akhir_jam',
        'oil_type_fgs_akhir_flowmeter',
        'oil_type_fgs_total',
        'oil_type_fgs_to_tank',
        'oil_type_fgh_id',
        'oil_type_fgh',
        'oil_type_fgh_awal_jam',
        'oil_type_fgh_awal_flowmeter',
        'oil_type_fgh_akhir_jam',
        'oil_type_fgh_akhir_flowmeter',
        'oil_type_fgh_total',
        'oil_type_fgh_to_tank',
        'remarks',
        'flag',
        'oil_type_fgh_no',
        'uu_item',
        'uu_budget_ref_qty',
        'uu_flowmeter_before',
        'uu_flowmeter_after',
        'uu_flowmeter_total',
        'uu_yield_percent',
        'uu_listrik',
        'uu_air',
        'entry_by',
        'entry_date',
        'prepared_by',
        'prepared_date',
        'prepared_status',
        'prepared_status_remarks',
        'verified_by',
        'verified_date',
        'verified_status',
        'verified_status_remarks',
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

        'oil_type_rm_no' => 'integer',
        // 'oil_type_rm_cr' => 'integer',
        'oil_type_rm_awal_jam' => 'datetime:H:i:s',
        'oil_type_rm_awal_flowmeter' => 'decimal:3',
        'oil_type_rm_akhir_jam' => 'datetime:H:i:s',
        'oil_type_rm_akhir_flowmeter' => 'decimal:3',
        'oil_type_rm_total' => 'decimal:3',

        'oil_type_fgs_no' => 'integer',
        // 'oil_type_fgs_cr' => 'integer',
        'oil_type_fgs_awal_jam' => 'datetime:H:i:s',
        'oil_type_fgs_awal_flowmeter' => 'decimal:3',
        'oil_type_fgs_akhir_jam' => 'datetime:H:i:s',
        'oil_type_fgs_akhir_flowmeter' => 'decimal:3',
        'oil_type_fgs_total' => 'decimal:3',

        'oil_type_fgh_awal_jam' => 'datetime:H:i:s',
        'oil_type_fgh_awal_flowmeter' => 'decimal:3',
        'oil_type_fgh_akhir_jam' => 'datetime:H:i:s',
        'oil_type_fgh_akhir_flowmeter' => 'decimal:3',
        'oil_type_fgh_total' => 'decimal:3',
        'oil_type_fgh_no' => 'integer',

        'uu_flowmeter_before' => 'decimal:3',
        'uu_flowmeter_after' => 'decimal:3',
        'uu_flowmeter_total' => 'decimal:3',
        'uu_yield_percent' => 'decimal:3',
        'uu_listrik' => 'decimal:3',
        'uu_air' => 'decimal:3',

        'entry_date' => 'datetime',
        'prepared_date' => 'datetime',
        'verified_date' => 'datetime',
        'checked_date' => 'datetime',
        'date_issued' => 'datetime',
        'revision_no' => 'integer',
        'revision_date' => 'datetime',
    ];


}
