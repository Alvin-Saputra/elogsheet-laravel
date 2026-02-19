<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LSLampGlassHeader extends Model
{
  use HasFactory;

  protected $table = 't_checklist_lamps_glass_control';
  protected $keyType = 'string';
  public $timestamps = false;

  protected $fillable = [
    'id',
    'company',
    'plant',
    'work_center',
    'check_date',
    'remarks',
    'entry_by',
    'entry_date',
    'checked_by',
    'checked_date',
    'checked_status',
    'checked_status_remarks'
  ];

  protected $casts = [
    'id' => 'string',
    'company' => 'string',
    'plant' => 'string',
    'work_center' => 'string',
    'check_date' => 'date',              // hanya tanggal
    'remarks' => 'string',
    'entry_by' => 'string',
    'entry_date' => 'datetime',          // tanggal + jam
    'checked_by' => 'string',
    'checked_date' => 'datetime',
    'checked_status' => 'string',        // bisa juga enum/boolean kalau datanya T/F
    'checked_status_remarks' => 'string',
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
    return $this->hasMany(LSLampGlassDetail::class, 'id_hdr', 'id');
  }
}
