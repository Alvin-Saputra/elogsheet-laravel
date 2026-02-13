<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ARIMByTruckDetail extends Model
{
    use HasFactory;

    protected $table = 't_analytical_result_incoming_material_by_truck_detail';

    // Composite primary key (id, id_hdr) in DB; Eloquent doesn't support composite PKs
    // so mark model as non-incrementing and use string key type. When querying/updating
    // prefer using where('id', $id)->where('id_hdr', $hdr).
    public $incrementing = false;
    protected $primaryKey = null;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        "id",
        "id_hdr",
        "no",
        "sampling_date",
        "police_no",
        "p_ffa",
        "p_moisture",
        "p_iv",
        "p_dobi",
        "p_pv",
        "p_color_r",
        "p_color_y",
        "analis",
        "remarks"
    ];

    protected $casts = [
        'p_ffa' => 'decimal:3',
        'p_moisture' => 'decimal:3',
        'p_iv' => 'decimal:3',
        'p_dobi' => 'decimal:3',
        'p_pv' => 'decimal:3',
        'p_color_r' => 'decimal:3',
        'p_color_y' => 'decimal:3',
        'palka_p_iv' => 'decimal:3',
        'palka_p_dobi' => 'decimal:3',
        'palka_p_mni' => 'decimal:3',
        // "sampling_date" => "datetime",
    ];

    /**
     * Relationship to header record
     */
    public function header()
    {
        return $this->belongsTo(ARIMByVesselHeader::class, 'id_hdr', 'id');
    }

    // public function getSamplingDateAttribute($value)
    // {
    //     return $value
    //         ? Carbon::parse($value)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s')
    //         : null;
    // }
}
