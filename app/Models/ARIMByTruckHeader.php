<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
