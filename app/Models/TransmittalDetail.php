<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransmittalDetail extends Model
{
    protected $table = 'transmittal_details';

    protected $fillable = ['outgoing_transmittal_header_id', 'item_id', 'item_qty', 'item_desc', 'item_unit', 'item_serial_no', 'par_no', 'attachment', 'remarks'];

    public function header(): BelongsTo
    {
        return $this->belongsTo(TransmittalHeader::class, 'outgoing_transmittal_header_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
