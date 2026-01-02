<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ARIMByVesselHeader extends Model
{
    use HasFactory;

    protected $table = "t_analytical_result_incoming_material_by_vessel";

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        "id",
        "company",
        "plant",
        "transaction_date",
        "material",
        "arrival",
        "quantity",
        "supplier",
        "ship_name",
        "contract_do_nomor",
        "ffa",
        "mni",
        "dobi",
        "others",
        "hasil_analisa_ffa",
        "hasil_analisa_iv",
        "hasil_analisa_moisture",
        "hasil_analisa_dobi",
        "hasil_analisa_pv",
        "hasil_analisa_anv",
        "remarks",
        "flag",
        "entry_by",
        "entry_date",
        "prepared_by",
        "prepared_date",
        "prepared_status",
        "prepared_status_remarks",
        "approved_by",
        "approved_date",
        "approved_status",
        "approved_status_remarks",
        "updated_by",
        "updated_date",
        "form_no",
        "date_issued",
        "revision_no",
        "revision_date"
    ];

    protected $casts = [
        'arrival' => "datetime",
        "entry_date" => "datetime",
        "prepared_date" => "datetime",
        "approved_date" => "datetime",
        "updated_date" => "datetime",
        "date_issued" => "datetime",
        "revision_date" => "datetime",
    ];

    /**
     * Detail rows for this header
     */
    public function details()
    {
        return $this->hasMany(ARIMByVesselDetail::class, 'id_hdr', 'id');
    }

    public function getTransactionDateAttribute($value)
    {
        return $value
            ? Carbon::parse($value)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s')
            : null;
    }

    public function getArrivalAttribute($value)
    {
        return $value
            ? Carbon::parse($value)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s')
            : null;
    }

    public function getEntryDateAttribute($value)
    {
        return $value
            ? Carbon::parse($value)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s')
            : null;
    }

    public function getPreparedDateAttribute($value)
    {
        return $value
            ? Carbon::parse($value)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s')
            : null;
    }

    public function getApprovedDateAttribute($value)
    {
        return $value
            ? Carbon::parse($value)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s')
            : null;
    }

    public function getUpdatedDateAttribute($value)
    {
        return $value
            ? Carbon::parse($value)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s')
            : null;
    }

    public function getDateIssuedAttribute($value)
    {
        return $value
            ? Carbon::parse($value)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s')
            : null;
    }
}
