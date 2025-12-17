<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AROIPChemicalHeader extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 't_analytical_result_incoming_plant_chemical_ingredient';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'id_coa',
        'no_ref_coa',
        'material',
        'quantity',
        'analyst',
        'supplier',
        'police_no',
        'batch_lot',
        'status',
        'entry_by',
        'entry_date',
        'form_no',
        'date_issued',
        'revision_no',
        'revision_date',
        'prepared_by',
        'prepared_date',
        'prepared_status',
        'prepared_status_remarks',
        'prepared_role',
        'approved_by',
        'approved_date',
        'approved_status',
        'approved_status_remarks',
        'approved_role',
        'updated_by',
        'updated_date',
        'date',
        'exp_date',
    ];

    public function coa()
    {
        return $this->belongsTo(COAHeader::class, 'id_coa', 'id');
    }

    public function details()
    {
        return $this->hasMany(AROIPChemicalDetail::class, 'id_hdr', 'id');
    }
}
