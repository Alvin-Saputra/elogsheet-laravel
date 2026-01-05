<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AROSByVesselDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 't_analytical_result_outgoing_shipment_by_vessel_detail';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'id_hdr',
        'palka_s_palka',
        'palka_s_ffa',
        'palka_s_iv',
        'palka_s_colour',
        'palka_s_pv',
        'palka_s_mni',
        'palka_p_palka',
        'palka_p_ffa',
        'palka_p_iv',
        'palka_p_colour',
        'palka_p_pv',
        'palka_p_mni',
    ];

    protected $casts = [
        'palka_s_palka' => 'decimal:4',
        'palka_s_ffa' => 'decimal:4',
        'palka_s_iv' => 'decimal:4',
        'palka_s_colour' => 'decimal:4',
        'palka_s_pv' => 'decimal:4',
        'palka_s_mni' => 'decimal:4',
        'palka_p_palka' => 'decimal:4',
        'palka_p_ffa' => 'decimal:4',
        'palka_p_iv' => 'decimal:4',
        'palka_p_colour' => 'decimal:4',
        'palka_p_pv' => 'decimal:4',
        'palka_p_mni' => 'decimal:4',
    ];

    public function header()
    {
        return $this->belongsTo(AROSByVesselHeader::class, 'id_hdr', 'id');
    }
}
