<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverPayments extends Model
{
    use HasFactory;
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id', 'user_id')
                                    ->where('users.role_id','4')
                                    ->where('users.reg_status','1');
    }
}
