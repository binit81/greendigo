<?php
namespace Retailcore\Store_Profile\Models\store_profile;

use Illuminate\Database\Eloquent\Model;

class company_relationship_tree extends Model
{
    protected $primaryKey = 'company_relationship_trees_id'; //Default: id
    protected $guarded=['company_relationship_trees_id'];

    public function state_name()
    {
        return $this->hasOne('App\state','state_id','state_id');
    }

    public function company_profile()
    {
    	return $this->hasOne('Retailcore\Company_Profile\Models\company_profile\company_profile','company_profile_id','store_id');
    }

    public function storeUsers()
    {
        return $this->hasMany('App\User','store_id','store_id')->whereNull('deleted_at');
    }

}
