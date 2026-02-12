<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LSFormTransferHeader extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 't_form_transfer_header';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'company',
        'plant',
        'transaction_date',
        'to_dept',
        'from_dept',
        'form_no',
        'date_issued',
        'revision_no',
        'revision_date',
        'flag',
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
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'date_issued' => 'date',
        'revision_no' => 'integer',
        'revision_date' => 'date',
        'entry_date' => 'datetime',
        'prepared_date' => 'datetime',
        'approved_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(LSFormTransferDetail::class, 'id_hdr', 'id');
    }
}
