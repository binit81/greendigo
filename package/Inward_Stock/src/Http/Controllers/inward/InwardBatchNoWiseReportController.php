<?php

namespace Retailcore\Inward_Stock\Http\Controllers\inward;

use App\company;
use function foo\func;
use function GuzzleHttp\Promise\all;
use Retailcore\Inward_Stock\Models\inward\inward_batch_no_wise_report;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Retailcore\Inward_Stock\Models\inward\inward_stock;
use Retailcore\Products\Models\product\product;
use Retailcore\Products\Models\product\price_master;
use Retailcore\Inward_Stock\Models\inward\batchnowise_report_export;
use Retailcore\Products\Models\product\ProductFeatures;
use Retailcore\Store_Profile\Models\store_profile\company_relationship_tree;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Auth;
use Carbon\Carbon;
use Log;
class InwardBatchNoWiseReportController extends Controller
{
    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();

          $compname       =   company::where('company_id',Auth::user()->company_id)    
                                        ->first();                                           
          $companyname    =   $compname['company_name'];  

        $today_date   =  date("Y-m-d", strtotime(date("d-m-Y")));
        $today_date_inward   = date("d-m-Y");
        $inward_product =  inward_product_detail::select('*',DB::raw('SUM(pending_return_qty *offer_price)/SUM(pending_return_qty) as averagemrp'),DB::raw('SUM(pending_return_qty *cost_rate)/SUM(pending_return_qty) as averagecost'))
            ->where('company_id',Auth::User()->company_id)
            ->where('deleted_at', '=', NULL)
            ->where('batch_no','!=',NULL)
            ->with('product')
            ->with('inward_stock')
            ->withCount([
                'batchinward_product_detail as totalinwardqty' => function($fquery) use ($today_date)
                {
                    $fquery->select(DB::raw('SUM(product_qty+free_qty)'));
                    $fquery->where('batch_no', '=', DB::raw('inward_product_details.batch_no'));
                    $fquery->where('batch_no', '!=', NULL);
                    $fquery->with('inward_stock');
                    $fquery->whereHas('inward_stock',function ($q) use ($today_date)
                    {
                        $q->whereRaw("STR_TO_DATE(inward_date,'%d-%m-%Y') < '$today_date' ");
                    });

                }
            ])
            ->withCount([
                'batchinward_product_detail as currentinward' => function($fquery) use ($today_date)
                {
                    $fquery->select(DB::raw('SUM(product_qty+free_qty)'));
                    $fquery->where('batch_no', '=', DB::raw('inward_product_details.batch_no'));
                    $fquery->where('batch_no', '!=', NULL);
                    $fquery->with('inward_stock');
                    $fquery->whereHas('inward_stock',function ($q) use ($today_date)
                    {
                        $q->whereRaw("STR_TO_DATE(inward_date,'%d-%m-%Y') = '$today_date' ");
                    });

                }
            ])
            ->withCount([
                'sales_product_detail as currentsold' => function($fquery) use ($today_date)
                {
                    $fquery->select(DB::raw('SUM(qty)'));
                    $fquery->where('product_type',1);
                    $fquery->with('sales_bill');
                    $fquery->whereHas('sales_bill',function ($q) use ($today_date)
                    {
                        $q->whereRaw("STR_TO_DATE(bill_date,'%d-%m-%Y') = '$today_date' ");
                        $q->where('sales_type',1);
                    });
                    $fquery->with('price_master_batch_wise');
                    $fquery->whereHas('price_master_batch_wise',function ($q)use($today_date)
                    {
                        $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                    });
                }
            ])
            ->withCount([
                'sales_product_detail as totalsoldqty' => function($fquery) use ($today_date)
                {
                    $fquery->select(DB::raw('SUM(qty)'));
                    $fquery->where('product_type',1);
                    $fquery->with('sales_bill');
                    $fquery->whereHas('sales_bill',function ($q) use ($today_date)
                    {
                        $q->whereRaw("STR_TO_DATE(bill_date,'%d-%m-%Y') < '$today_date' ");
                        $q->where('sales_type',1);
                    });
                    $fquery->with('price_master_batch_wise');
                    $fquery->whereHas('price_master_batch_wise',function ($q)use($today_date)
                    {
                        $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                    });
                }
            ])
            ->withCount([
                'returnbill_product as currentreturn' => function($fquery) use ($today_date)
                {
                    $fquery->select(DB::raw('SUM(qty)'));
                    $fquery->whereRaw("STR_TO_DATE(return_date,'%d-%m-%Y') = '$today_date' ");
                    $fquery->with('price_master_batch_wise');
                    $fquery->whereHas('price_master_batch_wise',function ($q)use($today_date)
                    {
                        $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                    });
                }
            ])
            ->withCount([
                'returnbill_product as totalreturn' => function($fquery) use ($today_date)
                {
                    $fquery->select(DB::raw('SUM(qty)'));
                    $fquery->whereRaw("STR_TO_DATE(return_date,'%d-%m-%Y') < '$today_date' ");
                    $fquery->with('price_master_batch_wise');
                    $fquery->whereHas('price_master_batch_wise',function ($q)use($today_date)
                    {
                        $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                    });
                }
            ])
            ->withCount([
                'returnbill_product as currentrestock' => function($fquery) use ($today_date) {
                    $fquery->select(DB::raw('SUM(restockqty)'));
                    $fquery->whereRaw("STR_TO_DATE(return_date,'%d-%m-%Y') = '$today_date' ");
                    $fquery->with('price_master_batch_wise');
                    $fquery->whereHas('price_master_batch_wise',function ($q)use($today_date)
                    {
                        $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                    });
                }
            ])


