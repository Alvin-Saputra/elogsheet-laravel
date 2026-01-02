<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AROSProductByTruckHeader extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 't_analytical_result_outgoing_shipment_product_truck';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;


    protected $fillable = [
        'id',
        'company',
        'plant',
        'loading_date',
        'product_name',
        'quantity',
        'ships_name',
        'destination',
        'load_port',
        'entry_by',
        'entry_date',
        'corrected_by',
        'corrected_date',
        'corrected_status',
        'corrected_status_remarks',
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

    /**
     * One Header has many Details
     */
    public function details()
    {
        return $this->hasMany(
            AROSProductByTruckDetail::class,
            'id_hdr',
            'id'
        );
    }
}
