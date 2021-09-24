<?php

namespace Retailcore\Products\Http\Controllers\product;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Retailcore\Products\Models\product\product;
use Retailcore\Products\Models\product\price_master;
use Retailcore\PO\Models\purchase_order\purchase_order_detail;
use Retailcore\Products\Models\product\category;
use Retailcore\Products\Models\product\brand;
use Retailcore\Products\Models\product\ProductFeatures;
use Retailcore\Products\Models\product\stockreport_export;
use Retailcore\Products\Models\product\lowStock_export;
use Illuminate\Support\Facades\DB;

use Retailcore\Products\Models\product\colour;
use Retailcore\Products\Models\product\product_image;
use Retailcore\Products\Models\product\product_export;
use Retailcore\Products\Models\product\size;
use Retailcore\Products\Models\product\subcategory;
use Retailcore\Products\Models\product\uqc;

use Auth;
use Maatwebsite\Excel\Facades\Excel;
use Log;
class StockreportController extends Controller
{
    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

    	/*$product = product::where('products.company_id',Auth::user()->company_id)
            ->select('products.*','products.product_id')
            ->where('products.deleted_at','=',NULL)
            ->orderBy('products.product_id', 'DESC')
            ->where('products.item_type','=','1')
            ->with(array('inward_product_detail'=>function($query){
		        $query->select(DB::raw("SUM(inward_product_details.product_qty) as totalQty"),'inward_product_details.product_id')
                ->orWhere('inward_product_details.product_id', '=', 'products.product_id')
                ->groupBy('inward_product_details.product_id');
		    }))->paginate(10);*/

            //Join query but gives incorrect result while sum different columns especially when multijoin

             /*$product =   product::select('products.*',DB::raw("SUM(inward_product_details.product_qty) as totalinwardqty"),DB::raw("SUM(sales_product_details.qty) as totalsoldqty"))->leftJoin('inward_product_details', function ($injoin) {
                             $injoin->on('inward_product_details.product_id', '=', 'products.product_id')
                            ->where(DB::raw("DATE(inward_product_details.created_at)"),'<',date("Y-m-d"))
                            ->groupBy('inward_product_details.product_id');
                            })
                            ->leftJoin('sales_product_details', function ($sajoin) {
                                 $sajoin->on('sales_product_details.product_id', '=', 'products.product_id')
                                 ->where(DB::raw("sales_product_details.bill_date"),'<',date("d-m-Y"));
                            })

                    ->groupBy('products.product_id')
                    ->paginate(10);*/



             $inwarddate = date("Y-m-d");
             $billdate   = date("d-m-Y");


                 $product = product::select("products.*",DB::raw("(SELECT SUM(inward_product_details.product_qty + inward_product_details.free_qty) FROM inward_product_details WHERE inward_product_details.product_id = products.product_id and DATE(inward_product_details.created_at) < '$inwarddate' GROUP BY inward_product_details.product_id) as totalinwardqty"),DB::raw("(SELECT SUM(sales_product_details.qty) FROM sales_product_details WHERE sales_product_details.product_id = products.product_id and DATE(sales_product_details.created_at) < '$inwarddate' and sales_product_details.deleted_at IS NULL GROUP BY sales_product_details.product_id) as totalsoldqty"),DB::raw("(SELECT SUM(inward_product_details.product_qty + inward_product_details.free_qty) FROM inward_product_details WHERE inward_product_details.product_id = products.product_id and DATE(inward_product_details.created_at) = '$inwarddate' GROUP BY inward_product_details.product_id) as currentinward"),DB::raw("(SELECT SUM(sales_product_details.qty) FROM sales_product_details WHERE sales_product_details.product_id = products.product_id and DATE(sales_product_details.created_at) = '$inwarddate' and sales_product_details.deleted_at IS NULL GROUP BY sales_product_details.product_id) as currentsold"),DB::raw("(SELECT SUM(price_masters.product_qty *price_masters.offer_price)/SUM(price_masters.product_qty) FROM price_masters WHERE price_masters.product_id = products.product_id GROUP BY price_masters.product_id) as averagemrp"))->orderBy('product_id','DESC')->paginate(10);



