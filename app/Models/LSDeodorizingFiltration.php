<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LSDeodorizingFiltration extends Model
{
    use HasFactory;

    // Assuming the table name follows convention. Change if it's different.
    protected $table = 't_deodorizing_filtration';

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
        'refinery_machine',
        'time',
        'shift',
        'oil_type_id',
        'oil_type',
        'fit701_bpo',
        'd701_vacum',
        'd701_td701',
        'e702',
        'thermopac_inlet',
        'thermopac_outlet',
        'd702_inlet',
        'd702_outlet',
        'd702_vacum',
        'sparging_a',
        'sparging_b',
        'e730_inlet',
        'steam_inlet',
        'pish_706',
        'tiwh_706',
        'f702_a',
        'f702_b',
        'f702_c',
        'oil_type_fg_id',
        'oil_type_fg',
        'fit704_rpo',
        'e704',
        'oil_type_bp_id',
        'oil_type_bp',
        'fit_705_pfad',
        'e705',
        'clarity',
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
        'posting_date' => 'datetime',
        'time' => 'datetime:H:i:s',

        'fit701_bpo' => 'decimal:3',
        'd701_vacum' => 'decimal:3',
        'd701_td701' => 'decimal:3',
        'e702' => 'decimal:0',
        'thermopac_inlet' => 'decimal:3',
        'thermopac_outlet' => 'decimal:3',
        'd702_inlet' => 'decimal:3',
        'd702_outlet' => 'decimal:3',
        // 'pish_706' => 'decimal:3',
        // 'tiwh_706' => 'decimal:3',
        'f702_a' => 'decimal:3',
        'f702_b' => 'decimal:3',
        'f702_c' => 'decimal:3',
        'fit704_rpo' => 'decimal:3',
        'e704' => 'decimal:3',




        'entry_date' => 'datetime',
        'prepared_date' => 'datetime',
        'checked_date' => 'datetime',
        'updated_date' => 'datetime',
        'date_issued' => 'datetime',
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
}
