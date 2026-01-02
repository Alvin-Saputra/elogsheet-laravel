<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ARIMByTruckHeader extends Model
{
    protected $table = "t_analytical_result_incoming_material_by_truck";

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
        "arrival_date",
        "contract_do",
        "supplier",
        "vessel_vehicle",
        "ss_ffa",
        "ss_mni",
        "ss_others",
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
        "revision_date",
    ];

    protected $casts = [
        "arrival_date" => "datetime",
        "transaction_date" => "datetime",
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
        return $this->hasMany(ARIMByTruckDetail::class, 'id_hdr', 'id');
    }

    public function getTransactionDateAttribute($value)
    {
        return $value
            ? Carbon::parse($value)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s')
            : null;
    }

    public function getArrivalDateAttribute($value)
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
