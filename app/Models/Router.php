<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Router extends Model
{
    use HasFactory;

    // Table associated with the model
    protected $table = 'm_router';

    // Define which attributes can be mass-assigned
    protected $fillable = [
        'name',     // Router name
        'ip',       // IP address of the router
        'username', // Username for authentication
        'password', // Password for authentication
        'port'      // API port (default: 8728)
    ];

    // If your table uses timestamps (created_at, updated_at), Laravel handles this by default.
    // If not, set this to false:
    public $timestamps = true;

    // You can define relationships here if needed (e.g., hasMany, belongsTo)
}
