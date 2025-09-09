<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogLoginUser extends Model
{
    use HasFactory;
    protected $fillable=['username', 'ip_address', 'user_agent', 'platform', 'sukses', 'created_at'];
}
