<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = ['item_code', 'description', 'unit', 'serial_no', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
