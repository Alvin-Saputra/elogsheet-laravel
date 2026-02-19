<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LSStartUpProduksiChecklistHeader extends Model
{
    use HasFactory;

    protected $table = 't_startup_produksi_checklist';

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'company',
        'plant',
        'transaction_date',
        'transaction_time',
        'product',
        'work_center',
        'remarks',
        'flag',
        'entry_by',
        'entry_date',
        'prepared_by',
        'prepared_date',
        'prepared_status',
        'prepared_status_remarks',
        'checked_by',
        'checked_date',
        'checked_status',
        'checked_status_remarks',
        'updated_by',
        'updated_date',
        'form_no',
        'date_issued',
        'revision_no',
        'revision_date'
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'transaction_time' => 'datetime:H:i:s',
        'entry_date' => 'datetime',
        'prepared_date' => 'datetime',
        'checked_date' => 'datetime',
        'updated_date' => 'datetime',
        'date_issued' => 'datetime',
        'revision_no' => 'integer',
        'revision_date' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(LSStartUpProduksiChecklistDetail::class, 'id_hdr', 'id');
    }

    public function oilProduct()
    {
        return $this->belongsTo(MProduct::class, 'product', 'id');
    }

      protected static function booted()
    {
        static::addGlobalScope('plant', function ($query) {
            if ($plant = session('plant_code')) {
                $query->where('plant', $plant);
            }
        });
    }
}
