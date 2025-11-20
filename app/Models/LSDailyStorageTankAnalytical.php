<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LSDailyStorageTankAnalytical extends Model
{
  use HasFactory;

  protected $table = 't_daily_storage_tank_analytical_report';
  protected $primaryKey = 'id';

  public $incrementing = false;
  protected $keyType = 'int';

  public $timestamps = false;

  protected $fillable = [
    'id',
    'company',
    'plant',
    'transaction_date',
    'posting_date',
    'tank_no',
    'oil_type',
    'kapasitas_tanki',
    'quantity',
    'empty_space',
    'suhu',
    'qp_ffa',
    'qp_moisture',
    'qp_lovibond_color_r',
    'qp_lovibond_color_y',
    'qp_iv',
    'qp_pv',
    'qp_slip_melting_point',
    'qp_cloud_point',
    'qp_anv',
    'qp_beta_carotene',
    'qp_p',
    'qp_dobi',
    'qp_totox',
    'qp_odor',
    'remarks',
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
    'form_no',
    'date_issued',
    'revision_no',
    'revision_date',
  ];

  protected $casts = [
    'transaction_date' => 'datetime',
  ];
}
