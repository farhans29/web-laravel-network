<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FirewallList extends Model
{
    use HasFactory;
    
    // Table associated with the model
    protected $table = 't_firewall_addresslist';

}
