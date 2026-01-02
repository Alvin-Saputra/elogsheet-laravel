<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AROIPFuelHeader extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 't_analytical_result_incoming_plant_fuel';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'id_roa',
        'company',
        'plant',
        'date',
        'material',
        'quantity',
        'supplier',
        'police_no',
        'analyst',
        'entry_by',
        'entry_date',
        'prepared_by',
        'prepared_date',
        'prepared_status',
        'prepared_status_remarks',
        'approved_by',
        'approved_date',
        'approved_status',
        'approved_status_remarks',
        'updated_by',
        'updated_date',
        'form_no',
        'date_issued',
        'revision_no',
        'revision_date',
    ];

     public function roa()
    {
        return $this->belongsTo(ROAHeader::class, 'id_roa', 'id');
    }

     public function details()
    {
        return $this->hasMany(AROIPFuelDetail::class, 'id_hdr', 'id');
    }
}
