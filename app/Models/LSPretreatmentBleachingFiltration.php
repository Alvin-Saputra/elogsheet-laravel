<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LSPretreatmentBleachingFiltration extends Model
{
    use HasFactory;
    protected $table = 't_pretreatment_bleaching_filtration';

    protected $primaryKey = 'id';

    public $incrementing = false;

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
        'pt_fit001',
        'pt_e001a_inlet',
        'pt_f0012',
        'pt_h3po4',
        'pt_be',
        'bl_vacum',
        'bl_t_inlet',
        'bl_t_b602',
        'bl_spurge',
        'p_a',
        'p_b',
        'p_c',
        'fn_f601',
        'fn_f602',
        'fn_f603',
        'fb_604a',
        'fb_604b',
        'fb_604c',
        'fc_605a',
        'fc_605b',
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
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'posting_date' => 'datetime',
        'time' => 'datetime:H:i:s',


        'pt_fit001' => 'decimal:3',
        'pt_e001a_inlet' => 'decimal:3',
        'pt_f0012' => 'decimal:3',

        'pt_h3po4' => 'decimal:3',
        'pt_be' => 'decimal:3',

        'bl_t_inlet' => 'decimal:3',
        'bl_t_b602' => 'decimal:3',
        'bl_spurge' => 'decimal:3',

        'p_a' => 'decimal:3',
        'p_b' => 'decimal:3',
        'p_c' => 'decimal:3',

        'fn_f601' => 'decimal:3',
        'fn_f602' => 'decimal:3',
        'fn_f603' => 'decimal:3',

        'fb_604a' => 'decimal:3',
        'fb_604b' => 'decimal:3',
        'fb_604c' => 'decimal:3',
        'fc_605a' => 'decimal:3',
        'fc_605c' => 'decimal:3',

        'entry_date' => 'datetime',
        'prepared_date' => 'datetime',
        'checked_date' => 'datetime',

        'updated_date' => 'datetime',
    ];
}