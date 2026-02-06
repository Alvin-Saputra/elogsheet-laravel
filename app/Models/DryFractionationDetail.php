<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DryFractionationDetail extends Model
{
    use HasFactory;
    protected $table = 't_dry_fractionation_detail';
    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'id_hdr',
        'filtration_cycle_number',
        'filtration_date',
        'filtration_temp',
        'time_start_filtration',
        'time_end_filtration',
        'load',
        'olein_iv',
        'olein_cp',
        'olein_ffa',
        'olein_color_red',
        'stearin_iv',
        'stearin_ffa',
        'stearin_color_red',
        'stearin_pv'
    ];


    protected $casts = [

        // 'filtration_date' => 'datetime',

    ];

    public function header()
    {
        return $this->belongsTo(DryFractionationHeader::class, 'id_hdr', 'id');
    }
}
