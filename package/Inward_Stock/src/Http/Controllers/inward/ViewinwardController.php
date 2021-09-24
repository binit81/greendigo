<?php

namespace Retailcore\Inward_Stock\Http\Controllers\inward;

use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Retailcore\Inward_Stock\Models\inward\View_inward;
use Retailcore\Inward_Stock\Models\inward\inward_stock;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Retailcore\Products\Models\product\product;
use Retailcore\Products\Models\product\ProductFeatures;
use Retailcore\Supplier\Models\supplier\supplier_payment_detail;
use Retailcore\Sales\Models\payment_method;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Log;
class ViewinwardController extends Controller
{

    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $payment_methods = payment_method::where('is_active','=','1')->where('payment_method_id','!=',9)->get();

        $todate       =    date('Y-m-d');

        $inward_stock = inward_stock::where('company_id',Auth::user()->company_id)
            ->where('deleted_at','=',NULL)
            ->orderBy('inward_stock_id', 'DESC')
            ->with('supplier_gstdetail.supplier_company_info')
            ->with('inward_supplier_payment')
            ->whereRaw("STR_TO_DATE(inward_date,'%d-%m-%Y') = '$todate'")
            ->select('*',
                DB::raw("(SELECT SUM(inward_product_details.pending_return_qty) FROM inward_product_details WHERE inward_product_details.inward_stock_id = inward_stocks.inward_stock_id and inward_product_details.deleted_at IS NULL)  as totalpendingqty"))
            ->paginate(10);

       /* $inward_in_ids = array();
       if(isset($inward_stock) && isset($inward_stock[0]) && isset($inward_stock[0]['inward_stock_ids']))
       {
            $inward_in_ids = explode(',',$inward_stock[0]['inward_stock_ids']);
       }*/




        $inward_ids = $inward_stock->pluck('inward_stock_id')->toArray();


        $supplier_payment = supplier_payment_detail::where('company_id',Auth::user()->company_id)
                                                    ->select('*',DB::raw("sum(amount) as total_amt"))
                                                    ->whereNull('deleted_at')
                                                    ->whereIn('inward_stock_id',$inward_ids)
                                                    ->groupBy('payment_method_id')
                                                    ->get();
        $payment_value = array();

        foreach($supplier_payment AS $key=>$value)
        {
            $payment_value[$value['payment_method_id']] =  $value['total_amt'];
        }

        $max_date  =  $inward_stock->max('inward_date');
        $min_date  =  $inward_stock->min('inward_date');

