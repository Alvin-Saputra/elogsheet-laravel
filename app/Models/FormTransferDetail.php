<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormTransferDetail extends Model
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
        'form_storage_tank_no',
        'form_refinery_fractionation',
        'form_other',
        'to_storage_tank_no',
        'to_refinery_fractionation',
        'to_auto_filing_tank',
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

    public function header()
    {
        return $this->belongsTo(FormTransferHeader::class, 'id_hdr', 'id');
    }
}
