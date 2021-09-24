<?php

namespace Retailcore\ProductAge_Range\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class productage_range extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'productage_range_id'; //Default: id
    protected $guarded = ['productage_range_id'];
}
