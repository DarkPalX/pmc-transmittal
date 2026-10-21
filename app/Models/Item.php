<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = ['item_code', 'description', 'unit', 'serial_no', 'is_active', 'transmittal_date', 'par_no', 'attachment'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'transmittal_date' => 'date'];
    }

    public function getRouteKey(): mixed
    {
        return $this->getAttribute('route_key') ?? parent::getRouteKey();
    }
}