            ->withCount([
                'returnbill_product as totalrestock' => function($fquery) use ($today_date) {
                    $fquery->select(DB::raw('SUM(restockqty)'));
                    $fquery->whereRaw("STR_TO_DATE(return_date,'%d-%m-%Y')  < '$today_date' ");
                    $fquery->with('price_master_batch_wise');
                    $fquery->whereHas('price_master_batch_wise',function ($q)use($today_date)
                    {
                        $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                    });
                }
            ])
            ->withCount([
                'damage_product_detail as currentddamage' => function($fquery) use ($today_date)
                {
                    $fquery->select(DB::raw('SUM(product_damage_qty)'));
                    $fquery->where('inward_product_detail_id','=',DB::raw('inward_product_details.inward_product_detail_id'));
                    $fquery->with('damage_product')
                        ->whereHas('damage_product',function ($q) use($today_date){
                            $q->where('damage_type_id',1);
                            $q->whereRaw("STR_TO_DATE(damage_date,'%d-%m-%Y') = '$today_date' ");
                        });
                    $fquery->with('inward_product_detail');
                    $fquery->whereHas('inward_product_detail',function ($q)use($today_date)
                    {
                        $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                    });
                }
            ])
            ->withCount([
                'damage_product_detail as totalddamage' => function($fquery) use ($today_date)
                {
                    $fquery->select(DB::raw('SUM(product_damage_qty)'));
                    $fquery->where('inward_product_detail_id','=',DB::raw('inward_product_details.inward_product_detail_id'));
                    $fquery->with('damage_product')
                        ->whereHas('damage_product',function ($q) use($today_date){
                            $q->where('damage_type_id',1);
                            //  $q->where('damage_date','<',$today_date);
                            $q->whereRaw("STR_TO_DATE(damage_date,'%d-%m-%Y') < '$today_date' ");
                        });
                    $fquery->with('inward_product_detail');
                    $fquery->whereHas('inward_product_detail',function ($q)use($today_date)
                    {
                        $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                    });
                }
            ])
            ->withCount([
                'damage_product_detail as currentused' => function($fquery) use ($today_date)
                {
                    $fquery->select(DB::raw('SUM(product_damage_qty)'));
                    $fquery->where('inward_product_detail_id','=',DB::raw('inward_product_details.inward_product_detail_id'));
                    $fquery->with('damage_product')
                        ->whereHas('damage_product',function ($q) use($today_date)
                        {
                            $q->where('damage_type_id',2);
                            //  $q->where('damage_date','=',$today_date);
                            $q->whereRaw("STR_TO_DATE(damage_date,'%d-%m-%Y') = '$today_date' ");
                        });
                    $fquery->with('inward_product_detail');
                    $fquery->whereHas('inward_product_detail',function ($q)use($today_date)
                    {
                        $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                    });
                }
            ])

