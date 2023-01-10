<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TruckStockLog extends Model
{
    use HasFactory;
    protected $appends = [
        'converted_created_at',
        ];
       
        public function getConvertedCreatedAtAttribute()
        {
            return date('d M Y, h:i a', strtotime($this->created_at));
       
        }
}
