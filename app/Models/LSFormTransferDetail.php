<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LSFormTransferDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 't_form_transfer_detail';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'id_hdr',
        'oil_type',
        'quantity',
        'from_storage_tank_no',
        'from_refinery_fractionation',
        'from_other',
        'to_storage_tank_no',
        'to_refinery_fractionation',
        'to_auto_filling_tank',
        'to_other',
        'quality_m_and_i',
        'quality_ffa',
        'quality_lov_color_r',
        'quality_lov_color_y',
        'quality_cp_temp',
        'quality_smp',
        'quality_pv',
        'quality_iv',
        'remark',
    ];

    protected $casts = [
        'to_auto_filling_tank' => 'integer',
        'quality_m_and_i' => 'decimal:3',
        'quality_ffa' => 'decimal:3',
        'quality_lov_color_r' => 'decimal:3',
        'quality_lov_color_y' => 'decimal:3',
        'quality_cp_temp' => 'decimal:3',
        'quality_smp' => 'decimal:3',
        'quality_pv' => 'decimal:3',
        'quality_iv' => 'decimal:3',
    ];

    public function header()
    {
        return $this->belongsTo(LSFormTransferHeader::class, 'id_hdr', 'id');
    }
}
