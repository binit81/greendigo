<?php

namespace Retailcore\SalesReport\Http\Controllers;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Retailcore\Products\Models\product\product;
use Retailcore\Products\Models\product\category;
use Retailcore\Products\Models\product\brand;
use Retailcore\SalesReport\Models\stockreport_export;
use Retailcore\Sales\Models\sales_product_detail;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Retailcore\Products\Models\product\ProductFeatures;
use Retailcore\Store_Profile\Models\store_profile\company_relationship_tree;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use App\company;
use Illuminate\Support\Facades\DB;
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

                     // $product = product::select("products.*",DB::raw("(SELECT SUM(inward_product_details.product_qty + inward_product_details.free_qty) FROM inward_product_details WHERE inward_product_details.product_id = products.product_id and DATE(inward_product_details.created_at) < '$inwarddate' GROUP BY inward_product_details.product_id) as totalinwardqty"),DB::raw("(SELECT SUM(sales_product_details.qty) FROM sales_product_details WHERE sales_product_details.product_id = products.product_id and DATE(sales_product_details.created_at) < '$inwarddate' and sales_product_details.deleted_at IS NULL GROUP BY sales_product_details.product_id) as totalsoldqty"),DB::raw("(SELECT SUM(inward_product_details.product_qty + inward_product_details.free_qty) FROM inward_product_details WHERE inward_product_details.product_id = products.product_id and DATE(inward_product_details.created_at) = '$inwarddate' GROUP BY inward_product_details.product_id) as currentinward"),DB::raw("(SELECT SUM(sales_product_details.qty) FROM sales_product_details WHERE sales_product_details.product_id = products.product_id and DATE(sales_product_details.created_at) = '$inwarddate' and sales_product_details.deleted_at IS NULL GROUP BY sales_product_details.product_id) as currentsold"),DB::raw("(SELECT SUM(price_masters.product_qty *price_masters.offer_price)/SUM(price_masters.product_qty) FROM price_masters WHERE price_masters.product_id = products.product_id GROUP BY price_masters.product_id) as averagemrp"))->where('item_type', '=', 1)->orderBy('product_id','DESC')->paginate(10);

   ///final query/////////////////////////////////////////////////////////////////////////////////////////////////


             $get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();

              $compname       =   company::where('company_id',Auth::user()->company_id)    
                                            ->first();                                           
              $companyname    =   $compname['company_name'];                              


             $inwarddate = date("Y-m-d");
             $billdate   = date("d-m-Y");

             $query  = product::select('product_name','product_system_barcode','supplier_barcode','product_id','sku_code','uqc_id','hsn_sac_code','product_code')
                ->where('deleted_at', '=', NULL)
               ->withCount([
                    'inward_product_detail as totalinwardqty' => function($fquery) use ($inwarddate) {
                        $fquery->select(DB::raw('SUM(product_qty+free_qty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                        $fquery->with('inward_stock')->whereHas('inward_stock',function ($q) use ($inwarddate){
                            $q->whereRaw("STR_TO_DATE(inward_stocks.inward_date,'%d-%m-%Y') < '$inwarddate'");
                            $q->where('company_id',Auth::user()->company_id);
                        });

                    }
                ])
                ->withCount([
                    'sales_product_detail as totalsoldqty' => function($fquery) use ($inwarddate) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                        $fquery->where('product_type',1);
                        $fquery->with('sales_bill')->whereHas('sales_bill',function ($q) use ($inwarddate){
                            $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') < '$inwarddate'");
                            $q->where('sales_type',1);
                            $q->where('company_id',Auth::user()->company_id);
                        });
                    }
                ])
                ->withCount([
                    'sales_product_detail as totalfranchiseqty' => function($fquery) use ($inwarddate) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                        $fquery->where('product_type',1);
                        $fquery->with('sales_bill')->whereHas('sales_bill',function ($q) use ($inwarddate){
                            $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') < '$inwarddate'");
                            $q->where('sales_type',2);
                            $q->where('company_id',Auth::user()->company_id);
                        });
                    }
                ])
                ->withCount([
                    'inward_product_detail as currentinward' => function($fquery) use ($inwarddate) {
                        $fquery->select(DB::raw('SUM(product_qty+free_qty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                         $fquery->with('inward_stock')->whereHas('inward_stock',function ($q) use ($inwarddate) {
                            $q->whereRaw("STR_TO_DATE(inward_stocks.inward_date,'%d-%m-%Y') between '$inwarddate' and '$inwarddate'");
                            $q->where('company_id',Auth::user()->company_id);
                        });

                    }
                ])
                ->withCount([
                    'sales_product_detail as currentsold' => function($fquery) use ($inwarddate) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                        $fquery->where('product_type',1);
                         $fquery->with('sales_bill')->whereHas('sales_bill',function ($q) use ($inwarddate){
                            $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$inwarddate' and '$inwarddate'");
                            $q->where('sales_type',1);
                            $q->where('company_id',Auth::user()->company_id);
                        });

                    }
                ])
                ->withCount([
                    'sales_product_detail as currentfranchiseqty' => function($fquery) use ($inwarddate) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                        $fquery->where('product_type',1);
                        $fquery->with('sales_bill')->whereHas('sales_bill',function ($q) use ($inwarddate){
                            $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$inwarddate' and '$inwarddate'");
                            $q->where('sales_type',2);
                            $q->where('company_id',Auth::user()->company_id);
                        });
                    }
                ])
                ->withCount([
                    'price_master as averagemrp' => function($fquery) use ($inwarddate) {
                        $fquery->select(DB::raw('SUM(product_qty *offer_price)/SUM(product_qty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                        $fquery->groupBy('product_id');
                    }
                ])
                ->withCount([
                    'returnbill_product as totalreturn' => function($fquery) use ($inwarddate) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                        $fquery->whereRaw("STR_TO_DATE(return_date,'%d-%m-%Y') < '$inwarddate'");
                    }
                ])
                ->withCount([
                    'returnbill_product as currentreturn' => function($fquery) use ($inwarddate) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                        $fquery->whereRaw("STR_TO_DATE(return_date,'%d-%m-%Y') between '$inwarddate' and '$inwarddate'");
                    }
                ])
                ->withCount([
                    'returnbill_product as totalrestock' => function($fquery) use ($inwarddate) {
                        $fquery->select(DB::raw('SUM(restockqty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                        $fquery->whereRaw("STR_TO_DATE(return_date,'%d-%m-%Y') < '$inwarddate'");
                    }
                ])
                ->withCount([
                    'returnbill_product as totaldamage' => function($fquery) use ($inwarddate) {
                        $fquery->select(DB::raw('SUM(damageqty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                        $fquery->whereRaw("STR_TO_DATE(return_date,'%d-%m-%Y') < '$inwarddate'");
                    }
                ])
                ->withCount([
                    'returnbill_product as currentrestock' => function($fquery) use ($inwarddate) {
                        $fquery->select(DB::raw('SUM(restockqty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                        $fquery->whereRaw("STR_TO_DATE(return_date,'%d-%m-%Y') between '$inwarddate' and '$inwarddate'");
                        $fquery->where('restockstatus','1');
                    }
                ])
                ->withCount([
                    'returnbill_product as currentdamage' => function($fquery) use ($inwarddate) {
                        $fquery->select(DB::raw('SUM(damageqty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                        $fquery->whereRaw("STR_TO_DATE(return_date,'%d-%m-%Y') between '$inwarddate' and '$inwarddate'");
                    }
                ])
                ->withCount([
                    'damage_product_detail as totalused' => function($fquery) use ($inwarddate)
                    {
                        $fquery->select(DB::raw('SUM(product_damage_qty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                        $fquery->with('damage_product')->whereHas('damage_product',function ($q) use ($inwarddate){
                            $q->where('damage_type_id','!=',1);
                            $q->whereRaw("STR_TO_DATE(damage_products.damage_date,'%d-%m-%Y') < '$inwarddate'");
                            $q->where('company_id',Auth::user()->company_id);
                        });
                    }
                ])
                ->withCount([
                    'damage_product_detail as currentused' => function($fquery) use ($inwarddate)
                    {
                        $fquery->select(DB::raw('SUM(product_damage_qty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                        $fquery->with('damage_product')->whereHas('damage_product',function ($q) use ($inwarddate){
                            $q->where('damage_type_id','!=',1);
                            $q->whereRaw("STR_TO_DATE(damage_products.damage_date,'%d-%m-%Y') between '$inwarddate' and '$inwarddate'");
                            $q->where('company_id',Auth::user()->company_id);
                        });

                    }
                ])
                ->withCount([
                    'damage_product_detail as totalddamage' => function($fquery) use ($inwarddate)
                    {
                        $fquery->select(DB::raw('SUM(product_damage_qty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                        $fquery->with('damage_product')->whereHas('damage_product',function ($q) use ($inwarddate){
                            $q->where('damage_type_id',1);
                            $q->whereRaw("STR_TO_DATE(damage_products.damage_date,'%d-%m-%Y') < '$inwarddate'");
                            $q->where('company_id',Auth::user()->company_id);
                        });

                    }
                ])
                ->withCount([
                    'damage_product_detail as currentddamage' => function($fquery) use ($inwarddate)
                    {
                        $fquery->select(DB::raw('SUM(product_damage_qty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                        $fquery->with('damage_product')->whereHas('damage_product',function ($q) use ($inwarddate){
                            $q->where('damage_type_id',1);
                            $q->whereRaw("STR_TO_DATE(damage_products.damage_date,'%d-%m-%Y') between '$inwarddate' and '$inwarddate'");
                            $q->where('company_id',Auth::user()->company_id);
                        });

                    }
                ])
                ->withCount([
                    'debit_product_detail as totalsuppreturn' => function($fquery) use ($inwarddate) {
                        $fquery->select(DB::raw('SUM(return_qty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                         $fquery->with('debit_note')->whereHas('debit_note',function ($q) use ($inwarddate){
                            $q->whereRaw("STR_TO_DATE(debit_notes.debit_date,'%d-%m-%Y') < '$inwarddate'");
                            $q->where('company_id',Auth::user()->company_id);
                        });
                    }
                ])
                ->withCount([
                    'debit_product_detail as currentsuppreturn' => function($fquery) use ($inwarddate) {
                        $fquery->select(DB::raw('SUM(return_qty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                         $fquery->with('debit_note')->whereHas('debit_note',function ($q) use ($inwarddate){
                            $q->whereRaw("STR_TO_DATE(debit_notes.debit_date,'%d-%m-%Y') between '$inwarddate' and '$inwarddate'");
                            $q->where('company_id',Auth::user()->company_id);
                        });
                    }
                ])
                ->withCount([
                    'consign_products_detail as totalconsign' => function($fquery) use ($inwarddate) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                         $fquery->with('consign_bill')->whereHas('consign_bill',function ($q) use ($inwarddate){
                            $q->whereRaw("STR_TO_DATE(consign_bills.bill_date,'%d-%m-%Y') < '$inwarddate'");
                            $q->where('company_id',Auth::user()->company_id);
                        });
                    }
                ])                
                ->withCount([
                    'consign_products_detail as currentconsign' => function($fquery) use ($inwarddate) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                         $fquery->with('consign_bill')->whereHas('consign_bill',function ($q) use ($inwarddate){
                            $q->whereRaw("STR_TO_DATE(consign_bills.bill_date,'%d-%m-%Y') between '$inwarddate' and '$inwarddate'");
                            $q->where('company_id',Auth::user()->company_id);
                        });
                    }
                ])
                ->withCount([
                    'sales_product_detail as totalconsignsold' => function($fquery) use ($inwarddate) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                        $fquery->whereNotNull('consign_products_detail_id');
                         $fquery->with('sales_bill')->whereHas('sales_bill',function ($q) use ($inwarddate){
                            $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') < '$inwarddate'");
                            $q->where('company_id',Auth::user()->company_id);
                        });
                    }
                ])
                ->withCount([
                    'sales_product_detail as currentconsignsold' => function($fquery) use ($inwarddate) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                        $fquery->whereNotNull('consign_products_detail_id');
                         $fquery->with('sales_bill')->whereHas('sales_bill',function ($q) use ($inwarddate){
                            $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$inwarddate' and '$inwarddate'");
                            $q->where('company_id',Auth::user()->company_id);
                        });
                    }
                ])
                ->withCount([
                    'stock_transfer_detail as currentstransfer' => function($fquery) use ($inwarddate) {
                        $fquery->select(DB::raw('SUM(product_qty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                         $fquery->with('stock_transfer')->whereHas('stock_transfer',function ($q) use ($inwarddate){
                            $q->whereRaw("STR_TO_DATE(stock_transfers.stock_transfer_date,'%d-%m-%Y') between '$inwarddate' and '$inwarddate'");
                            $q->whereNull('sales_bill_id');
                            $q->where('company_id',Auth::user()->company_id);
                        });
                    }
                ])
                ->withCount([
                    'stock_transfer_detail as totalstransfer' => function($fquery) use ($inwarddate) {
                        $fquery->select(DB::raw('SUM(product_qty)'));
                        $fquery->where('company_id',Auth::user()->company_id);
                         $fquery->with('stock_transfer')->whereHas('stock_transfer',function ($q) use ($inwarddate){
                            $q->whereRaw("STR_TO_DATE(stock_transfers.stock_transfer_date,'%d-%m-%Y') < '$inwarddate'");
                            $q->whereNull('sales_bill_id');
                            $q->where('company_id',Auth::user()->company_id);
                        });
                    }
                ])
                ->where('item_type', '!=', 2)
                ->with('product_price_master')
                ->whereHas('product_price_master',function ($q) {
                        $q->where('company_id',Auth::user()->company_id);
                 })
                 ->orderBy('product_id','DESC');





                $custom = collect();
                $data = $custom->merge($query->get());
                $product   =  $query->paginate(10);


                // echo '<pre>';
                // print_r($product);
                // exit;

                $product_features =  ProductFeatures::getproduct_feature('');
                 foreach($product as $pp=>$pval)
                 {

                        $ptotinwardqty          =   $pval['totalinwardqty'];
                        $ptotsoldqty            =   $pval['totalsoldqty'];
                        $ptotrestock            =   $pval['totalrestock'];
                        $ptotusedqty            =   $pval['totalused'];
                        $ptotdamageqty          =   $pval['totalddamage'];
                        $ptotsupprqty           =   $pval['totalsuppreturn'];
                        $pcurrinward            =   $pval['currentinward'];
                        $pcurrsold              =   $pval['currentsold'];
                        $pcurrrestock           =   $pval['currentrestock'];
                        $pcurrusedqty           =   $pval['currentused'];
                        $pcurrdamageqty         =   $pval['currentdamage'];
                        $pcurrddamageqty        =   $pval['currentddamage'];
                        $pcurrsupprqty          =   $pval['currentsuppreturn'];
                        $ptotconsignqty         =   $pval['totalconsign'];
                        $pcurrconsignqty        =   $pval['currentconsign'];
                        $pcurrconsignsold       =   $pval['currentconsignsold'];
                        $ptotconsignsold        =   $pval['totalconsignsold'];
                        $pcurrfranqty           =   $pval['currentfranchiseqty'];
                        $ptotfranqty            =   $pval['totalfranchiseqty'];
                        $pcurrstransfer         =   $pval['currentstransfer'];
                        $ptotstransfer          =   $pval['totalstransfer'];
                        $ppendingcurrconsignqty  =  $pval['currentconsign'] - $pval['currentconsignsold'];
                        $ppendingtotalconsignqty =  $pval['totalconsign'] - $pval['totalconsignsold'];

                         $ptotopening     =   $ptotinwardqty - $ptotsoldqty + $ptotrestock - $ptotusedqty - $ptotdamageqty - $ptotsupprqty - $ppendingtotalconsignqty-$ptotfranqty-$ptotstransfer;
                         $ptotstock       =   $ptotopening +$pcurrinward -$pcurrsold + $pcurrrestock-$pcurrusedqty - $pcurrddamageqty  - $pcurrsupprqty - $ppendingcurrconsignqty-$pcurrfranqty-$pcurrstransfer;

//////////////////LIFO Method To Calculate Average cost code is done here in Blade file because Instock come after subtraction and addition of Multiple variables thats why ////////////////////////////////////////////////////////////////////
                    if($ptotstock != ''  || $ptotstock !=0)
                    {

                         $inwarddetail    =  inward_product_detail::select('inward_product_detail_id',DB::raw('sum(product_qty+free_qty) as pending_return_qty'),'cost_rate')
                                        ->where('product_id',$pval['product_id'])
                                        ->where('company_id',Auth::user()->company_id)
                                        ->with('inward_stock')->whereHas('inward_stock',function ($q) use ($inwarddate){
                                            $q->whereRaw("STR_TO_DATE(inward_stocks.inward_date,'%d-%m-%Y') < '$inwarddate'");
                                            $q->where('company_id',Auth::user()->company_id);
                                        })
                                        ->orderBy('inward_product_detail_id','DESC')
                                        ->groupBy('inward_product_detail_id')->get();
                                        
                                       $ccount    =   0;  
                                       $icount    =   0;
                                       $pcount    =   0;
                                       $done      =   0;
                                       $firstout  =   0;
                                       $restqty   =   $ptotstock;
                                       $productcostprice  = 0;

                                    foreach($inwarddetail as $inwarddata)
                                       {
                                          //echo $inwarddata['pending_return_qty'];
                                            if($inwarddata['pending_return_qty'] >= $restqty && $firstout==0)
                                            {  
                                                  if($done == 0)
                                                  {
                                                         $productcostprice  +=  $inwarddata['cost_rate'] * $ptotstock;
                                                          $pcount++;
                                                          $done++;
                                                 }
                                           }
                                           else
                                           {
                                              if($pcount==0 && $done == 0 && $icount==0)
                                              {
                                                  
                                                 
                                                  if($restqty  > $inwarddata['pending_return_qty'])
                                                  {
                                                    
                                                      
                                                      $ccount       =   $restqty  - $inwarddata['pending_return_qty'];
                                                      $productcostprice  +=  $inwarddata['cost_rate'] * $inwarddata['pending_return_qty'];
                                                     
                                                  }
                                                  else
                                                  {
                                                   
                                                     
                                                      $ccount   =   $restqty  - $inwarddata['pending_return_qty'];
                                                      $productcostprice  +=  $inwarddata['cost_rate'] * $restqty;
                                                     
                                                  }


                                                   if($ccount > 0)
                                                    {
                                                       $firstout++;
                                                       $restqty   =   $restqty  - $inwarddata['pending_return_qty'];
                                                       //echo $restqty;

                                                       
                                                    }
                                                    if($ccount <= 0)
                                                    {
                                                      
                                                      $firstout++;
                                                       $icount++;
                                                         
                                                    }
                                                   
                                              }
                                           }

                                        }
                                               $averageproductcost               =   $productcostprice / $ptotstock;
                                               $pval['averageproductcost']       =   $productcostprice / $ptotstock;
                                               $pval['totalaverageproductcost']  =   $averageproductcost * $ptotstock;    
                                        }
                                        else
                                        {
                                                $pval['averageproductcost']       =   0;
                                                $pval['totalaverageproductcost']  =   0;  
                                        }
//////////////////End Calculate Average cost code is done here in Blade file because Instock come after subtraction and addition of Multiple variables thats why ////////////////////////////////////////////////////////////////////


                    
                  if(isset($pval['product_features_relationship']) && $pval['product_features_relationship'] != '')
                  {
                      foreach ($product_features AS $kk => $vv)
                      {
                          $html_id = $vv['html_id'];

                          if($pval['product_features_relationship'][$html_id] != '' && $pval['product_features_relationship'][$html_id] != NULL)
                          {
                              $nm =  product::feature_value($vv['product_features_id'],$pval['product_features_relationship'][$html_id]);
                              $pval[$html_id] =$nm;
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
                $currconsignsold=0;
                $totconsignsold=0;
                $pendingcurrconsignqty = 0;
                $pendingtotalconsignqty = 0;
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
                $currinward            +=   $ttotproduct['currentinward'];
                $currsold              +=   $ttotproduct['currentsold'];
                $currrestock           +=   $ttotproduct['currentrestock'];
                $currusedqty           +=   $ttotproduct['currentused'];
                $currdamageqty         +=   $ttotproduct['currentdamage'];
                $currddamageqty        +=   $ttotproduct['currentddamage'];
                $currsupprqty          +=   $ttotproduct['currentsuppreturn'];
                $currconsignqty        +=   $ttotproduct['currentconsign'];
                $totconsignqty         +=   $ttotproduct['totalconsign'];
                $currconsignsold       +=   $ttotproduct['currentconsignsold'];
                $totconsignsold        +=   $ttotproduct['totalconsignsold'];
                $currfranqty           +=   $ttotproduct['currentfranchiseqty'];
                $totfranqty            +=   $ttotproduct['totalfranchiseqty'];
                $currstransfer         +=   $ttotproduct['currentstransfer'];
                $totstransfer          +=   $ttotproduct['totalstransfer'];
                $pendingcurrconsignqty +=   $ttotproduct['currentconsign'] - $ttotproduct['currentconsignsold'];
                $pendingtotalconsignqty +=   $ttotproduct['totalconsign'] - $ttotproduct['totalconsignsold'];
                


            }

           
           
               $totopening     =   $totinwardqty - $totsoldqty + $totrestock - $totusedqty - $totdamageqty - $totsupprqty  -$pendingtotalconsignqty-$totfranqty-$totstransfer;
               $totstock       =   $totopening +$currinward -$currsold + $currrestock-$currusedqty - $currddamageqty  - $currsupprqty -$currfranqty-$currstransfer - $pendingcurrconsignqty;
               $ttotaldamage   =   $currdamageqty + $currddamageqty;

    		 return view('salesreport::stock_report',compact('product','totopening','totstock','currinward','currsold','currrestock','currusedqty','ttotaldamage','currsupprqty','count','totconsignqty','currconsignqty','currfranqty','currstransfer','pendingcurrconsignqty','get_store','companyname'));
    }

    function datewise_stock_detail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        if($request->ajax())
        {

             $get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();

            $data            =      $request->all();
            $sort_by   = $data['sortby'];
            $sort_type = $data['sorttype'];
            $query = isset($data['query']) ? $data['query']  : '';
            //print_r(Auth::user()->company_id);

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


            if($query['from_date']!='')
            {
                 $inwardstartdate            =      date("Y-m-d",strtotime($query['from_date']));
                 $inwardenddate              =      date("Y-m-d",strtotime($query['to_date']));
                 $salesstartdate             =      $query['from_date'];
                 $salesenddate               =      $query['to_date'];
            }
            else
            {
                $inwardstartdate            =      date("Y-m-d");
                $inwardenddate              =      date("Y-m-d");
                $salesstartdate             =      date("d-m-Y");
                $salesenddate               =      date("d-m-Y");
            }
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



        $pquery  = product::select('product_name','product_system_barcode','supplier_barcode','product_id','sku_code','uqc_id','hsn_sac_code','product_code')
                ->where('deleted_at', '=', NULL)
                ->withCount([
                    'inward_product_detail as totalinwardqty' => function($fquery) use ($inwardstartdate,$company_id) {
                        $fquery->select(DB::raw('SUM(product_qty+free_qty)'));
                        $fquery->where('company_id',$company_id);
                        $fquery->with('inward_stock')->whereHas('inward_stock',function ($q) use ($inwardstartdate,$company_id){
                            $q->whereRaw("STR_TO_DATE(inward_stocks.inward_date,'%d-%m-%Y') < '$inwardstartdate'");
                            $q->where('company_id',$company_id);
                        });

                    }
                ])
                ->withCount([
                    'sales_product_detail as totalsoldqty' => function($fquery) use ($inwardstartdate,$company_id) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',$company_id);
                        $fquery->where('product_type',1);
                        $fquery->with('sales_bill')->whereHas('sales_bill',function ($q) use ($inwardstartdate,$company_id){
                            $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') < '$inwardstartdate'");
                            $q->where('sales_type',1);
                            $q->where('company_id',$company_id);
                        });
                    }
                ])
                ->withCount([
                    'sales_product_detail as totalfranchiseqty' => function($fquery) use ($inwardstartdate,$company_id) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',$company_id);
                        $fquery->where('product_type',1);
                        $fquery->with('sales_bill')->whereHas('sales_bill',function ($q) use ($inwardstartdate,$company_id){
                            $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') < '$inwardstartdate'");
                            $q->where('sales_type',2);
                            $q->where('company_id',$company_id);
                        });
                    }
                ])
                ->withCount([
                    'inward_product_detail as currentinward' => function($fquery) use ($inwardstartdate,$inwardenddate,$company_id) {
                        $fquery->select(DB::raw('SUM(product_qty+free_qty)'));
                        $fquery->where('company_id',$company_id);
                         $fquery->with('inward_stock')->whereHas('inward_stock',function ($q) use ($inwardstartdate,$inwardenddate,$company_id) {
                            $q->whereRaw("STR_TO_DATE(inward_stocks.inward_date,'%d-%m-%Y') between '$inwardstartdate' and '$inwardenddate'");
                            $q->where('company_id',$company_id);
                        });

                    }
                ])
                ->withCount([
                    'sales_product_detail as currentsold' => function($fquery) use ($inwardstartdate,$inwardenddate,$company_id) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',$company_id);
                        $fquery->where('product_type',1);
                         $fquery->with('sales_bill')->whereHas('sales_bill',function ($q) use ($inwardstartdate,$inwardenddate,$company_id){
                            $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$inwardstartdate' and '$inwardenddate'");
                            $q->where('sales_type',1);
                            $q->where('company_id',$company_id);
                        });

                    }
                ])
                ->withCount([
                    'sales_product_detail as currentfranchiseqty' => function($fquery) use ($inwardstartdate,$inwardenddate,$company_id) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',$company_id);
                        $fquery->where('product_type',1);
                        $fquery->with('sales_bill')->whereHas('sales_bill',function ($q) use ($inwardstartdate,$inwardenddate,$company_id) {
                            $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$inwardstartdate' and '$inwardenddate'");
                            $q->where('sales_type',2);
                            $q->where('company_id',$company_id);
                        });
                    }
                ])
                ->withCount([
                    'price_master as averagemrp' => function($fquery) use ($inwardstartdate,$company_id) {
                        $fquery->select(DB::raw('SUM(product_qty *offer_price)/SUM(product_qty)'));
                        $fquery->where('company_id',$company_id);
                        $fquery->groupBy('product_id');
                    }
                ])
                ->withCount([
                    'returnbill_product as totalreturn' => function($fquery) use ($inwardstartdate,$company_id) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',$company_id);
                        $fquery->whereRaw("STR_TO_DATE(return_date,'%d-%m-%Y') < '$inwardstartdate'");
                    }
                ])
                ->withCount([
                    'returnbill_product as currentreturn' => function($fquery) use ($inwardstartdate,$inwardenddate,$company_id) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',$company_id);
                        $fquery->whereRaw("STR_TO_DATE(return_date,'%d-%m-%Y') between '$inwardstartdate' and '$inwardenddate'");
                    }
                ])
                ->withCount([
                    'returnbill_product as totalrestock' => function($fquery) use ($inwardstartdate,$company_id) {
                        $fquery->select(DB::raw('SUM(restockqty)'));
                        $fquery->where('company_id',$company_id);
                        $fquery->whereRaw("STR_TO_DATE(return_date,'%d-%m-%Y') < '$inwardstartdate'");
                    }
                ])
                ->withCount([
                    'returnbill_product as totaldamage' => function($fquery) use ($inwardstartdate,$company_id) {
                        $fquery->select(DB::raw('SUM(damageqty)'));
                        $fquery->where('company_id',$company_id);
                        $fquery->whereRaw("STR_TO_DATE(return_date,'%d-%m-%Y') < '$inwardstartdate'");
                    }
                ])
                ->withCount([
                    'returnbill_product as currentrestock' => function($fquery) use ($inwardstartdate,$inwardenddate,$company_id) {
                        $fquery->select(DB::raw('SUM(restockqty)'));
                        $fquery->where('company_id',$company_id);
                        $fquery->whereRaw("STR_TO_DATE(return_date,'%d-%m-%Y') between '$inwardstartdate' and '$inwardenddate'");
                    }
                ])
                ->withCount([
                    'returnbill_product as currentdamage' => function($fquery) use ($inwardstartdate,$inwardenddate,$company_id) {
                        $fquery->select(DB::raw('SUM(damageqty)'));
                        $fquery->where('company_id',$company_id);
                        $fquery->whereRaw("STR_TO_DATE(return_date,'%d-%m-%Y') between '$inwardstartdate' and '$inwardenddate'");
                    }
                ])
                ->withCount([
                    'damage_product_detail as totalused' => function($fquery) use ($inwardstartdate,$company_id)
                    {
                        $fquery->select(DB::raw('SUM(product_damage_qty)'));
                        $fquery->where('company_id',$company_id);
                        $fquery->with('damage_product')->whereHas('damage_product',function ($q) use ($inwardstartdate,$company_id){
                            $q->where('damage_type_id','!=',1);
                            $q->whereRaw("STR_TO_DATE(damage_products.damage_date,'%d-%m-%Y') < '$inwardstartdate'");
                            $q->where('company_id',$company_id);
                        });
                    }
                ])
                ->withCount([
                    'damage_product_detail as currentused' => function($fquery) use ($inwardstartdate,$inwardenddate,$company_id)
                    {
                        $fquery->select(DB::raw('SUM(product_damage_qty)'));
                        $fquery->where('company_id',$company_id);
                        $fquery->with('damage_product')->whereHas('damage_product',function ($q) use ($inwardstartdate,$inwardenddate,$company_id){
                            $q->where('damage_type_id','!=',1);
                            $q->whereRaw("STR_TO_DATE(damage_products.damage_date,'%d-%m-%Y') between '$inwardstartdate' and '$inwardenddate'");
                            $q->where('company_id',$company_id);
                        });

                    }
                ])
                ->withCount([
                    'damage_product_detail as totalddamage' => function($fquery) use ($inwardstartdate,$company_id)
                    {
                        $fquery->select(DB::raw('SUM(product_damage_qty)'));
                        $fquery->where('company_id',$company_id);
                        $fquery->with('damage_product')->whereHas('damage_product',function ($q) use ($inwardstartdate,$company_id){
                            $q->where('damage_type_id',1);
                            $q->whereRaw("STR_TO_DATE(damage_products.damage_date,'%d-%m-%Y') < '$inwardstartdate'");
                            $q->where('company_id',$company_id);
                        });

                    }
                ])
                ->withCount([
                    'damage_product_detail as currentddamage' => function($fquery) use ($inwardstartdate,$inwardenddate,$company_id)
                    {
                        $fquery->select(DB::raw('SUM(product_damage_qty)'));
                        $fquery->where('company_id',$company_id);
                        $fquery->with('damage_product')->whereHas('damage_product',function ($q) use ($inwardstartdate,$inwardenddate,$company_id){
                            $q->where('damage_type_id',1);
                            $q->whereRaw("STR_TO_DATE(damage_products.damage_date,'%d-%m-%Y') between '$inwardstartdate' and '$inwardenddate'");
                            $q->where('company_id',$company_id);
                        });

                    }
                ])
                ->withCount([
                    'debit_product_detail as totalsuppreturn' => function($fquery) use ($inwardstartdate,$company_id) {
                        $fquery->select(DB::raw('SUM(return_qty)'));
                        $fquery->where('company_id',$company_id);
                         $fquery->with('debit_note')->whereHas('debit_note',function ($q) use ($inwardstartdate,$company_id){
                            $q->whereRaw("STR_TO_DATE(debit_notes.debit_date,'%d-%m-%Y') < '$inwardstartdate'");
                            $q->where('company_id',$company_id);
                        });
                    }
                ])
                ->withCount([
                    'debit_product_detail as currentsuppreturn' => function($fquery) use ($inwardstartdate,$inwardenddate,$company_id) {
                        $fquery->select(DB::raw('SUM(return_qty)'));
                        $fquery->where('company_id',$company_id);
                         $fquery->with('debit_note')->whereHas('debit_note',function ($q) use ($inwardstartdate,$inwardenddate,$company_id){
                            $q->whereRaw("STR_TO_DATE(debit_notes.debit_date,'%d-%m-%Y') between '$inwardstartdate' and '$inwardenddate'");
                            $q->where('company_id',$company_id);
                        });
                    }
                ])
                ->withCount([
                    'consign_products_detail as totalconsign' => function($fquery) use ($inwardstartdate,$company_id) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',$company_id);
                         $fquery->with('consign_bill')->whereHas('consign_bill',function ($q) use ($inwardstartdate,$company_id){
                            $q->whereRaw("STR_TO_DATE(consign_bills.bill_date,'%d-%m-%Y') < '$inwardstartdate'");
                            $q->where('company_id',$company_id);
                        });
                    }
                ])
                ->withCount([
                    'consign_products_detail as currentconsign' => function($fquery) use ($inwardstartdate,$inwardenddate,$company_id) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',$company_id);
                         $fquery->with('consign_bill')->whereHas('consign_bill',function ($q) use ($inwardstartdate,$inwardenddate,$company_id){
                            $q->whereRaw("STR_TO_DATE(consign_bills.bill_date,'%d-%m-%Y') between '$inwardstartdate' and '$inwardenddate'");
                            $q->where('company_id',$company_id);
                        });
                    }
                ])
                ->withCount([
                    'sales_product_detail as totalconsignsold' => function($fquery) use ($inwardstartdate,$inwardenddate,$company_id) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',$company_id);
                        $fquery->whereNotNull('consign_products_detail_id');
                         $fquery->with('sales_bill')->whereHas('sales_bill',function ($q) use ($inwardstartdate,$inwardenddate,$company_id){
                            $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') < '$inwardstartdate'");
                            $q->where('company_id',$company_id);
                        });
                    }
                ])
                ->withCount([
                    'sales_product_detail as currentconsignsold' => function($fquery) use ($inwardstartdate,$inwardenddate,$company_id) {
                        $fquery->select(DB::raw('SUM(qty)'));
                        $fquery->where('company_id',$company_id);
                        $fquery->whereNotNull('consign_products_detail_id');
                         $fquery->with('sales_bill')->whereHas('sales_bill',function ($q) use ($inwardstartdate,$inwardenddate,$company_id){
                            $q->whereRaw("STR_TO_DATE(sales_bills.bill_date,'%d-%m-%Y') between '$inwardstartdate' and '$inwardenddate'");
                            $q->where('company_id',$company_id);
                        });
                    }
                ])
                ->withCount([
                    'stock_transfer_detail as currentstransfer' => function($fquery) use ($inwardstartdate,$inwardenddate,$company_id) {
                        $fquery->select(DB::raw('SUM(product_qty)'));
                        $fquery->where('company_id',$company_id);
                         $fquery->with('stock_transfer')->whereHas('stock_transfer',function ($q) use ($inwardstartdate,$inwardenddate,$company_id) {
                            $q->whereRaw("STR_TO_DATE(stock_transfers.stock_transfer_date,'%d-%m-%Y') between '$inwardstartdate' and '$inwardenddate'");
                            $q->whereNull('sales_bill_id');
                            $q->where('company_id',$company_id);
                        });
                    }
                ])
                ->withCount([
                    'stock_transfer_detail as totalstransfer' => function($fquery) use ($inwardstartdate,$company_id) {
                        $fquery->select(DB::raw('SUM(product_qty)'));
                        $fquery->where('company_id',$company_id);
                         $fquery->with('stock_transfer')->whereHas('stock_transfer',function ($q) use ($inwardstartdate,$company_id){
                            $q->whereRaw("STR_TO_DATE(stock_transfers.stock_transfer_date,'%d-%m-%Y') < '$inwardstartdate'");
                            $q->whereNull('sales_bill_id');
                            $q->where('company_id',$company_id);

                        });
                    }
                ])
                ->where('item_type', '!=', 2)
                ->with('product_price_master')
                ->whereHas('product_price_master',function ($q) use($company_id){
                        $q->where('company_id',$company_id);
                 });
               // echo '<pre>';
               // print_r($pquery);
               // exit;



            if(isset($query) && $query != '' && $query['productsearch'] != '')
            {

                 $prquery = product::select('product_id')
                     ->where('company_id',Auth::user()->company_id)
                     ->where('deleted_at','=',NULL);

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
                                 ->orWhere('product_system_barcode', 'LIKE', "%$prod_barcode%")
                                 ->orWhere('supplier_barcode', 'LIKE', "%$prod_barcode%");
                    }

                    $prodresult  =  $prquery->get();


                     $pquery->whereIn('product_id',$prodresult);
            }
            if(isset($query) && $query != '' && $query['productcode'] != '')
            {
               
                $pquery->where('product_code',$query['productcode']);
            }

            if(isset($dynamic_search) && $dynamic_search !='' &&  !empty($dynamic_search))
            {

                $pquery =  $pquery->with('product_features_relationship')
                    ->whereHas('product_features_relationship',function ($q) use($dynamic_search)
                    {
                     foreach($dynamic_search AS $k=>$v)
                      {
                          $q->where(DB::raw($k),$v);
                      }
                    });
            }





                $custom = collect();
                $data = $custom->merge($pquery->get());

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
                $currconsignsold=0;
                $totconsignsold=0;
                $pendingcurrconsignqty = 0;
                $pendingtotalconsignqty = 0;
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
                $currinward            +=   $ttotproduct['currentinward'];
                $currsold              +=   $ttotproduct['currentsold'];
                $currrestock           +=   $ttotproduct['currentrestock'];
                $currusedqty           +=   $ttotproduct['currentused'];
                $currdamageqty         +=   $ttotproduct['currentdamage'];
                $currddamageqty        +=   $ttotproduct['currentddamage'];
                $currsupprqty          +=   $ttotproduct['currentsuppreturn'];
                $currconsignqty        +=   $ttotproduct['currentconsign'];
                $totconsignqty         +=   $ttotproduct['totalconsign'];
                $currconsignsold       +=   $ttotproduct['currentconsignsold'];
                $totconsignsold        +=   $ttotproduct['totalconsignsold'];
                $currfranqty           +=   $ttotproduct['currentfranchiseqty'];
                $totfranqty            +=   $ttotproduct['totalfranchiseqty'];
                $currstransfer         +=   $ttotproduct['currentstransfer'];
                $totstransfer          +=   $ttotproduct['totalstransfer'];
                $pendingcurrconsignqty +=   $ttotproduct['currentconsign'] - $ttotproduct['currentconsignsold'];
                $pendingtotalconsignqty +=  $ttotproduct['totalconsign'] - $ttotproduct['totalconsignsold'];

            }

               $totopening     =   $totinwardqty - $totsoldqty + $totrestock - $totusedqty - $totdamageqty - $totsupprqty  -$pendingtotalconsignqty-$totfranqty - $totstransfer;
               $totstock       =   $totopening +$currinward -$currsold + $currrestock-$currusedqty - $currddamageqty  - $currsupprqty  - $currfranqty - $currstransfer -$pendingcurrconsignqty;
               $ttotaldamage   =   $currdamageqty + $currddamageqty;



            $product = $pquery->where('item_type', '!=', 2)->orderBy($sort_by, $sort_type)
                          ->paginate(10);

              $inwarddate   =  $inwardenddate;   
              $product_features =  ProductFeatures::getproduct_feature('');
                 foreach($product as $pp=>$pval)
                 {

                        $ptotinwardqty          =   $pval['totalinwardqty'];
                        $ptotsoldqty            =   $pval['totalsoldqty'];
                        $ptotrestock            =   $pval['totalrestock'];
                        $ptotusedqty            =   $pval['totalused'];
                        $ptotdamageqty          =   $pval['totalddamage'];
                        $ptotsupprqty           =   $pval['totalsuppreturn'];
                        $pcurrinward            =   $pval['currentinward'];
                        $pcurrsold              =   $pval['currentsold'];
                        $pcurrrestock           =   $pval['currentrestock'];
                        $pcurrusedqty           =   $pval['currentused'];
                        $pcurrdamageqty         =   $pval['currentdamage'];
                        $pcurrddamageqty        =   $pval['currentddamage'];
                        $pcurrsupprqty          =   $pval['currentsuppreturn'];
                        $ptotconsignqty         =   $pval['totalconsign'];
                        $pcurrconsignqty        =   $pval['currentconsign'];
                        $pcurrconsignsold       =   $pval['currentconsignsold'];
                        $ptotconsignsold        =   $pval['totalconsignsold'];
                        $pcurrfranqty           =   $pval['currentfranchiseqty'];
                        $ptotfranqty            =   $pval['totalfranchiseqty'];
                        $pcurrstransfer         =   $pval['currentstransfer'];
                        $ptotstransfer          =   $pval['totalstransfer'];
                        $ppendingcurrconsignqty  =  $pval['currentconsign'] - $pval['currentconsignsold'];
                        $ppendingtotalconsignqty =  $pval['totalconsign'] - $pval['totalconsignsold'];

                         $ptotopening     =   $ptotinwardqty - $ptotsoldqty + $ptotrestock - $ptotusedqty - $ptotdamageqty - $ptotsupprqty - $ppendingtotalconsignqty-$ptotfranqty-$ptotstransfer;
                         $ptotstock       =   $ptotopening +$pcurrinward -$pcurrsold + $pcurrrestock-$pcurrusedqty - $pcurrddamageqty  - $pcurrsupprqty - $ppendingcurrconsignqty-$pcurrfranqty-$pcurrstransfer;

//////////////////LIFO Method To Calculate Average cost code is done here in Blade file because Instock come after subtraction and addition of Multiple variables thats why ////////////////////////////////////////////////////////////////////
                    if($ptotstock != ''  || $ptotstock !=0)
                    {

                         $inwarddetail    =  inward_product_detail::select('inward_product_detail_id',DB::raw('sum(product_qty+free_qty) as pending_return_qty'),'cost_rate')
                                        ->where('product_id',$pval['product_id'])
                                        ->where('company_id',$company_id)
                                        ->with('inward_stock')->whereHas('inward_stock',function ($q) use ($inwarddate,$company_id){
                                            $q->whereRaw("STR_TO_DATE(inward_stocks.inward_date,'%d-%m-%Y') < '$inwarddate'");
                                            $q->where('company_id',$company_id);
                                        })
                                        ->orderBy('inward_product_detail_id','DESC')
                                        ->groupBy('inward_product_detail_id')->get();
                                        
                                       $ccount    =   0;  
                                       $icount    =   0;
                                       $pcount    =   0;
                                       $done      =   0;
                                       $firstout  =   0;
                                       $restqty   =   $ptotstock;
                                       $productcostprice  = 0;

                                    foreach($inwarddetail as $inwarddata)
                                       {
                                          //echo $inwarddata['pending_return_qty'];
                                            if($inwarddata['pending_return_qty'] >= $restqty && $firstout==0)
                                            {  
                                                  if($done == 0)
                                                  {
                                                         $productcostprice  +=  $inwarddata['cost_rate'] * $ptotstock;
                                                          $pcount++;
                                                          $done++;
                                                 }
                                           }
                                           else
                                           {
                                              if($pcount==0 && $done == 0 && $icount==0)
                                              {
                                                  
                                                 
                                                  if($restqty  > $inwarddata['pending_return_qty'])
                                                  {
                                                    
                                                      
                                                      $ccount       =   $restqty  - $inwarddata['pending_return_qty'];
                                                      $productcostprice  +=  $inwarddata['cost_rate'] * $inwarddata['pending_return_qty'];
                                                     
                                                  }
                                                  else
                                                  {
                                                   
                                                     
                                                      $ccount   =   $restqty  - $inwarddata['pending_return_qty'];
                                                      $productcostprice  +=  $inwarddata['cost_rate'] * $restqty;
                                                     
                                                  }


                                                   if($ccount > 0)
                                                    {
                                                       $firstout++;
                                                       $restqty   =   $restqty  - $inwarddata['pending_return_qty'];
                                                       //echo $restqty;

                                                       
                                                    }
                                                    if($ccount <= 0)
                                                    {
                                                      
                                                      $firstout++;
                                                       $icount++;
                                                         
                                                    }
                                                   
                                              }
                                           }

                                        }
                                               $averageproductcost               =   $productcostprice / $ptotstock;
                                               $pval['averageproductcost']       =   $productcostprice / $ptotstock;
                                               $pval['totalaverageproductcost']  =   $averageproductcost * $ptotstock;    
                                        }
                                        else
                                        {
                                                $pval['averageproductcost']       =   0;
                                                $pval['totalaverageproductcost']  =   0;  
                                        }
//////////////////End Calculate Average cost code is done here in Blade file because Instock come after subtraction and addition of Multiple variables thats why ////////////////////////////////////////////////////////////////////

                  if(isset($pval['product_features_relationship']) && $pval['product_features_relationship'] != '')
                  {
                      foreach ($product_features AS $kk => $vv)
                      {
                          $html_id = $vv['html_id'];

                          if($pval['product_features_relationship'][$html_id] != '' && $pval['product_features_relationship'][$html_id] != NULL)
                          {
                              $nm =  product::feature_value($vv['product_features_id'],$pval['product_features_relationship'][$html_id]);
                              $pval[$html_id] =$nm;
                          }
                      }
                  }
                }


              //  print_r($product);

             
            return view('salesreport::view_stockreport_data',compact('product','totopening','totstock','currinward','currsold','currrestock','currusedqty','ttotaldamage','currsupprqty','currconsignqty','count','currfranqty','currstransfer','pendingcurrconsignqty','inwarddate','get_store','companyname'));
        }


    }

    public function export_stockreport_details(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $query = isset($request['query']) ? $request['query']  : '';
        $dynamic_query = isset($request['dynamic_query']) ? $request['dynamic_query']  : '';
        
        return Excel::download(new stockreport_export($query,$dynamic_query), 'StockReport-Export.xlsx');

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


}
