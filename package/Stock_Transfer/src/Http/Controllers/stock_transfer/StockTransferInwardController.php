<?php
namespace Retailcore\Stock_Transfer\Http\Controllers\stock_transfer;
use Retailcore\Stock_Transfer\Models\stock_transfer\stock_transfer_detail;
use Retailcore\Stock_Transfer\Models\stock_transfer\Stock_Transfer_Inward;
use Retailcore\Stock_Transfer\Models\stock_transfer\stock_transfer;
use Retailcore\Company_Profile\Models\company_profile\company_profile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use DB;
use Log;
class StockTransferInwardController extends Controller
{

    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $company_id = Auth::User()->company_id;

        $store_id = company_profile::where('company_id',$company_id)->select('company_profile_id')->first();

        if(isset($store_id) && $store_id != '' && isset($store_id['company_profile_id']) )
        {
            $stock_transfer_inward_data = stock_transfer::where('store_id', '=', $store_id['company_profile_id'])
                ->whereNull('deleted_at')
                ->orderBy('stock_transfer_id','desc')
                ->paginate(10);
        }

        if(isset($stock_transfer_inward_data[0]) && $stock_transfer_inward_data[0] != '')
        {
            foreach ($stock_transfer_inward_data AS $key=>$value)
            {
                $qty = stock_transfer_detail::
                    selectRaw('SUM(pending_rcv_qty) AS pending_rcv_qty')
                    ->where('pending_rcv_qty', '!=', NULL)
                    ->whereNull('deleted_at')
                    ->where('stock_transfer_id', '=', $value['stock_transfer_id'])
                    ->groupBy('stock_transfer_id')
                    ->first();

                $stock_transfer_inward_data[$key]['pending_qty'] = $qty['pending_rcv_qty'];

            }
        }

        return view('stock_transfer::stock_transfer/stock_transfer_inward',compact('stock_transfer_inward_data'))->render();
    }

    public function stock_transfer_inward_fetch_data(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->ajax())
        {
            $data = $request->all();
            $sort_by = isset($data['sortby'])?$data['sortby']:'stock_transfer_id';
            $sort_type = isset($data['sorttype'])?$data['sorttype']:'desc';
            $query = isset($data['query']) ? $data['query'] : '';
            $query = str_replace(" ", "", $query);



            $store_id = company_profile::where('company_id',Auth::user()->company_id)->select('company_profile_id')->first();

            if(isset($store_id) && $store_id != '' && isset($store_id['company_profile_id']) )
            {
                $stock_transfer_inward_data = stock_transfer::where('store_id', '=', $store_id['company_profile_id'])
                    ->whereNull('deleted_at')
                    ;

                if($query != '')
                {
                    if ($query['from_date'] != '' || $query['to_date'] != '') {
                       // $stock_transfer_inward_data = $stock_transfer_inward_data->WhereBetween('stock_transfer_date', [$query['from_date'], $query['to_date']])->where('deleted_at', '=', NULL);

                        $from_date = date("Y-m-d", strtotime($query['from_date']));
                        $to_date = date("Y-m-d", strtotime($query['to_date']));

                        $stock_transfer_inward_data = $stock_transfer_inward_data->whereRaw("STR_TO_DATE(stock_transfer_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
                    }

                    if ($query['stock_transfer_no'] != '')
                    {
                        $stock_transfer_inward_data = $stock_transfer_inward_data->where('stock_transfer_no', '=', $query['stock_transfer_no'])->where('deleted_at', '=', NULL);
                    }
                }

                $stock_transfer_inward_data = $stock_transfer_inward_data->orderBy($sort_by, $sort_type)->paginate(10);
            }


            if(isset($stock_transfer_inward_data[0]) && $stock_transfer_inward_data[0] != '')
            {
                foreach ($stock_transfer_inward_data AS $key=>$value)
                {
                    $qty = stock_transfer_detail::
                    selectRaw('SUM(pending_rcv_qty) AS pending_rcv_qty')
                        ->where('pending_rcv_qty', '!=', NULL)
                        ->whereNull('deleted_at')
                        ->where('stock_transfer_id', '=', $value['stock_transfer_id'])
                        ->groupBy('stock_transfer_id')
                        ->first();

                    $stock_transfer_inward_data[$key]['pending_qty'] = $qty['pending_rcv_qty'];

                }
            }

            return view('stock_transfer::stock_transfer/stock_transfer_inward_data', compact('stock_transfer_inward_data'))->render();
        }
    }


    public function view_stock_inward_detail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $stock_transfer_id = decrypt($request->stock_transfer_id);

        $store_id = company_profile::where('company_id',Auth::user()->company_id)->select('company_profile_id')->first();

        $stock_transfer = stock_transfer::where([
            ['stock_transfer_id','=',$stock_transfer_id],
            ['store_id',$store_id['company_profile_id']]])
            ->with('stock_transfer_detail.product_data')
            ->select('*')
            ->where('deleted_at','=',NULL)
            ->first();



        $data = json_encode($stock_transfer);
        $next_id = stock_transfer::where('stock_transfer_id', '>', $stock_transfer_id)->min('stock_transfer_id');

        $next = '';
        if(isset($next_id) && $next_id != '')
        {
            $next = encrypt($next_id);
        }
        //TO GET PREVIOUS ID OF SELECTED ID IN POPUP
        $previous = stock_transfer::where('stock_transfer_id', '<', $stock_transfer_id)->max('stock_transfer_id');

        $prev = '';
        if(isset($previous) && $previous != '')
        {
            $prev = encrypt($previous);
        }

        return json_encode(array("Success"=>"True","Data"=>$data,"next"=>$next,"previous"=>$prev,));
    }


    public function take_stock_inward(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $stock_transfer_id = decrypt($request->stock_transfer_id);

        $store_id = company_profile::where('company_id',Auth::user()->company_id)->select('company_profile_id','inward_type')->first();

        $stock_transfer = stock_transfer::where([
            ['stock_transfer_id','=',$stock_transfer_id],
            ['store_id',$store_id['company_profile_id']]])
            ->with('stock_transfer_detail.product_data')
            ->with('warehouse')
            ->select('*')
            ->first();


        $data = json_encode($stock_transfer);

        //$inward_type = company_profile::select('inward_type')->where('company_id',Auth::user()->company_id)->first();

            if(!isset($store_id) && $store_id == '')
            {
                $url = 'inward_stock';
            }
            else if($store_id['inward_type'] == 1)
            {
                $url = 'inward_stock';
            }
            else
            {
                $url = 'inward_stock_show';
            }


        return json_encode(array("Success"=>"True","Data"=>$data,"url"=>$url));
    }

    public function stock_transfer_number_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $store_id = company_profile::where('company_id',Auth::user()->company_id)->select('company_profile_id','inward_type')->first();
        $result = stock_transfer::select('stock_transfer_no')
            ->where('store_id',$store_id['company_profile_id'])
            ->where('stock_transfer_no','LIKE',"%$request->search_val%")
            ->whereNull('deleted_at')
            ->get();

        return json_encode(array("Success"=>"True","Data"=>$result));
    }

    public function stock_transfer_no_warehouse_filter(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $result = stock_transfer::select('stock_transfer_no')
            ->where('company_id',Auth::user()->company_id)
            ->where('stock_transfer_no','LIKE',"%$request->search_val%")
            ->whereNull('deleted_at')
            ->get();

        return json_encode(array("Success"=>"True","Data"=>$result));
    }
}