                 $totproduct = product::select(DB::raw("(SELECT SUM(inward_product_details.product_qty + inward_product_details.free_qty) FROM inward_product_details WHERE inward_product_details.product_id = products.product_id and DATE(inward_product_details.created_at) < '$inwarddate' GROUP BY inward_product_details.product_id) as totalinwardqty"),DB::raw("(SELECT SUM(sales_product_details.qty) FROM sales_product_details WHERE sales_product_details.product_id = products.product_id and DATE(sales_product_details.created_at) < '$inwarddate' and sales_product_details.deleted_at IS NULL GROUP BY sales_product_details.product_id) as totalsoldqty"),DB::raw("(SELECT SUM(inward_product_details.product_qty + inward_product_details.free_qty) FROM inward_product_details WHERE inward_product_details.product_id = products.product_id and DATE(inward_product_details.created_at) = '$inwarddate' GROUP BY inward_product_details.product_id) as currentinward"),DB::raw("(SELECT SUM(sales_product_details.qty) FROM sales_product_details WHERE sales_product_details.product_id = products.product_id and DATE(sales_product_details.created_at) = '$inwarddate' and sales_product_details.deleted_at IS NULL GROUP BY sales_product_details.product_id) as currentsold"))->orderBy('product_id','DESC')->get();



                $totinwardqty = 0;
                $totsoldqty = 0;
                $currinward = 0;
                $currsold = 0;
                $count=0;

            foreach ($totproduct as $ttotproduct)
            {
                $count++;
                $totinwardqty          +=   $ttotproduct->totalinwardqty;
                $totsoldqty            +=   $ttotproduct->totalsoldqty;
                $currinward            +=   $ttotproduct->currentinward;
                $currsold              +=   $ttotproduct->currentsold;
            }

