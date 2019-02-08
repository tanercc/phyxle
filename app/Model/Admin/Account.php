<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    // Define timestamps in table
    public $timestamps = true;

    // Define table name
    protected $table = "accounts";
}
