<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ROADetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'roa_analytical_result_incoming_plant_fuel_detail';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'id_hdr',
        'parameter',
        'unit',
        'basis',
        'result',
    ];

    // detail -> coa header (belongsTo)
    public function header()
    {
        return $this->belongsTo(ROAHeader::class, 'id_hdr', 'id');
    }
}