        return view('inward_stock::inward/view_inward_stock', compact('inward_stock','payment_methods','payment_value','max_date','min_date'))->render();
    }

    function inward_fetch_data(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->ajax())
        {
            $data = $request->all();
            $sort_by = isset($data['sortby'])?$data['sortby'] : 'inward_stock_id';
            $sort_type = isset($data['sorttype'])?$data['sorttype']:'desc';
            $query = isset($data['query']) ? $data['query'] : '';

            //$query = str_replace(" ", "", $query);

            $inward_stock = inward_stock::where('company_id', Auth::user()->company_id)
                ->where('deleted_at', '=', NULL)
                ->select('*',DB::raw("(SELECT SUM(inward_product_details.pending_return_qty) FROM inward_product_details WHERE inward_product_details.inward_stock_id = inward_stocks.inward_stock_id and inward_product_details.deleted_at IS NULL)  as totalpendingqty"));

            if(isset($query) && $query != '')
            {
                if ($query['from_date'] != '' || $query['to_date'] != '')
                {
                    $from_date   =   date("Y-m-d",strtotime($query['from_date']));
                    $to_date   =  date("Y-m-d",strtotime($query['to_date']));

                    $inward_stock->whereRaw("STR_TO_DATE(inward_date,'%d-%m-%Y') between '$from_date' and '$to_date'");
                }

                if ($query['invoice_no'] != '')
                {
                    $inward_stock->where('invoice_no', '=', $query['invoice_no'])->where('deleted_at', '=', NULL);
                }
                if ($query['supplier_name'] != '')
                {
                    $inward_stock->where('supplier_gst_id', '=', $query['supplier_name'])->where('deleted_at', '=', NULL);
                }
            }
            else{
                $today_date   =  date('Y-m-d');
                $inward_stock = inward_stock::whereRaw("STR_TO_DATE(inward_date,'%d-%m-%Y') between '$today_date' and '$today_date'")->where('deleted_at', '=', NULL);
            }
            $inward_stock = $inward_stock->orderBy($sort_by, $sort_type)->paginate(10);
            $payment_methods = payment_method::where('is_active','=','1')->where('payment_method_id','!=',9)->get();
            $inward_ids = $inward_stock->pluck('inward_stock_id')->toArray();
            $supplier_payment = supplier_payment_detail::where('company_id',Auth::user()->company_id)
                ->select('*',DB::raw("sum(amount) as total_amt"))
                ->whereNull('deleted_at')
                ->whereIn('inward_stock_id',$inward_ids)
                ->groupBy('payment_method_id')
                ->get();

            $payment_value = array();

            foreach($supplier_payment AS $key=>$value)
            {
                $payment_value[$value['payment_method_id']] =  $value['total_amt'];
            }


            if(isset($query['from_date']) && $query['from_date'] !='')
            {
                $max_date  =  $query['from_date'];
                $min_date  =  $query['to_date'];
            }
            else
            {
                $max_date  =  $inward_stock->max('inward_date');
                $min_date  =  $inward_stock->min('inward_date');
            }

            return view('inward_stock::inward/inward_stock_data', compact('inward_stock','payment_methods','payment_value','max_date','min_date'))->render();
        }
    }

    public function view_inward_detail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $inward_stock_id = decrypt($request->inward_stock_id);

        $inward_stock = inward_stock::where([
            ['inward_stock_id','=',$inward_stock_id],
            ['company_id',Auth::user()->company_id]])
            ->with('inward_product_detail.product_detail.uqc')
            ->with('inward_product_detail.product_detail.product_features_relationship')
            ->with('supplier_payment_details.payment_method')
            ->with('supplier_gstdetail.supplier_company_info')
            ->with('warehouse')
            ->select('*')
            ->where('deleted_at','=',NULL)
            ->first();

        $product_features =  ProductFeatures::getproduct_feature('');


        foreach ($inward_stock['inward_product_detail'] AS $key=>$v) {

            if (isset($v['product_detail']['product_features_relationship']) && $v['product_detail']['product_features_relationship'] != '')
            {
                foreach($product_features AS $kk => $vv)
                {
                    $html_id = $vv['html_id'];

                    if ($v['product_detail']['product_features_relationship'][$html_id] != '' && $v['product_detail']['product_features_relationship'][$html_id] != NULL)
                    {

                        $nm = product::feature_value($vv['product_features_id'], $v['product_detail']['product_features_relationship'][$html_id]);

                        $inward_stock['inward_product_detail'][$key]['product_detail'][$html_id] = $nm;
                    }
                }
            }
        }



        $data = json_encode($inward_stock);

         //TO GET NEXT ID OF SELECTED ID IN POPUP
         $next_id = inward_stock::where('inward_stock_id', '>', $inward_stock_id)->where('company_id',Auth::user()->company_id)->min('inward_stock_id');


 $next = '';
 if(isset($next_id) && $next_id != '')
{
    $next = encrypt($next_id);
}

           //TO GET PREVIOUS ID OF SELECTED ID IN POPUP
         $previous = inward_stock::where('inward_stock_id', '<', $inward_stock_id)->where('company_id',Auth::user()->company_id)->max('inward_stock_id');

         $prev = '';
         if(isset($previous) && $previous != '')
            {
                 $prev = encrypt($previous);
            }



 return json_encode(array("Success"=>"True","Data"=>$data,"next"=>$next,"previous"=>$prev,));

}


    public function invoice_number_search(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $result = inward_stock::select('invoice_no')
            ->where('company_id',Auth::user()->company_id)
            ->where('invoice_no','LIKE',"%$request->search_val%")
            ->whereNull('deleted_at')
            ->get();
        return json_encode(array("Success"=>"True","Data"=>$result));
    }
}
