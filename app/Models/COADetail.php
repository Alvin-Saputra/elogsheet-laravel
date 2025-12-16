<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class COADetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'coa_incoming_plant_chemical_ingredient_detail';
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'id_hdr',
        'parameter',
        'actual_min',
        'actual_max',
        'standard_min',
        'standard_max',
        'method'
    ];

    // detail -> coa header (belongsTo)
    public function header()
    {
        return $this->belongsTo(COAHeader::class, 'id_hdr', 'id');
    }
}
