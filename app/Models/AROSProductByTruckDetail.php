<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AROSProductByTruckDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 't_analytical_result_outgoing_shipment_product_truck_detail';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'id_hdr',
        'ships_tank',
        'no_police',
        'ffa',
        'm_and_i',
        'iv',
        'lovibond_color_red',
        'lovibond_color_yellow',
        'pv',
        'other',
        'remark',
    ];

    /**
     * Detail belongs to Header
     */
    public function header()
    {
        return $this->belongsTo(
            AROSProductByTruckHeader::class,
            'id_hdr',
            'id'
        );
    }
}