            ->withCount([
                'damage_product_detail as totalused' => function($fquery) use ($today_date)
                {
                    $fquery->select(DB::raw('SUM(product_damage_qty)'));
                    $fquery->where('inward_product_detail_id','=',DB::raw('inward_product_details.inward_product_detail_id'));
                    $fquery->with('damage_product')
                        ->whereHas('damage_product',function ($q) use($today_date)
                        {
                            $q->where('damage_type_id',2);
                            $q->whereRaw("STR_TO_DATE(damage_date,'%d-%m-%Y') < '$today_date' ");
                        })
                        ->with('inward_product_detail')
                        ->whereHas('inward_product_detail',function ($q)use($today_date)
                        {
                            $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                        });
                }
            ])
            ->withCount([
                'debit_product_detail as currentsuppreturn' => function($fquery) use ($today_date)
                {
                    $fquery->select(DB::raw('SUM(return_qty)'));
                    $fquery->with('debit_note')
                        ->whereHas('debit_note',function ($q) use($today_date)
                        {
                            // $q->where('debit_date','=',$today_date);
                            $q->whereRaw("STR_TO_DATE(debit_date,'%d-%m-%Y') = '$today_date' ");
                        });
                    $fquery->with('price_master_batch_wise');
                    $fquery->whereHas('price_master_batch_wise',function ($q)use($today_date)
                    {
                        $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                    });
                }
            ])
            ->withCount([
                'debit_product_detail as totalsuppreturn' => function($fquery) use ($today_date)
                {
                    $fquery->select(DB::raw('SUM(return_qty)'));
                    $fquery->with('debit_note')
                        ->whereHas('debit_note',function ($q) use($today_date)
                        {
                            $q->whereRaw("STR_TO_DATE(debit_date,'%d-%m-%Y') < '$today_date' ");
                        });
                    $fquery->with('price_master_batch_wise');
                    $fquery->whereHas('price_master_batch_wise',function ($q)use($today_date)
                    {
                        $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                    });
                }
            ])
            ->withCount([
                    'sales_product_detail as totalfranchiseqty' => function($fquery) use ($today_date) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('product_type',1);
                        $fquery->with('sales_bill')->whereHas('sales_bill',function ($q) use ($today_date){
                            $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') < '$today_date'");
                            $q->where('sales_type',2);
                        });
                        $fquery->with('price_master_batch_wise');
                        $fquery->whereHas('price_master_batch_wise',function ($q)use($today_date)
                        {
                            $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                        });
                    }
            ])
            ->withCount([
                    'sales_product_detail as currentfranchiseqty' => function($fquery) use ($today_date) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('product_type',1);
                        $fquery->with('sales_bill')->whereHas('sales_bill',function ($q) use ($today_date){
                            $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$today_date' and '$today_date'");
                            $q->where('sales_type',2);
                        });
                        $fquery->with('price_master_batch_wise');
                        $fquery->whereHas('price_master_batch_wise',function ($q)use($today_date)
                        {
                            $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                        });
                    }
                ])
            ->withCount([
                    'consign_products_detail as totalconsign' => function($fquery) use ($today_date) {
                        $fquery->select(DB::raw('SUM(qty)'));
                         $fquery->with('consign_bill')->whereHas('consign_bill',function ($q) use ($today_date){
                            $q->whereRaw("STR_TO_DATE(consign_bills.bill_date,'%d-%m-%Y') < '$today_date'");
                        });
                        $fquery->with('price_master_batch_wise');
                        $fquery->whereHas('price_master_batch_wise',function ($q)use($today_date)
                        {
                            $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                        });
                    }
                ])
                ->withCount([
                    'consign_products_detail as currentconsign' => function($fquery) use ($today_date) {
                        $fquery->select(DB::raw('SUM(qty)'));
                         $fquery->with('consign_bill')->whereHas('consign_bill',function ($q) use ($today_date){
                            $q->whereRaw("STR_TO_DATE(consign_bills.bill_date,'%d-%m-%Y') between '$today_date' and '$today_date'");
                        });
                        $fquery->with('price_master_batch_wise');
                        $fquery->whereHas('price_master_batch_wise',function ($q)use($today_date)
                        {
                            $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                        });
                    }
                ])
                ->withCount([
                    'stock_transfer_detail as currentstransfer' => function($fquery) use ($today_date) {
                        $fquery->select(DB::raw('SUM(product_qty)'));
                         $fquery->with('stock_transfer')->whereHas('stock_transfer',function ($q) use ($today_date){
                            $q->whereRaw("STR_TO_DATE(stock_transfers.stock_transfer_date,'%d-%m-%Y') between '$today_date' and '$today_date'");
                            $q->whereNull('sales_bill_id');
                        });
                        $fquery->with('price_master_batch_wise');
                        $fquery->whereHas('price_master_batch_wise',function ($q)use($today_date)
                        {
                            $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                        });
                    }
                ])
                ->withCount([
                    'stock_transfer_detail as totalstransfer' => function($fquery) use ($today_date) {
                        $fquery->select(DB::raw('SUM(product_qty)'));
                         $fquery->with('stock_transfer')->whereHas('stock_transfer',function ($q) use ($today_date){
                            $q->whereRaw("STR_TO_DATE(stock_transfers.stock_transfer_date,'%d-%m-%Y') < '$today_date'");
                            $q->whereNull('sales_bill_id');
                        });
                        $fquery->with('price_master_batch_wise');
                        $fquery->whereHas('price_master_batch_wise',function ($q)use($today_date)
                        {
                            $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                        });
                    }
                ])
            ->groupBy('product_id','batch_no')
            ->orderBy('inward_product_detail_id', 'DESC');

            // echo '<pre>';
            // print_r($inward_product);
            // exit;


                // $product_all = collect();
                // $alldata =  $product_all->merge($inward_product->get());

                $custom    =  collect();
                $data      =  $custom->merge($inward_product->get());
                $product   =  $inward_product->paginate(10);

                 $product_features =  ProductFeatures::getproduct_feature('');
                 foreach($product as $pp=>$pval)
                 {
                    // echo '<pre>';
                    // print_r($bval);
                  if(isset($pval['product']['product_features_relationship']) && $pval['product']['product_features_relationship'] != '')
                  {
                      foreach ($product_features AS $kk => $vv)
                      {
                          $html_id = $vv['html_id'];

                          if($pval['product']['product_features_relationship'][$html_id] != '' && $pval['product']['product_features_relationship'][$html_id] != NULL)
                          {
                              $nm =  product::feature_value($vv['product_features_id'],$pval['product']['product_features_relationship'][$html_id]);
                              $pval['product'][$html_id] =$nm;
                          }
                      }
                  }
                }



                $totinwardqty = 0;
                $totsoldqty = 0;
                $totrestock = 0;
                $totusedqty = 0;
                $totdamageqty =0;
                $totsupprqty=0;
                $currinward = 0;
                $currsold = 0;
                $currrestock=0;
                $currusedqty=0;
                $currdamageqty =0;
                $currddamageqty=0;
                $ttotaldamage = 0;
                $currsupprqty = 0;
                $currconsignqty = 0;
                $totconsignqty = 0;
                $currstransfer = 0;
                $totstransfer=0;
                $currfranqty=0;
                $totfranqty=0;

                $count=0;

            foreach ($data as $totproductkey=>$ttotproduct)
            {


                $count++;
                $totinwardqty          +=   $ttotproduct['totalinwardqty'];
                $totsoldqty            +=   $ttotproduct['totalsoldqty'];
                $totrestock            +=   $ttotproduct['totalrestock'];
                $totusedqty            +=   $ttotproduct['totalused'];
                $totdamageqty          +=   $ttotproduct['totalddamage'];
                $totsupprqty           +=   $ttotproduct['totalsuppreturn'];
                $totconsignqty         +=   $ttotproduct['totalconsign'];
                $currinward            +=   $ttotproduct['currentinward'];
                $currsold              +=   $ttotproduct['currentsold'];
                $currrestock           +=   $ttotproduct['currentrestock'];
                $currusedqty           +=   $ttotproduct['currentused'];
                $currdamageqty         +=   $ttotproduct['currentdamage'];
                $currddamageqty        +=   $ttotproduct['currentddamage'];
                $currsupprqty          +=   $ttotproduct['currentsuppreturn'];
                $currconsignqty        +=   $ttotproduct['currentconsign'];
                $currfranqty           +=   $ttotproduct['currentfranchiseqty'];
                $totfranqty            +=   $ttotproduct['totalfranchiseqty'];
                $currstransfer         +=   $ttotproduct['currentstransfer'];
                $totstransfer          +=   $ttotproduct['totalstransfer'];


            }

               $totopening     =   $totinwardqty - $totsoldqty + $totrestock - $totusedqty - $totdamageqty - $totsupprqty - $totconsignqty-$totfranqty-$totstransfer;
               $totstock       =   $totopening +$currinward -$currsold + $currrestock-$currusedqty - $currddamageqty  - $currsupprqty - $currconsignqty-$currfranqty-$currstransfer;
               $ttotaldamage   =   $currdamageqty + $currddamageqty;


        return view('inward_stock::inward/batch_no_wise_report',compact('product','totopening','totstock','currinward','currsold','currrestock','currusedqty','ttotaldamage','currsupprqty','count','totconsignqty','currconsignqty','currfranqty','currstransfer','get_store','companyname'));

    }

    public function batchno_search(Request $request)
    {

      if($request->search_val !='')
        {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $result = inward_product_detail::where('company_id',Auth::user()->company_id)
                  ->where('deleted_at','=',NULL)
                  ->whereNotNull('batch_no')
                  ->select('inward_product_detail_id','batch_no')
                  ->groupBy('batch_no')
                  ->get();

        if(!empty($result))
            {
           
                foreach($result as $productkey=>$productvalue){

                       $json[$productkey]['label'] = $productvalue['batch_no'];
                       $json[$productkey]['inward_product_detail_id'] = $productvalue['inward_product_detail_id'];

                      
                }
            }
           
            return json_encode($json);
        }
        else
        {
          $json = [];
          return json_encode($json);
        }
       
    }
    function batch_no_wise_record(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->ajax())
        {
            $data = $request->all();
            $sort_by = $data['sortby'];
            $sort_type = $data['sorttype'];
            $query = isset($data['query']) && $data['query'] != '' ? $data['query'] : '';
            $query = str_replace(" ", "", $query);

            $inward_start_date =  date("Y-m-d", strtotime(date("d-m-Y")));
            $today_date =  date('d-m-Y');
            $end_date =  date('d-m-Y');
            $inward_end_date =  date("Y-m-d", strtotime(date("d-m-Y")));

            $get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();

            if(isset($query) && $query != '' && isset($query['store_name']) && $query['store_name'] != '')
            {
                 $company_id      =   $query['store_name'];
                 $compname        =   company_profile::where('company_profile_id',$company_id)    
                                            ->first();                                           
                 $companyname    =   $compname['full_name']; 
            }
            else
            {
                $company_id      =   Auth::user()->company_id;
                $compname        =   company::where('company_id',$company_id)    
                                            ->first();                                           
                 $companyname    =   $compname['company_name']; 
            }

            if($query['from_date']!='')
            {
                $inward_start_date = date("Y-m-d",strtotime($query['from_date']));
                $inward_end_date  =  date("Y-m-d",strtotime($query['to_date']));
                $today_date =  $query['from_date'];
                $end_date  = $query['to_date'];
            }

            $inward_product =  inward_product_detail::select('*',DB::raw('SUM(pending_return_qty *offer_price)/SUM(pending_return_qty) as averagemrp'),DB::raw('SUM(pending_return_qty *cost_rate)/SUM(pending_return_qty) as averagecost'))
            ->where('company_id',$company_id)
            ->where('deleted_at', '=', NULL)
            ->where('batch_no','!=',NULL)
            ->with('product')
            ->with('inward_stock')
            ->withCount([
                'batchinward_product_detail as totalinwardqty' => function($fquery) use ($inward_start_date,$inward_end_date,$company_id)
                {
                    $fquery->select(DB::raw('SUM(product_qty+free_qty)'));
                    $fquery->where('company_id',$company_id);
                    $fquery->where('batch_no', '=', DB::raw('inward_product_details.batch_no'));
                    $fquery->where('batch_no', '!=', NULL);
                    $fquery->with('inward_stock');
                    $fquery->whereHas('inward_stock',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                    {
                        $q->whereRaw("STR_TO_DATE(inward_date,'%d-%m-%Y') < '$inward_start_date' ");
                        $q->where('company_id',$company_id);
                    });

                }
            ])
            ->withCount([
                'batchinward_product_detail as currentinward' => function($fquery) use ($inward_start_date,$inward_end_date,$company_id)
                {
                    $fquery->select(DB::raw('SUM(product_qty+free_qty)'));
                    $fquery->where('company_id',$company_id);
                    $fquery->where('batch_no', '=', DB::raw('inward_product_details.batch_no'));
                    $fquery->where('batch_no', '!=', NULL);
                    $fquery->with('inward_stock');
                    $fquery->whereHas('inward_stock',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                    {
                        $q->whereRaw("STR_TO_DATE(inward_date,'%d-%m-%Y') between '$inward_start_date' and '$inward_end_date'");
                        $q->where('company_id',$company_id);
                    });

                }
            ])
            ->withCount([
                'sales_product_detail as currentsold' => function($fquery) use ($inward_start_date,$inward_end_date,$company_id)
                {
                    $fquery->select(DB::raw('SUM(qty)'));
                    $fquery->where('company_id',$company_id);
                    $fquery->where('product_type',1);
                    $fquery->with('sales_bill');
                    $fquery->whereHas('sales_bill',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                    {
                        $q->whereRaw("STR_TO_DATE(bill_date,'%d-%m-%Y') between '$inward_start_date' and '$inward_end_date'");
                        $q->where('sales_type',1);
                        $q->where('company_id',$company_id);
                    });
                    $fquery->with('price_master_batch_wise');
                    $fquery->whereHas('price_master_batch_wise',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                    {
                        $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                        $q->where('company_id',$company_id);
                    });
                }
            ])
            ->withCount([
                'sales_product_detail as totalsoldqty' => function($fquery) use ($inward_start_date,$inward_end_date,$company_id)
                {
                    $fquery->select(DB::raw('SUM(qty)'));
                    $fquery->where('company_id',$company_id);
                    $fquery->where('product_type',1);
                    $fquery->with('sales_bill');
                    $fquery->whereHas('sales_bill',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                    {
                        $q->whereRaw("STR_TO_DATE(bill_date,'%d-%m-%Y') < '$inward_start_date' ");
                        $q->where('sales_type',1);
                        $q->where('company_id',$company_id);
                    });
                    $fquery->with('price_master_batch_wise');
                    $fquery->whereHas('price_master_batch_wise',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                    {
                        $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                        $q->where('company_id',$company_id);
                    });
                }
            ])
            ->withCount([
                'returnbill_product as currentreturn' => function($fquery) use ($inward_start_date,$inward_end_date,$company_id)
                {
                    $fquery->select(DB::raw('SUM(qty)'));
                    $fquery->whereRaw("STR_TO_DATE(return_date,'%d-%m-%Y') between '$inward_start_date' and '$inward_end_date'");
                    $fquery->where('company_id',$company_id);
                    $fquery->with('price_master_batch_wise');
                    $fquery->whereHas('price_master_batch_wise',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                    {
                        $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                        $q->where('company_id',$company_id);
                    });
                }
            ])
            ->withCount([
                'returnbill_product as totalreturn' => function($fquery) use ($inward_start_date,$inward_end_date,$company_id)
                {
                    $fquery->select(DB::raw('SUM(qty)'));
                    $fquery->whereRaw("STR_TO_DATE(return_date,'%d-%m-%Y') < '$inward_start_date' ");
                    $fquery->where('company_id',$company_id);
                    $fquery->with('price_master_batch_wise');
                    $fquery->whereHas('price_master_batch_wise',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                    {
                        $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                        $q->where('company_id',$company_id);
                    });
                }
            ])
            ->withCount([
                'returnbill_product as currentrestock' => function($fquery) use ($inward_start_date,$inward_end_date,$company_id) {
                    $fquery->select(DB::raw('SUM(restockqty)'));
                    $fquery->where('company_id',$company_id);
                    $fquery->whereRaw("STR_TO_DATE(return_date,'%d-%m-%Y') between '$inward_start_date' and '$inward_end_date'");
                    $fquery->with('price_master_batch_wise');
                    $fquery->whereHas('price_master_batch_wise',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                    {
                        $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                        $q->where('company_id',$company_id);
                    });
                }
            ])


            ->withCount([
                'returnbill_product as totalrestock' => function($fquery) use ($inward_start_date,$inward_end_date,$company_id) {
                    $fquery->select(DB::raw('SUM(restockqty)'));
                    $fquery->whereRaw("STR_TO_DATE(return_date,'%d-%m-%Y')  < '$inward_start_date' ");
                    $fquery->where('company_id',$company_id);
                    $fquery->with('price_master_batch_wise');
                    $fquery->whereHas('price_master_batch_wise',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                    {
                        $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                        $q->where('company_id',$company_id);
                    });
                }
            ])
            ->withCount([
                'damage_product_detail as currentddamage' => function($fquery) use ($inward_start_date,$inward_end_date,$company_id)
                {
                    $fquery->select(DB::raw('SUM(product_damage_qty)'));
                    $fquery->where('company_id',$company_id);
                    $fquery->where('inward_product_detail_id','=',DB::raw('inward_product_details.inward_product_detail_id'));
                    $fquery->with('damage_product')
                        ->whereHas('damage_product',function ($q) use ($inward_start_date,$inward_end_date,$company_id){
                            $q->where('damage_type_id',1);
                            $q->whereRaw("STR_TO_DATE(damage_date,'%d-%m-%Y') between '$inward_start_date' and '$inward_end_date'");
                            $q->where('company_id',$company_id);
                        });
                    $fquery->with('inward_product_detail');
                    $fquery->whereHas('inward_product_detail',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                    {
                        $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                        $q->where('company_id',$company_id);
                    });
                }
            ])
            ->withCount([
                'damage_product_detail as totalddamage' => function($fquery) use ($inward_start_date,$inward_end_date,$company_id)
                {
                    $fquery->select(DB::raw('SUM(product_damage_qty)'));
                    $fquery->where('inward_product_detail_id','=',DB::raw('inward_product_details.inward_product_detail_id'));
                    $fquery->where('company_id',$company_id);
                    $fquery->with('damage_product')
                        ->whereHas('damage_product',function ($q) use ($inward_start_date,$inward_end_date,$company_id){
                            $q->where('damage_type_id',1);
                            $q->where('company_id',$company_id);
                            $q->whereRaw("STR_TO_DATE(damage_date,'%d-%m-%Y') < '$inward_start_date' ");
                        });
                    $fquery->with('inward_product_detail');
                    $fquery->whereHas('inward_product_detail',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                    {
                        $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                        $q->where('company_id',$company_id);
                    });
                }
            ])
            ->withCount([
                'damage_product_detail as currentused' => function($fquery) use ($inward_start_date,$inward_end_date,$company_id)
                {
                    $fquery->select(DB::raw('SUM(product_damage_qty)'));
                    $fquery->where('company_id',$company_id);
                    $fquery->where('inward_product_detail_id','=',DB::raw('inward_product_details.inward_product_detail_id'));
                    $fquery->with('damage_product')
                        ->whereHas('damage_product',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                        {
                            $q->where('damage_type_id',2);
                            $q->where('company_id',$company_id);
                            $q->whereRaw("STR_TO_DATE(damage_date,'%d-%m-%Y') between '$inward_start_date' and '$inward_end_date'");
                        });
                    $fquery->with('inward_product_detail');
                    $fquery->whereHas('inward_product_detail',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                    {
                        $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                        $q->where('company_id',$company_id);
                    });
                }
            ])

            ->withCount([
                'damage_product_detail as totalused' => function($fquery) use ($inward_start_date,$inward_end_date,$company_id)
                {
                    $fquery->select(DB::raw('SUM(product_damage_qty)'));
                    $fquery->where('company_id',$company_id);
                    $fquery->where('inward_product_detail_id','=',DB::raw('inward_product_details.inward_product_detail_id'));
                    $fquery->with('damage_product')
                        ->whereHas('damage_product',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                        {
                            $q->where('damage_type_id',2);
                            $q->whereRaw("STR_TO_DATE(damage_date,'%d-%m-%Y') < '$inward_start_date' ");
                            $q->where('company_id',$company_id);
                        })
                        ->with('inward_product_detail')
                        ->whereHas('inward_product_detail',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                        {
                            $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                            $q->where('company_id',$company_id);
                        });
                }
            ])
            ->withCount([
                'debit_product_detail as currentsuppreturn' => function($fquery) use ($inward_start_date,$inward_end_date,$company_id)
                {
                    $fquery->select(DB::raw('SUM(return_qty)'));
                    $fquery->where('company_id',$company_id);
                    $fquery->with('debit_note')
                        ->whereHas('debit_note',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                        {
                            $q->where('company_id',$company_id);
                            $q->whereRaw("STR_TO_DATE(debit_date,'%d-%m-%Y') between '$inward_start_date' and '$inward_end_date'");
                        });
                    $fquery->with('price_master_batch_wise');
                    $fquery->whereHas('price_master_batch_wise',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                    {
                        $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                        $q->where('company_id',$company_id);
                    });
                }
            ])
            ->withCount([
                'debit_product_detail as totalsuppreturn' => function($fquery) use ($inward_start_date,$inward_end_date,$company_id)
                {
                    $fquery->select(DB::raw('SUM(return_qty)'));
                    $fquery->where('company_id',$company_id);
                    $fquery->with('debit_note')
                        ->whereHas('debit_note',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                        {
                            $q->whereRaw("STR_TO_DATE(debit_date,'%d-%m-%Y') < '$inward_start_date' ");
                            $q->where('company_id',$company_id);
                        });
                    $fquery->with('price_master_batch_wise');
                    $fquery->whereHas('price_master_batch_wise',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                    {
                        $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                        $q->where('company_id',$company_id);
                    });
                }
            ])
            ->withCount([
                    'sales_product_detail as totalfranchiseqty' => function($fquery) use ($inward_start_date,$inward_end_date,$company_id) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',$company_id);
                        $fquery->where('product_type',1);
                        $fquery->with('sales_bill')->whereHas('sales_bill',function ($q) use ($inward_start_date,$inward_end_date,$company_id){
                            $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') < '$inward_start_date'");
                            $q->where('sales_type',2);
                            $q->where('company_id',$company_id);
                        });
                        $fquery->with('price_master_batch_wise');
                        $fquery->whereHas('price_master_batch_wise',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                        {
                            $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                            $q->where('company_id',$company_id);
                        });
                    }
            ])
            ->withCount([
                    'sales_product_detail as currentfranchiseqty' => function($fquery) use ($inward_start_date,$inward_end_date,$company_id) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('product_type',1);
                        $fquery->where('company_id',$company_id);
                        $fquery->with('sales_bill')->whereHas('sales_bill',function ($q) use ($inward_start_date,$inward_end_date,$company_id){
                            $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$inward_start_date' and '$inward_end_date'");
                            $q->where('sales_type',2);
                            $q->where('company_id',$company_id);
                        });
                        $fquery->with('price_master_batch_wise');
                        $fquery->whereHas('price_master_batch_wise',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                        {
                            $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                            $q->where('company_id',$company_id);
                        });
                    }
                ])
            ->withCount([
                    'consign_products_detail as totalconsign' => function($fquery) use ($inward_start_date,$inward_end_date,$company_id) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',$company_id);
                         $fquery->with('consign_bill')->whereHas('consign_bill',function ($q) use ($inward_start_date,$inward_end_date,$company_id){
                            $q->whereRaw("STR_TO_DATE(consign_bills.bill_date,'%d-%m-%Y') < '$inward_start_date'");
                            $q->where('company_id',$company_id);
                        });
                        $fquery->with('price_master_batch_wise');
                        $fquery->whereHas('price_master_batch_wise',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                        {
                            $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                            $q->where('company_id',$company_id);
                        });
                    }
                ])
                ->withCount([
                    'consign_products_detail as currentconsign' => function($fquery) use ($inward_start_date,$inward_end_date,$company_id) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',$company_id);
                         $fquery->with('consign_bill')->whereHas('consign_bill',function ($q) use ($inward_start_date,$inward_end_date,$company_id){
                            $q->whereRaw("STR_TO_DATE(consign_bills.bill_date,'%d-%m-%Y') between '$inward_start_date' and '$inward_end_date'");
                            $q->where('company_id',$company_id);
                        });
                        $fquery->with('price_master_batch_wise');
                        $fquery->whereHas('price_master_batch_wise',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                        {
                            $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                            $q->where('company_id',$company_id);
                        });
                    }
                ])
                ->withCount([
                    'stock_transfer_detail as currentstransfer' => function($fquery) use ($inward_start_date,$inward_end_date,$company_id) {
                        $fquery->select(DB::raw('SUM(product_qty)'));
                        $fquery->where('company_id',$company_id);
                         $fquery->with('stock_transfer')->whereHas('stock_transfer',function ($q) use ($inward_start_date,$inward_end_date,$company_id){
                            $q->whereRaw("STR_TO_DATE(stock_transfers.stock_transfer_date,'%d-%m-%Y') between '$inward_start_date' and '$inward_end_date'");
                            $q->whereNull('sales_bill_id');
                            $q->where('company_id',$company_id);
                        });
                        $fquery->with('price_master_batch_wise');
                        $fquery->whereHas('price_master_batch_wise',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                        {
                            $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                            $q->where('company_id',$company_id);
                        });
                    }
                ])
                ->withCount([
                    'stock_transfer_detail as totalstransfer' => function($fquery) use ($inward_start_date,$inward_end_date,$company_id) {
                        $fquery->select(DB::raw('SUM(product_qty)'));
                        $fquery->where('company_id',$company_id);
                         $fquery->with('stock_transfer')->whereHas('stock_transfer',function ($q) use ($inward_start_date,$inward_end_date,$company_id){
                            $q->whereRaw("STR_TO_DATE(stock_transfers.stock_transfer_date,'%d-%m-%Y') < '$inward_start_date'");
                            $q->whereNull('sales_bill_id');
                            $q->where('company_id',$company_id);
                        });
                        $fquery->with('price_master_batch_wise');
                        $fquery->whereHas('price_master_batch_wise',function ($q) use ($inward_start_date,$inward_end_date,$company_id)
                        {
                            $q->where('batch_no','=',DB::raw('inward_product_details.batch_no'));
                            $q->where('company_id',$company_id);
                        });
                    }
                ]);

            if(isset($query) && $query != '' && $query['batchnosearch'] != '')
            {
                $inward_product->where('batch_no',$query['batchnosearch']);
            }

            if(isset($query) && $query != '' && $query['productsearch'] != '')
            {

                      $prquery = product::select('product_id')
                     ->where('deleted_at','=',NULL)
                     ->with('price_master')
                     ->whereHas('price_master',function ($q) use($company_id){
                            $q->where('company_id',$company_id);
                       });

                   if(strpos($query['productsearch'], '_') !== false)
                    {
                        $prodname      =   explode('_',$query['productsearch']);
                        $prod_barcode  =  $prodname[0];
                        $prod_name     =  $prodname[1];

                        $prquery->where('product_name', 'LIKE', "%$prod_name%")
                                 ->orWhere('product_system_barcode', 'LIKE', "%$prod_barcode%")
                                 ->orWhere('supplier_barcode', 'LIKE', "%$prod_barcode%");

                    }
                    else
                    {
                        $prod_barcode   =   $query['productsearch'];
                        $prod_name      =   $query['productsearch'];
                        $prquery->where('product_name', 'LIKE', "%$prod_name%")
                                 ->orWhere('product_system_barcode', 'LIKE', "%$prod_barcode%");

                    }

                    $prodresult  =  $prquery->get();


                     $inward_product->whereIn('product_id',$prodresult);
                }

                $inward_product = $inward_product->groupBy('product_id','batch_no')
                                ->orderBy($sort_by,$sort_type);



                $custom    =  collect();
                $data      =  $custom->merge($inward_product->get());
                $product   =  $inward_product->paginate(10);

                $product_features =  ProductFeatures::getproduct_feature('');
                 foreach($product as $pp=>$pval)
                 {
                    // echo '<pre>';
                    // print_r($bval);
                  if(isset($pval['product']['product_features_relationship']) && $pval['product']['product_features_relationship'] != '')
                  {
                      foreach ($product_features AS $kk => $vv)
                      {
                          $html_id = $vv['html_id'];

                          if($pval['product']['product_features_relationship'][$html_id] != '' && $pval['product']['product_features_relationship'][$html_id] != NULL)
                          {
                              $nm =  product::feature_value($vv['product_features_id'],$pval['product']['product_features_relationship'][$html_id]);
                              $pval['product'][$html_id] =$nm;
                          }
                      }
                  }
                }




                $totinwardqty = 0;
                $totsoldqty = 0;
                $totrestock = 0;
                $totusedqty = 0;
                $totdamageqty =0;
                $totsupprqty=0;
                $currinward = 0;
                $currsold = 0;
                $currrestock=0;
                $currusedqty=0;
                $currdamageqty =0;
                $currddamageqty=0;
                $ttotaldamage = 0;
                $currsupprqty = 0;
                $currconsignqty = 0;
                $totconsignqty = 0;
                $currstransfer = 0;
                $totstransfer=0;
                $currfranqty=0;
                $totfranqty=0;
                $count=0;

            foreach ($data as $totproductkey=>$ttotproduct)
            {


                $count++;
                $totinwardqty          +=   $ttotproduct['totalinwardqty'];
                $totsoldqty            +=   $ttotproduct['totalsoldqty'];
                $totrestock            +=   $ttotproduct['totalrestock'];
                $totusedqty            +=   $ttotproduct['totalused'];
                $totdamageqty          +=   $ttotproduct['totalddamage'];
                $totsupprqty           +=   $ttotproduct['totalsuppreturn'];
                $totconsignqty         +=   $ttotproduct['totalconsign'];
                $currinward            +=   $ttotproduct['currentinward'];
                $currsold              +=   $ttotproduct['currentsold'];
                $currrestock           +=   $ttotproduct['currentrestock'];
                $currusedqty           +=   $ttotproduct['currentused'];
                $currdamageqty         +=   $ttotproduct['currentdamage'];
                $currddamageqty        +=   $ttotproduct['currentddamage'];
                $currsupprqty          +=   $ttotproduct['currentsuppreturn'];
                $currconsignqty        +=   $ttotproduct['currentconsign'];
                $currfranqty           +=   $ttotproduct['currentfranchiseqty'];
                $totfranqty            +=   $ttotproduct['totalfranchiseqty'];
                $currstransfer         +=   $ttotproduct['currentstransfer'];
                $totstransfer          +=   $ttotproduct['totalstransfer'];


            }

               $totopening     =   $totinwardqty - $totsoldqty + $totrestock - $totusedqty - $totdamageqty - $totsupprqty - $totconsignqty-$totfranqty-$totstransfer;
               $totstock       =   $totopening +$currinward -$currsold + $currrestock-$currusedqty - $currddamageqty  - $currsupprqty - $currconsignqty-$currfranqty-$currstransfer;
               $ttotaldamage   =   $currdamageqty + $currddamageqty;


         return view('inward_stock::inward/batch_no_wise_report_data',compact('product','totopening','totstock','currinward','currsold','currrestock','currusedqty','ttotaldamage','currsupprqty','count','totconsignqty','currconsignqty','currconsignqty','currstransfer','currfranqty','get_store','companyname'));


        }


    }

    public function export_batchno_details(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        return Excel::download(new batchnowise_report_export($request->from_date,$request->to_date,$request->productsearch,$request->batchnosearch), 'BatchnoWise-Report-Export.xlsx');

    }

}
