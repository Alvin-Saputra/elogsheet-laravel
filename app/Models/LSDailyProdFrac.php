<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LSDailyProdFrac extends Model
{
    use HasFactory;

    protected $table = 't_daily_production_fractionation';
    protected $primaryKey = 'id'; // Asumsi ID tetap unik per baris
    public $incrementing = false; // Jika ID adalah UUID/String
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
        'no', // Kolom 'no' baru pengganti sequence

        // Group Raw Material
        'oil_type_rm',
        'oil_type_rm_cr',
        'oil_type_rm_from_tank',
        'oil_type_rm_awal_jam',
        'oil_type_rm_awal_flowmeter',
        'oil_type_rm_akhir_jam',
        'oil_type_rm_akhir_flowmeter',
        'oil_type_rm_total',

        // Group Finish Good
        'oil_type_fgs',
        'oil_type_fgs_cr',
        'oil_type_fgs_awal_jam',
        'oil_type_fgs_awal_flowmeter',
        'oil_type_fgs_akhir_jam',
        'oil_type_fgs_akhir_flowmeter',
        'oil_type_fgs_total',
        'oil_type_fgs_to_tank',

        // Group Finish Good H (By Product)
        'oil_type_fgh',
        'oil_type_fgh_awal_jam',
        'oil_type_fgh_awal_flowmeter',
        'oil_type_fgh_akhir_jam',
        'oil_type_fgh_akhir_flowmeter',
        'oil_type_fgh_total',
        'oil_type_fgh_to_tank',

        // Utilities & Metrics
        'remarks',
        'flag',
        'is_completed', // is_completed baru
        'uu_item',
        'uu_budget_ref_qty',
        'uu_flowmeter_before',
        'uu_flowmeter_after',
        'uu_flowmeter_total',
        'uu_yield_percent',
        'uu_listrik',
        'uu_air',

        // Approval Columns
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

        // Document Info
        'form_no',
        'date_issued',
        'revision_no',
        'revision_date',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'posting_date' => 'date',
        'no' => 'integer',
        'is_completed' => 'boolean',
        // ... cast lainnya sesuaikan dengan tipe data database
    ];
}
