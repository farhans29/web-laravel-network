<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Firewall extends Model
{
    use HasFactory;
    
    // Table associated with the model
    protected $table = 'm_router_firewall';

    // Primary key
    protected $primaryKey = 'idrec';

    // Auto-incrementing primary key
    public $incrementing = true;

    // Timestamps (set to false if your table doesn't have created_at & updated_at)
    public $timestamps = false;

    // Mass assignable attributes
    protected $fillable = ['idrouter', 'firewall_name'];
}
