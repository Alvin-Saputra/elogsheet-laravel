<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class COAHeader extends Model
{
    use HasFactory;
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
    ];

    // COA header -> COA details
    public function details()
    {
        return $this->hasMany(COADetail::class, 'id_hdr', 'id');
    }

    // COA header -> Analytical headers (1 coa could have many analytical results)
    public function analytical()
    {
        return $this->hasMany(AROIPChemicalHeader::class, 'id_coa', 'id');
    }
}