               $totopening     =   $totinwardqty - $totsoldqty;
                $totstock     =   $totopening +$currinward -$currsold;
    		 return view('product.stock_report',compact('product','totopening','totstock','currinward','currsold','count'));
    }

    function datewise_stock_detail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->ajax())
        {


            $data            =      $request->all();
            $sort_by         =      $data['sortby'];
            $sort_type       =      $data['sorttype'];
            $from_date       =      $data['fromdate'];
            $to_date         =      $data['todate'];
            $productsearch   =      $data['productsearch'];
            $categoryname    =      $data['categoryname'];
            $brandname       =      $data['brandname'];

            if(strpos($productsearch, '_') !== false)
            {
                $prodname    =   explode('_',$productsearch);
                $prod_barcode    =  $prodname[0];
                $prod_name     =  $prodname[1];
            }
            else
            {
                $prod_barcode   =   $productsearch;
                $prod_name =   $productsearch;
            }



            $prodresult = product::select('product_id')
             ->where('company_id',Auth::user()->company_id)
             ->where('deleted_at','=',NULL)
             ->where('product_name', 'LIKE', "%$prod_name%")
             ->orwhere('product_system_barcode', 'LIKE', "%$prod_barcode%")
             ->get();


             $catresult = category::select('category_id')
             ->where('company_id',Auth::user()->company_id)
             ->where('deleted_at','=',NULL)
             ->where('category_name', 'LIKE', "%$categoryname%")
             ->get();



             $brandresult = brand::select('brand_id')
             ->where('company_id',Auth::user()->company_id)
             ->where('deleted_at','=',NULL)
             ->where('brand_type', 'LIKE', "%$brandname%")
             ->get();


            $inwardstartdate            =      date("Y-m-d",strtotime($from_date));
            $inwardenddate              =      date("Y-m-d",strtotime($to_date));

            $salesstartdate             =      $from_date;
            $salesenddate               =      $to_date;

            $query           =      product::select("products.*",DB::raw("(SELECT SUM(inward_product_details.product_qty + inward_product_details.free_qty) FROM inward_product_details WHERE inward_product_details.product_id = products.product_id and DATE(inward_product_details.created_at) < '$inwardstartdate' GROUP BY inward_product_details.product_id) as totalinwardqty"),DB::raw("(SELECT SUM(sales_product_details.qty) FROM sales_product_details WHERE sales_product_details.product_id = products.product_id and DATE(sales_product_details.created_at) < '$inwardstartdate' and sales_product_details.deleted_at IS NULL GROUP BY sales_product_details.product_id) as totalsoldqty"),DB::raw("(SELECT SUM(inward_product_details.product_qty + inward_product_details.free_qty) FROM inward_product_details WHERE inward_product_details.product_id = products.product_id and DATE(inward_product_details.created_at) between '$inwardstartdate' and '$inwardenddate' GROUP BY inward_product_details.product_id) as currentinward"),DB::raw("(SELECT SUM(sales_product_details.qty) FROM sales_product_details WHERE sales_product_details.product_id = products.product_id and DATE(sales_product_details.created_at) between '$inwardstartdate' and '$inwardenddate' and sales_product_details.deleted_at IS NULL GROUP BY sales_product_details.product_id) as currentsold"),DB::raw("(SELECT SUM(price_masters.product_qty *price_masters.offer_price)/SUM(price_masters.product_qty) FROM price_masters WHERE price_masters.product_id = products.product_id GROUP BY price_masters.product_id) as averagemrp"));


            if($productsearch!='')
            {
                $query->whereIn('product_id',$prodresult);
            }
            if($categoryname!='')
            {
                $query->whereIn('category_id',$catresult);
            }
            if($brandname!='')
            {
                $query->whereIn('brand_id',$brandresult);
            }

            $product = $query->orderBy($sort_by, $sort_type)
                          ->paginate(10);

            $tquery           =      product::select(DB::raw("(SELECT SUM(inward_product_details.product_qty + inward_product_details.free_qty) FROM inward_product_details WHERE inward_product_details.product_id = products.product_id and DATE(inward_product_details.created_at) < '$inwardstartdate' GROUP BY inward_product_details.product_id) as totalinwardqty"),DB::raw("(SELECT SUM(sales_product_details.qty) FROM sales_product_details WHERE sales_product_details.product_id = products.product_id and DATE(sales_product_details.created_at) < '$inwardstartdate' and sales_product_details.deleted_at IS NULL GROUP BY sales_product_details.product_id) as totalsoldqty"),DB::raw("(SELECT SUM(inward_product_details.product_qty + inward_product_details.free_qty) FROM inward_product_details WHERE inward_product_details.product_id = products.product_id and DATE(inward_product_details.created_at) between '$inwardstartdate' and '$inwardenddate' GROUP BY inward_product_details.product_id) as currentinward"),DB::raw("(SELECT SUM(sales_product_details.qty) FROM sales_product_details WHERE sales_product_details.product_id = products.product_id and DATE(sales_product_details.created_at) between '$inwardstartdate' and '$inwardenddate' and sales_product_details.deleted_at IS NULL GROUP BY sales_product_details.product_id) as currentsold"));


            if($productsearch!='')
            {
                $tquery->whereIn('product_id',$prodresult);
            }
            if($categoryname!='')
            {
                $tquery->whereIn('category_id',$catresult);
            }
            if($brandname!='')
            {
                $tquery->whereIn('brand_id',$brandresult);
            }


            $totproduct = $tquery->get();

                $totinwardqty = 0;
                $totsoldqty = 0;
                $currinward = 0;
                $currsold = 0;
                $count=0;

            foreach ($totproduct as $ttotproduct)
            {
                $count++;
                $totinwardqty          +=   $ttotproduct->totalinwardqty;
                $totsoldqty            +=   $ttotproduct->totalsoldqty;
                $currinward            +=   $ttotproduct->currentinward;
                $currsold              +=   $ttotproduct->currentsold;

            }

               $totopening     =   $totinwardqty - $totsoldqty;
                $totstock      =   $totopening +$currinward -$currsold;


            return view('product.view_stockreport_data',compact('product','totopening','totstock','currinward','currsold','count'));
        }


    }

    public function export_stockreport_details(Request $request)
    {

        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
          return Excel::download(new stockreport_export($request->from_date,$request->to_date,$request->productsearch,$request->categoryname,$request->brandname), 'StockReport-Export.xlsx');


    }


    public function category_search(Request $request)
    {

        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $result = category::select('category_name')
             ->where('company_id',Auth::user()->company_id)
             ->where('deleted_at','=',NULL)
             ->where('category_name', 'LIKE', "%$request->search_val%")
             ->get();



        return json_encode(array("Success"=>"True","Data"=>$result) );
    }
    public function brand_search(Request $request)
    {

        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $result = brand::select('brand_type')
             ->where('company_id',Auth::user()->company_id)
             ->where('deleted_at','=',NULL)
             ->where('brand_type', 'LIKE', "%$request->search_val%")
             ->get();



        return json_encode(array("Success"=>"True","Data"=>$result) );
    }

    function lowstock_report(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $lowStock = product::select('*')->where('company_id', Auth::User()->company_id)
        ->where('products.alert_product_qty', '>' ,DB::raw("(SELECT SUM(price_masters.product_qty) FROM price_masters WHERE price_masters.product_id = products.product_id)"))
        ->withCount([
            'price_master as totalstock' => function($fquery)  {
                $fquery->select(DB::raw('SUM(product_qty)'));
            }
        ])->with('uqc','product_features_relationship')->orderBy('product_id','DESC')->paginate(10);

        $product_features =  ProductFeatures::getproduct_feature('');


        foreach ($lowStock AS $key=>$v) {
            if (isset($v['product_features_relationship']) && $v['product_features_relationship'] != '')
            {
                foreach($product_features AS $kk => $vv)
                {
                    $html_id = $vv['html_id'];

                    if ($v['product_features_relationship'][$html_id] != '' && $v['product_features_relationship'][$html_id] != NULL)
                    {

                        $nm = product::feature_value($vv['product_features_id'], $v['product_features_relationship'][$html_id]);

                        $lowStock[$key][$html_id] = $nm;
                    }
                }
            }
        }

         // echo '<pre>'; print_r($lowStock); exit;
        return view('products::product/lowstock-report',compact('lowStock'));
    }

    public function lowstock_data(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $lowStock = product::select('*')->where('company_id', Auth::User()->company_id)
            ->where('products.alert_product_qty', '>' ,DB::raw("(SELECT SUM(price_masters.product_qty) FROM price_masters WHERE price_masters.product_id = products.product_id)"))
            ->withCount([
                'price_master as totalstock' => function($fquery)  {
                    $fquery->select(DB::raw('SUM(product_qty)'));
                }
            ])->with('uqc','product_features_relationship')->orderBy('product_id','DESC')->paginate(10);

        $product_features =  ProductFeatures::getproduct_feature('');

        foreach ($lowStock AS $key=>$v) {
            if (isset($v['product_features_relationship']) && $v['product_features_relationship'] != '')
            {
                foreach($product_features AS $kk => $vv)
                {
                    $html_id = $vv['html_id'];

                    if ($v['product_features_relationship'][$html_id] != '' && $v['product_features_relationship'][$html_id] != NULL)
                    {

                        $nm = product::feature_value($vv['product_features_id'], $v['product_features_relationship'][$html_id]);

                        $lowStock[$key][$html_id] = $nm;
                    }
                }
            }
        }
        return view('products::product/view_lowstock_data',compact('lowStock'));

    }

    function search_lowstock(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data   =   $request->all();

        $sort_by        =   isset($data['sortby']) ? $data['sortby'] : 'product_id';
        $sort_type      =   isset($data['sorttype']) ? $data['sorttype'] : 'DESC';
        $query          =   isset($data['query']) ? $data['query']  : '';
        $product_name   =   isset($query['product_name']) ? $query['product_name'] : '';
        $barcode        =   isset($query['barcode']) ? $query['barcode'] : '' ;
        /*$brand_id       =   $query['brand_id'];
        $category_id    =   $query['category_id'];
        $subcategory_id =   $query['subcategory_id'];
        $colour_id      =   $query['colour_id'];
        $size_id        =   $query['size_id'];*/
        $uqc_id         =   isset($query['uqc_id']) ? $query['uqc_id'] : 0;

        $dynamic_search = array();
        if($query != '')
        {
            foreach ($query as $key => $value)
            {
                if (strpos($key,'dynamic_') === 0)
                {
                    if($value != '')
                    {
                        $dynamic_search[$key] = $value;
                        unset($query[$key]);
                    }
                    else
                    {
                        unset($query[$key]);
                    }
                }
            }
        }

        $lquery = product::select('*')->where('company_id', Auth::User()->company_id);

        if($product_name!='')
        {
            $lquery->whereRaw("product_name LIKE '%$product_name%'");
        }

        if($barcode!='')
        {
            $lquery->whereRaw("product_system_barcode='$barcode' or supplier_barcode='$barcode'");
        }

//        if($brand_id!=0)
//        {
//            $lquery->whereRaw("brand_id='$brand_id'");
//        }
//
//        if($category_id!=0)
//        {
//            $lquery->whereRaw("category_id='$category_id'");
//        }
//
//        if($subcategory_id!=0)
//        {
//            $lquery->whereRaw("subcategory_id='$subcategory_id'");
//        }
//
//        if($colour_id!=0)
//        {
//            $lquery->whereRaw("colour_id='$colour_id'");
//        }
//
//        if($size_id!=0)
//        {
//            $lquery->whereRaw("size_id='$size_id'");
//        }
        if(isset($dynamic_search) && $dynamic_search !='' &&  !empty($dynamic_search))
        {

            $lquery->with('product_features_relationship')
                ->whereHas('product_features_relationship',function ($q) use($dynamic_search)
                {
                    foreach($dynamic_search AS $k=>$v)
                    {
                        $q->where(DB::raw($k),$v);
                    }
                });
        }

        if($uqc_id!=0)
        {
            $lquery->whereRaw("uqc_id='$uqc_id'");
        }


        $lowStock     =   $lquery->where('products.alert_product_qty', '>' ,DB::raw("(SELECT SUM(price_masters.product_qty) FROM price_masters WHERE price_masters.product_id = products.product_id)"))
        ->withCount([
            'price_master as totalstock' => function($fquery)  {
                $fquery->select(DB::raw('SUM(product_qty)'));
            }
        ])->with('uqc')->orderBy($sort_by,$sort_type)->paginate(10);

        $product_features =  ProductFeatures::getproduct_feature('');
        foreach ($lowStock AS $key=>$v) {
            if (isset($v['product_features_relationship']) && $v['product_features_relationship'] != '')
            {
                foreach($product_features AS $kk => $vv)
                {
                    $html_id = $vv['html_id'];

                    if ($v['product_features_relationship'][$html_id] != '' && $v['product_features_relationship'][$html_id] != NULL)
                    {

                        $nm = product::feature_value($vv['product_features_id'], $v['product_features_relationship'][$html_id]);

                        $lowStock[$key][$html_id] = $nm;
                    }
                }
            }
        }



        return view('products::product/view_lowstock_data',compact('lowStock'))->render();
    }

    public function exportlowstock_details(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        return Excel::download(new lowStock_export($request->product_name,$request->barcode,$request->uqc_id), 'LowStock-Export.xlsx');
    }
}
