<?php

namespace Retailcore\SalesReport\Models;

use Retailcore\Products\Models\product\product;
use Retailcore\Products\Models\product\category;
use Retailcore\Products\Models\product\brand;
use Retailcore\Store_Profile\Models\store_profile\company_relationship_tree;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use App\company;
use Retailcore\Products\Models\product\ProductFeatures;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Illuminate\Support\Facades\DB;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class stockreport_export implements FromQuery, WithHeadings, WithMapping
{

    //use Exportable;
    
    public $query = '';
    public $dynamic_query = '';
  
    public function __construct($query,$dynamic_query){

        $this->query = $query;
        $this->dynamic_query = $dynamic_query;

        $this->product_features = ProductFeatures::getproduct_feature('');

        $this->page_url = ProductFeatures::get_current_page_url();

        $this->show_dynamic_feature = array();
        $this->get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();
    }
   


    public function headings(): array
    {
         
        $state_id                =      company_profile::select('bill_calculation')->where('company_id',Auth::user()->company_id)->get();
        $bill_calculation_case   = $state_id[0]['bill_calculation'];
        $stock_report_header = [];
        if(sizeof($this->get_store)!=0)
         {
            $stock_report_header[] = 'Location';
         }
        $stock_report_header[] = 'Barcode';
        $stock_report_header[] = 'Product Name';
        $stock_report_header[] = 'HSN';
        $stock_report_header[] = 'Product Code';
         $dynamic_header = '';

            if (isset($this->product_features) && $this->product_features != '' && !empty($this->product_features))
            {
                foreach ($this->product_features AS $feature_key => $feature_value)
                {
                    if ($feature_value['show_feature_url'] != '' && $feature_value['show_feature_url'] != 'NULL' && $feature_value['show_feature_url'] != null)
                    {
                        $search =  $this->page_url;
                        if (strstr($feature_value['show_feature_url'],$search))
                        {
                            $this->show_dynamic_feature[$feature_value['html_id']] = $feature_value['product_features_id'];
                            $dynamic_header .= $stock_report_header[] = $feature_value['product_features_name'];
                        }
                    }
                }
            }

        $dynamic_header;
        $stock_report_header[] = 'UQC';
        if($bill_calculation_case ==1)
        {
             $stock_report_header[] = 'MRP';
             $stock_report_header[] = 'Cost Price';
        }
       
        $stock_report_header[] = 'Opening';
        $stock_report_header[] = 'Inward(+)';
        $stock_report_header[] = 'Sold(-)';
        $stock_report_header[] = 'Franchise Qty(-)';
        $stock_report_header[] = 'Stock Transfer(-)';
        $stock_report_header[] = 'Return';
        $stock_report_header[] = 'Restock(+)';
        $stock_report_header[] = 'Damage(-)';
        $stock_report_header[] = 'Used/Other(-)';
        $stock_report_header[] = 'Supplier Return(-)';
        $stock_report_header[] = 'Pending Consignment(-)';
        $stock_report_header[] = 'InStock';
        if($bill_calculation_case ==1)
        {
        $stock_report_header[] = 'Total MRP Value';
        $stock_report_header[] = 'Total Cost Value';
        }

        return $stock_report_header;
       
    }



public function map($product): array
{

 


             if(isset($this->query) && $this->query != '' && isset($this->query['store_name']) && $this->query['store_name'] != '')
            {
                 $company_id      =   $this->query['store_name'];
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
            
        $state_id  =  company_profile::select('decimal_points','bill_calculation')->where('company_id',Auth::user()->company_id)->get();


        $decimal_points  = $state_id[0]['decimal_points'];
        $bill_calculation_case   = $state_id[0]['bill_calculation'];

        $count = '';

        $opening   =   $product['totalinwardqty'] - $product['totalsoldqty'] + $product['totalrestock'] - $product['totalused'] - $product['totalddamage'] - $product['totalsuppreturn']+$product['totalconsign']-$product['totalconsignsold'] - $product['totalfranchiseqty']-$product['totalstransfer'];
        $stock     =   $opening +$product['currentinward'] -$product['currentsold'] + $product['currentrestock']-$product['currentused'] - $product['currentddamage']  - $product['currentsuppreturn'] + $product['currentconsign']- $product['currentconsignsold'] - $product['currentfranchiseqty'] - $product['currentstransfer'];
       

        $totaldamage  =  $product['currentdamage'] +  $product['currentddamage'];




        if($product->averagemrp !='')
        {
            $averagemrp   = $product->averagemrp;
        }
        else
        {
            $averagemrp    =  $product->offer_price != '' ?$product->offer_price : 0;
        }
        $totalmrpvalue  =  $averagemrp * $stock;
        if($product->supplier_barcode!='' && $product->supplier_barcode!=NULL)
        {
            $barcode  =   $product->supplier_barcode;

        }
        else
        {
             $barcode  =   $product->product_system_barcode;
        }

        $uqc_name = '';
       
        $uqc_name = '';
        if($product['uqc_id'] != '' && $product['uqc_id'] != null && $product['uqc_id'] != 0)
        {
            $uqc_name = $product['uqc']['uqc_shortname'];
        }

        

           $feature_show_val = array();


        if($this->show_dynamic_feature != '')
        {
            foreach($this->show_dynamic_feature AS $fea_key=>$fea_val)
            {
                $feature_data_id = $product['product_features_relationship'][$fea_key];

                if($feature_data_id != '')
                {
                    $data_feature = product::feature_value($fea_val,$feature_data_id);

                    $feature_show_val[] = $data_feature;
                }
                else
                {
                    $feature_show_val[] = 'no_val';

                }
            }
        }
if($this->query['to_date']=='')
{
     $inwarddate = date("Y-m-d");
}
else
{
    $inwarddate = date("Y-m-d",strtotime($this->query['to_date']));
}

  ////////////////////////////////LIFO Method////////////////////////////////////////////////////////////////
if($stock != ''  || $stock !=0)
{


$inwarddetail    =  inward_product_detail::select('inward_product_detail_id',DB::raw('sum(product_qty+free_qty) as pending_return_qty'),'cost_rate')
                                        ->where('product_id',$product['product_id'])
                                        ->where('company_id',Auth::user()->company_id)
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
                       $restqty   =   $stock;
                       $productcostprice  = 0;

                    foreach($inwarddetail as $inwarddata)
                       {
                          //echo $inwarddata['pending_return_qty'];
                            if($inwarddata['pending_return_qty'] >= $restqty && $firstout==0)
                            {  
                                  if($done == 0)
                                  {
                                         $productcostprice  +=  $inwarddata['cost_rate'] * $stock;
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
        $averageproductcost   =   $productcostprice / $stock;
        $totalaverageproductcost  =  $averageproductcost * $stock;    
}
else
{
        $averageproductcost   =   0;
        $totalaverageproductcost  =  0;
}
///////////////////////////End LIFO Method///////////////////////////////////////////////      
            
        $rows    = [];
        if(sizeof($this->get_store)!=0)
         {
            $rows[] = $companyname;
         }
           $rows[] = $barcode;
           $rows[] = $product->product_name;
           $rows[] = $product->hsn_sac_code!=0?$product->hsn_sac_code:'';
           $rows[] = $product->product_code;
            if($feature_show_val != '')
            {
                foreach($feature_show_val AS $kk=>$vv)
                {
                    $dynamic_value = $vv;
                    if($vv == 'no_val')
                    {
                        $dynamic_value = '';
                    }

                    $rows[] = $dynamic_value;
                }
            }

           $ppendingconsignqty  =  $product->currentconsign - $product->currentconsignsold;

           $rows[] = $uqc_name;
           if($bill_calculation_case ==1)
           {
           $rows[] = $averagemrp != '' ?round($averagemrp,$decimal_points) : '0';
           $rows[] = $averageproductcost != '' ?round($averageproductcost,$decimal_points) : '0';
           }
           $rows[] = $opening != '' ?$opening : '0';          
           $rows[] = $product->currentinward != '' ?$product->currentinward : '0';
           $rows[] = $product->currentsold != '' ?$product->currentsold : '0';
           $rows[] = $product->currentfranchiseqty != '' ?$product->currentfranchiseqty : '0';
           $rows[] = $product->currentstransfer != '' ?$product->currentstransfer : '0';
           $rows[] = $product->currentreturn != '' ?$product->currentreturn : '0';
           $rows[] = $product->currentrestock != '' ?$product->currentrestock : '0';
           $rows[] = $totaldamage != '' ?$totaldamage : '0';
           $rows[] = $product->currentused != '' ?$product->currentused : '0';
           $rows[] = $product->currentsuppreturn != '' ?$product->currentsuppreturn : '0';           
           $rows[] = $ppendingconsignqty != '' ?$ppendingconsignqty : '0';
           $rows[] = $stock != '' ?$stock : '0'; 
           if($bill_calculation_case ==1)
           {
           $rows[] = $totalmrpvalue != '' ?round($totalmrpvalue,$decimal_points) : '0';  
           $rows[] = $totalaverageproductcost != '' ?round($totalaverageproductcost,$decimal_points) : '0';  
           }



        return $rows;
        
    }


    public function query()
    {

       
    

             if(isset($this->query) && $this->query != '' && $this->query['from_date'] != '')
            {
                 $inwardstartdate            =      date("Y-m-d",strtotime($this->query['from_date']));
                 $inwardenddate              =      date("Y-m-d",strtotime($this->query['to_date']));
                 $salesstartdate             =      $this->query['from_date'];
                 $salesenddate               =      $this->query['to_date'];
            }
            else
            {
                $inwardstartdate            =      date("Y-m-d");
                $inwardenddate              =      date("Y-m-d");
                $salesstartdate             =      date("d-m-Y");
                $salesenddate               =      date("d-m-Y");
            }

            if(isset($this->query) && $this->query != '' && isset($this->query['store_name']) && $this->query['store_name'] != '')
            {
                 $company_id      =   $this->query['store_name']; 
            }
            else
            {
                 $company_id      =   Auth::user()->company_id;
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
                ->whereHas('product_price_master',function ($q) use($company_id) {
                        $q->where('company_id',$company_id);
                 });




         if(isset($this->query) && $this->query != '' && $this->query['productsearch'] != '')
        {
            if(strpos($this->query['productsearch'], '_') !== false)
            {
                $prodname    =   explode('_',$this->query['productsearch']);
                $prod_barcode =  $prodname[0];
                $prod_name     =  $prodname[1];
            }
            else
            {
                $prod_barcode   =   $this->query['productsearch'];
                $prod_name      =   $this->query['productsearch'];
            }
            $pquery->where('product_name', 'like', '%'.$prod_name.'%');
            $pquery->orWhere('product_system_barcode', 'like', '%'.$prod_barcode.'%');
            $pquery->orWhere('supplier_barcode', 'like', '%'.$prod_barcode.'%');
        }
        if(isset($this->query) && $this->query != '' && $this->query['productcode'] != '')
        {
           
            $pquery->where('product_code',$this->query['productcode']);
        }
        if(isset($this->dynamic_query) && $this->dynamic_query !='' && !empty($dynamic_search))
        {

            $dynamic_query = $this->dynamic_query;
             $pquery->with('product_features_relationship')
                ->whereHas('product_features_relationship',function ($q) use($dynamic_query)
                {
                    foreach($dynamic_query AS $k=>$v)
                    {
                       if($v != '')
                       {
                           $q->where(DB::raw($k), $v);
                       }
                    }
                });
        }


            $product  =  $pquery->orderBy('product_id','desc');



        return $product;

    }
}


