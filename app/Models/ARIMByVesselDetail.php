<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ARIMByVesselDetail extends Model
{
    use HasFactory;

    protected $table = 't_analytical_result_incoming_material_by_vessel_detail';

    // Composite primary key (id, id_hdr) in DB; Eloquent doesn't support composite PKs
    // so mark model as non-incrementing and use string key type. When querying/updating
    // prefer using where('id', $id)->where('id_hdr', $hdr).
    public $incrementing = false;
    protected $primaryKey = null;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'id_hdr',
        'palka_s_no',
        'palka_s_ffa',
        'palka_s_iv',
        'palka_s_dobi',
        'palka_s_mni',
        'palka_c_no',
        'palka_c_ffa',
        'palka_c_iv',
        'palka_c_dobi',
        'palka_c_mni',
        'palka_p_no',
        'palka_p_ffa',
        'palka_p_iv',
        'palka_p_dobi',
        'palka_p_mni',
    ];

    protected $casts = [
        'palka_s_ffa' => 'float',
        'palka_s_iv' => 'float',
        'palka_s_dobi' => 'float',
        'palka_s_mni' => 'float',
        'palka_c_ffa' => 'float',
        'palka_c_iv' => 'float',
        'palka_c_dobi' => 'float',
        'palka_c_mni' => 'float',
        'palka_p_ffa' => 'float',
        'palka_p_iv' => 'float',
        'palka_p_dobi' => 'float',
        'palka_p_mni' => 'float',
    ];

    /**
     * Relationship to header record
     */
    public function header()
    {
        return $this->belongsTo(ARIMByVesselHeader::class, 'id_hdr', 'id');
    }
}
