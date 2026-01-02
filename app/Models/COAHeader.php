<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class COAHeader extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'coa_incoming_plant_chemical_ingredient_header';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'no_doc',
        'product',
        'grade',
        'packing',
        'quantity',
        'tanggal_pengiriman',
        'vehicle',
        'lot_no',
        'production_date',
        'expired_date',
        'issue_by',
        'issue_date',
        'updated_by',
        'updated_date',
    ];

    public function aroipChemicalHeaders()
    {
        return $this->hasMany(AROIPChemicalHeader::class, 'id_coa', 'id');
    }

    public function details()
    {
        return $this->hasMany(COADetail::class, 'id_hdr', 'id');
    }

    protected static function booted()
    {
        static::updated(function (COAHeader $coaHeader) {
            // Check if the 'no_doc' column was one of the fields changed in this update
            if ($coaHeader->isDirty('no_doc')) {
                // Update all related AROIPChemicalHeader records
                $coaHeader->aroipChemicalHeaders()->update([
                    'no_ref_coa' => $coaHeader->no_doc,
                ]);
            }
        });
    }
}
