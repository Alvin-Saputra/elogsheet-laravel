<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AROIPChemicalDetail extends Model
{
    use HasFactory;

    protected $table = 't_analytical_result_incoming_plant_chemical_ingredient_detail';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'id_hdr',
        'specification_min',
        'specification_max',
        'status_ok',
        'parameter',
        'result_min',
        'result_max',
        'remark'
    ];

    // detail -> analytical header
    public function header()
    {
        return $this->belongsTo(AROIPChemicalHeader::class, 'id_hdr', 'id');
    }
}