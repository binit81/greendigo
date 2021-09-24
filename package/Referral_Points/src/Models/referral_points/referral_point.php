<?php

namespace Retailcore\Referral_Points\Models\referral_points;

use Illuminate\Database\Eloquent\Model;

class referral_point extends Model
{
    protected  $primaryKey = 'referral_point_id';
    protected $guarded = ['referral_point_id'];
}
