<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hmo extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'code', 'batch_type'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
