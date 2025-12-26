<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AROIPFuelDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 't_analytical_result_incoming_plant_fuel_detail';

    protected $primaryKey = 'id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'id_hdr',
        'parameter',
        'result_min',
        'result_max',
        'specification_min',
        'specification_max',
        'status_ok',
        'remark',
    ];

    public function header()
    {
        return $this->belongsTo(AROIPFuelHeader::class, 'id_hdr', 'id');
    }
}
