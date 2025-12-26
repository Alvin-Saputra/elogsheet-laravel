<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ROAHeader extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'roa_analytical_result_incoming_plant_fuel';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'report_no',
        'shipper',
        'buyer',
        'date_received',
        'date_analyzed_start',
        'date_analyzed_end',
        'date_reported',
        'lab_sample_id',
        'customer_sample_id',
        'seal_no',
        'weight_of_received_sample',
        'top_size_of_received_sample',
        'authorized_by',
        'authorized_date'
    ];

    public function aroipFuelHeaders()
    {
        return $this->hasMany(AROIPFuelHeader::class, 'id_coa', 'id');
    }

    public function details()
    {
        return $this->hasMany(ROADetail::class, 'id_hdr', 'id');
    }
}
