<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PublicAccount extends Model
{
    // Define timestamps in table
    public $timestamps = true;

    // Define table name
    protected $table = "public_accounts";
}
