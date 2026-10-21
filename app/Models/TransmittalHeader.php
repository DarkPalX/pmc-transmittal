<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransmittalHeader extends Model
{
    protected $table = 'transmittal_header';

    protected $fillable = [
        'ref_no', 'transmittal_date', 'transmittal_type', 'destination', 'plate_no',
        'prepared_by', 'received_by', 'noted_by', 'status', 'attachment', 'remarks',
    ];

    protected function casts(): array
    {
        return ['transmittal_date' => 'date'];
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransmittalDetail::class, 'outgoing_transmittal_header_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'auditable_id')->where('auditable_type', self::class)->latest();
    }
}
