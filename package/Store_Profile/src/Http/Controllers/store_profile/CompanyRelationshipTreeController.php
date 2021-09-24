<?php
namespace Retailcore\Store_Profile\Http\Controllers\store_profile;
use App\home_navigations_data;
use App\Http\Controllers\Controller;
use Retailcore\Products\Models\product\price_master;
use Retailcore\Store_Profile\Models\store_profile\company_relationship_tree;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Illuminate\Http\Request;
use App\state;
use App\country;
use Illuminate\Validation\Rule;
use Auth;
use DB;
use App\company;
use Log;
class CompanyRelationshipTreeController extends Controller
{
    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $is_store = 1;
        $state = state::all();
        $country = country::all();
        return view('store_profile::store_profile/store_profile', compact('state', 'country', 'is_store'));
    }

    public function view_store_data()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $company_id = Auth::User()->company_id;
        $view_store = company_relationship_tree::where('warehouse_id', '=', $company_id)->orderBy('company_relationship_trees_id','DESC')->paginate(10);
        return view('store_profile::store_profile/view_store',compact('view_store'));
    }

    public function view_store_fetch_data(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->ajax())
        {
            $data = $request->all();
            $sort_by = $data['sortby'];
            $sort_type = $data['sorttype'];
            $query = isset($data['query']) ? $data['query'] : '';

            $query = str_replace(" ", "%", $query);

            $company_id = Auth::User()->company_id;

            $view_store = company_relationship_tree::where('warehouse_id', '=', $company_id);

            $view_store = $view_store->orderBy($sort_by,$sort_type)->paginate(10);

            return view('store_profile::store_profile/view_store_data',compact('view_store'))->render();
        }
    }

    public function edit_store(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $store_id = decrypt($request->company_relationship_tree_id);
        $warehouse_id = decrypt($request->company_id);
        $company_profile_id = decrypt($request->company_profile_id);
        $storedata = company_profile::where('company_profile_id',$company_profile_id)->get();
        $storedata[0]['store_id'] = $store_id;
        $url = 'store_profile';
        return json_encode(array("Success"=>"True","Data"=>$storedata,'url'=>$url));
    }

    public function view_store_popup(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $company_relationship_trees_id = $request->company_relationship_trees_id;
        $view_store = company_relationship_tree::where('company_relationship_trees_id','=',$company_relationship_trees_id)->get();

        return view('store_profile::store_profile/view_popup',compact('view_store'));
    }

    public function get_store_list(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $company_id = Auth::User()->company_id;

        $product_id = decrypt($request->product_id);

        $price_master_value = price_master::select('offer_price','product_mrp','product_qty','price_master_id','batch_no')
            ->whereNull('deleted_at')
            ->where('product_id',$product_id)
            ->where('product_qty','>',0)->get();

        $store_list = company_relationship_tree::where('warehouse_id', '=', $company_id)->with('company_profile')->orderBy('company_relationship_trees_id','DESC')->get();

        $warehouse = company_profile::where('company_id',$company_id)->first();

        return json_encode(array("Success"=>"True","Data"=>$store_list,"warehouse"=>$warehouse,'price_master_value'=>$price_master_value));
    }

}
