<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AROSByVesselHeader extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 't_analytical_result_outgoing_shipment_by_vessel';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    // there are no created_at / updated_at columns in your DDL
    public $timestamps = false;

    protected $fillable = [
        'id',
        'company',
        'plant',
        'product_name',
        'sampling_date',
        'quantity',
        'shipper',
        'destination',
        'vessel_name',
        'hasil_analisa_ffa',
        'hasil_analisa_iv',
        'hasil_analisa_moisture',
        'hasil_analisa_colour',
        'hasil_analisa_pv',
        'hasil_analisa_smp',
        'remark',
        'entry_by',
        'entry_date',
        'prepared_by',
        'prepared_date',
        'prepared_status',
        'prepared_status_remarks',
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

    protected $casts = [
        'sampling_date' => 'datetime',
        'entry_date' => 'datetime',
        'prepared_date' => 'datetime',
        'approved_date' => 'datetime',
        'updated_date' => 'datetime',
        'date_issued' => 'datetime',
        'revision_date' => 'datetime',

        // numeric casts (preserve scale)
        'quantity' => 'decimal:3',
        'hasil_analisa_ffa' => 'decimal:3',
        'hasil_analisa_iv' => 'decimal:3',
        'hasil_analisa_moisture' => 'decimal:3',
        'hasil_analisa_colour' => 'decimal:3',
        'hasil_analisa_pv' => 'decimal:3',
        'hasil_analisa_smp' => 'decimal:3',
    ];


     protected static function booted()
    {
        static::addGlobalScope('plant', function ($query) {
            if ($plant = session('plant_code')) {
                $query->where('plant', $plant);
            }
        });
    }

    public function details()
    {
        return $this->hasMany(AROSByVesselDetail::class, 'id_hdr', 'id');
    }

     public function preparedByUser()
    {
        return $this->belongsTo(MUser::class, 'prepared_by', 'username');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(MUser::class, 'approved_by', 'username');
    }

    public function entriedByUser()
    {
        return $this->belongsTo(MUser::class, 'entry_by', 'username');
    }
}
