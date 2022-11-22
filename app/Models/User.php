<?php

namespace App\Models;

use App\Helpers\Formats;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    // Status constant
    const ACTIVE = 1;
    const BLOCKED = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'country_code',
        'mobile',
        'gender',
        'role_id',
        'dob',
        'place',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
       // 'converted_created_at',
        'converted_status',
      //  'converted_gender',
       // 'converted_dob',
        'converted_mobile',
    ];

    /**
     * * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('users.status', self::ACTIVE);
    }

    public function scopeBlocked($query)
    {
        return $query->where('users.status', self::BLOCKED);
    }

    public function scopeDescending($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeAlphebetical($query)
    {
        return $query->orderBy('name', 'asc');
    }


    /**
     * * Accessors
     */
    public function getConvertedStatusAttribute()
    {
        return $this->status == self::ACTIVE ? 'Active' : 'Blocked';
    }
    // public function getConvertedCreatedAtAttribute()
    // {
    //     return Formats::customDateTime($this->created_at);
    // }
   /*  public function getConvertedGenderAttribute()
    {
        if ($this->gender) {
            return $this->gender == 1 ? 'Male' : ($this->gender == 2 ? 'Female' : 'Other');
        }
        return null;
    }
    public function getConvertedDobAttribute()
    {
        if ($this->dob) {
            return Formats::customDate($this->dob);
        }
        return null;
    }
    */ public function getConvertedMobileAttribute()
    {
        return $this->code . ' ' . $this->mobile;
    }
}
