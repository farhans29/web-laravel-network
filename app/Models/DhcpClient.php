<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DhcpClient extends Model
{
    use HasFactory;
    
    // Table associated with the model
    protected $table = 't_dhcp_list';

}
