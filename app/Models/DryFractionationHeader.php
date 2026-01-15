<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DryFractionationHeader extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     */
    protected $table = 't_dry_fractionation_header';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'date',
        'posting_date',
        'company',
        'plant',
        'crystallizer',
        'feed_oil_iv',
        'filling_start_time',
        'filling_end_time',
        'initial_oil_level',
        'cooling_start_temp',
        'cooling_start_time',
        'agitator_speed',
        'water_pump_pres',
        'remarks',
        'flag',
        'entry_by',
        'entry_date', // Di-handle otomatis oleh CREATED_AT
        'prepared_by',
        'prepared_date',
        'prepared_status',
        'prepared_status_remarks',
        'checked_by',
        'checked_date',
        'checked_status',
        'checked_status_remarks',
        'updated_by',
        'updated_date', // Di-handle otomatis oleh UPDATED_AT
        'form_no',
        'date_issued',
        'revision_no',
        'revision_date',
        'is_completed'
    ];

    /**
     * Casting tipe data kolom.
     * Berguna agar saat response JSON, format datanya sesuai (tidak semua string).
     */
    protected $casts = [
        // 'date' => 'datetime',
        // 'posting_date' => 'datetime',
        // 'filling_start_time' => 'datetime',
        // 'filling_end_time' => 'datetime',
        // 'cooling_start_time' => 'datetime',
        // 'entry_date' => 'datetime',
        // 'prepared_date' => 'datetime',
        // 'checked_date' => 'datetime',
        // 'updated_date' => 'datetime',
        // 'revision_date' => 'datetime',
        // 'date_issued' => 'datetime',
        // 'is_completed' => 'boolean',
        // 'flag' => 'boolean',
        // Kolom numerik (float/decimal) biasanya otomatis, tapi bisa dipertegas:
        // 'feed_oil_iv' => 'decimal:2',
        // 'initial_oil_level' => 'decimal:2',
    ];

    /**
     * Relasi ke tabel detail.
     * Diasumsikan nama model detail adalah DryFractionationDetail.
     */
    public function details(): HasMany
    {
        // Parameter: Related Model, Foreign Key, Local Key
        return $this->hasMany(DryFractionationDetail::class, 'id_hdr', 'id');
    }
}
