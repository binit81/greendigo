<?php

namespace Retailcore\Inward_Stock\Models\inward;

use Retailcore\Products\Models\product\product;
use Retailcore\Products\Models\product\category;
use Retailcore\Products\Models\product\brand;
use Retailcore\Products\Models\product\price_master;
use Retailcore\Store_Profile\Models\store_profile\company_relationship_tree;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use App\company;
use Illuminate\Support\Facades\DB;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Retailcore\Products\Models\product\ProductFeatures;

class batchnowise_report_export implements FromQuery, WithHeadings, WithMapping
{

    use Exportable;

    public $from_date = '';
    public $to_date = '';
    public $productsearch = '';

    public function __construct($from_date,$to_date,$productsearch,$batchnosearch) {
        $this->from_date = $from_date;
        $this->to_date = $to_date;
        $this->productsearch = $productsearch;
        $this->batchnosearch= $batchnosearch;

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
        $batch_no_report_header = [];
        if(sizeof($this->get_store)!=0)
         {
            $batch_no_report_header[] = 'Location';
         }
        $batch_no_report_header[] = 'Barcode';
        $batch_no_report_header[] = 'Product Name';

        $dynamic_header = '';
        if (isset($this->product_features) && $this->product_features != '' && !empty($this->product_features))
        {
            foreach ($this->product_features AS $feature_key => $feature_value)
            {
                if ($feature_value['show_feature_url'] != '' && $feature_value['show_feature_url'] != 'NULL' && $feature_value['show_feature_url'] != null)
                {
                    $search =  $this->page_url;

                    if (strstr($feature_value['show_feature_url'],$search) )
                    {
                        $this->show_dynamic_feature[$feature_value['html_id']] = $feature_value['product_features_id'];
                        $dynamic_header .= $batch_no_report_header[] = $feature_value['product_features_name'];
                    }
                }
            }
        }
        $dynamic_header;

        $batch_no_report_header[] = 'UQC';
         if($bill_calculation_case ==1)
        {
        $batch_no_report_header[] = 'Batch No.';
        $batch_no_report_header[] = 'Cost Rate';
        }
        $batch_no_report_header[] = 'MRP';
        $batch_no_report_header[] = 'Opening';
        $batch_no_report_header[] = 'Inward';
        $batch_no_report_header[] = 'Sold';
        $batch_no_report_header[] = 'Franchise Qty';
        $batch_no_report_header[] = 'Stock Transfer';
        $batch_no_report_header[] = 'Consignment';
        $batch_no_report_header[] = 'Return';
        $batch_no_report_header[] = 'Restock';
        $batch_no_report_header[] = 'Damage';
        $batch_no_report_header[] = 'Used';
        $batch_no_report_header[] = 'Supplier Return';
        $batch_no_report_header[] = 'InStock';
        if($bill_calculation_case ==1)
        {
        $batch_no_report_header[] = 'Total Cost Rate';
        $batch_no_report_header[] = 'Total MRP Value';
        }
        $batch_no_report_header[] = 'Expiry Date';
        $batch_no_report_header[] = 'Expiry Days';

        return $batch_no_report_header;
    }



public function map($inward_product): array
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
         $state_id  =  company_profile::select('bill_calculation')->where('company_id',Auth::user()->company_id)->get();

        $bill_calculation_case   = $state_id[0]['bill_calculation'];
        $barcode = '';
        $rows    = [];

        $inward_start_date =  date("Y-m-d", strtotime(date("d-m-Y")));
        $inward_end_date =  date("Y-m-d", strtotime(date("d-m-Y")));
        $today_date =  date('d-m-Y');
        $end_date =  date('d-m-Y');
        if($this->from_date!='')
        {
            $inward_start_date  =date("Y-m-d",strtotime($this->from_date));
            $inward_end_date  =date("Y-m-d",strtotime($this->to_date));
            $end_date  = $this->to_date;
            $today_date =  $this->from_date;
        }

      
        if($inward_product->product->supplier_barcode != '')
        {
            $barcode =  $inward_product->product->supplier_barcode;
        }
        else
        {
            $barcode = $inward_product->product->product_system_barcode;
        }
        

        $diff = '';
        if($inward_product->expiry_date != null)
        {
            $now = strtotime(date('d-m-Y')); //CURRENT DATE
            $expiry_date = strtotime($inward_product->expiry_date);
            $datediff = $expiry_date-$now;
            $diff =  round($datediff / (60 * 60 * 24));
        }

        $uqc_name = '';


        if($inward_product['product']['uqc_id'] != '' && $inward_product['product']['uqc_id'] != null && $inward_product['product']['uqc_id'] != 0)
        {
            $uqc_name = $inward_product['product']['uqc']['uqc_shortname'];
        }

        $feature_show_val = array();
        if($this->show_dynamic_feature != '')
        {
            foreach($this->show_dynamic_feature AS $fea_key=>$fea_val)
            {
                $feature_data_id = $inward_product['product']['product_features_relationship'][$fea_key];

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

        $opening   =   $inward_product['totalinwardqty'] - $inward_product['totalsoldqty'] + $inward_product['totalrestock'] - $inward_product['totalused'] - $inward_product['totalddamage'] - $inward_product['totalsuppreturn']-$inward_product['totalconsign'] - $inward_product['totalfranchiseqty']-$inward_product['totalstransfer'];
        $stock     =   $opening +$inward_product['currentinward'] -$inward_product['currentsold'] + $inward_product['currentrestock']-$inward_product['currentused'] - $inward_product['currentddamage']  - $inward_product['currentsuppreturn'] - $inward_product['currentconsign'] - $inward_product['currentfranchiseqty'] - $inward_product['currentstransfer'];
        $todayinward  = $inward_product->currentinward != '' ?$inward_product->currentinward : 0;
        $todaysold    = $inward_product->currentsold != '' ?$inward_product->currentsold : 0;

        $totaldamage  =  $inward_product['currentdamage'] +  $inward_product['currentddamage'];


            if($inward_product->averagemrp !='')
            {
                $averagemrp   = $inward_product->averagemrp;
            }
            else
            {
                $averagemrp    =  $inward_product->offer_price != '' ?$inward_product->offer_price : 0;
            }

            $totalmrpvalue  =  $averagemrp * $stock;

            if($inward_product->averagecost !='')
            {
                $averagecost   = $inward_product->averagecost;
            }
            else
            {
                $averagecost    =  $inward_product->cost_rate != '' ?$inward_product->cost_rate : 0;
            }

            $totalcostvalue  =  $averagecost * $stock;

        if(sizeof($this->get_store)!=0)
         {
            $rows[] = $companyname;
         }
           $rows[] = $barcode;
           $rows[] = $inward_product->product->product_name;
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
           $rows[] = $uqc_name;
           $rows[] = $inward_product->batch_no;
            if($bill_calculation_case ==1)
           {
           $rows[] = $inward_product->averagecost;
           $rows[] = $inward_product->averagemrp;
           }
           $rows[] = $opening != '' ?$opening : '0';          
           $rows[] = $inward_product->currentinward != '' ?$inward_product->currentinward : '0';
           $rows[] = $inward_product->currentsold != '' ?$inward_product->currentsold : '0';
           $rows[] = $inward_product->currentfranchiseqty != '' ?$inward_product->currentfranchiseqty : '0';
           $rows[] = $inward_product->currentstransfer != '' ?$inward_product->currentstransfer : '0';
           $rows[] = $inward_product->currentconsign != '' ?$inward_product->currentconsign : '0';
           $rows[] = $inward_product->currentreturn != '' ?$inward_product->currentreturn : '0';
           $rows[] = $inward_product->currentrestock != '' ?$inward_product->currentrestock : '0';
           $rows[] = $totaldamage != '' ?$totaldamage : '0';
           $rows[] = $inward_product->currentused != '' ?$inward_product->currentused : '0';
           $rows[] = $inward_product->currentsuppreturn != '' ?$inward_product->currentsuppreturn : '0';
           $rows[] = $stock != '' ?$stock : '0'; 
            if($bill_calculation_case ==1)
           {
           $rows[] = $totalcostvalue != '' ? $totalcostvalue : '0';
           $rows[] = $totalmrpvalue != '' ? $totalmrpvalue : '0';
           }
           $rows[] = $inward_product->expiry_date != '' ? $inward_product->expiry_date : '0';
           $rows[] = $diff != '' ? $diff : '0';

           return $rows;
    }


    public function query()
    {
        $inward_start_date =  date("Y-m-d", strtotime(date("d-m-Y")));
        $inward_end_date =  date("Y-m-d", strtotime(date("d-m-Y")));

        if($this->from_date!='')
        {
            $inward_start_date  =  date("Y-m-d",strtotime($this->from_date));
            $inward_end_date   = date("Y-m-d",strtotime($this->to_date));
        }

        $from_date = $this->from_date;

       
        if(isset($this->productsearch) && $this->productsearch != '')
        {

            if(strpos($this->productsearch, '_') !== false)
            {
                $prodbarcode   =   explode('_',$this->productsearch);
                $prod_barcode  =  $prodbarcode[0];
            }
            else
            {
                $prod_barcode  =  $this->productsearch;
            }

      
        }

         if(isset($this->query) && $this->query != '' && isset($this->query['store_name']) && $this->query['store_name'] != '')
            {
                 $company_id      =   $this->query['store_name']; 
            }
            else
            {
                 $company_id      =   Auth::user()->company_id;
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

            if(isset($this->batchnosearch) && $this->batchnosearch != '')
            {
                $inward_product->where('batch_no',$this->batchnosearch);
            }
            if(isset($this->productsearch) && $this->productsearch != '')
            {

                      $prquery = product::select('product_id')
                     ->where('deleted_at','=',NULL)
                     ->with('price_master')
                     ->whereHas('price_master',function ($q) use($company_id){
                            $q->where('company_id',$company_id);
                       });

                   if(strpos($this->productsearch, '_') !== false)
                    {
                        $prodname      =   explode('_',$this->productsearch);
                        $prod_barcode  =  $prodname[0];
                        $prod_name     =  $prodname[1];

                        $prquery->where('product_name', 'LIKE', "%$prod_name%")
                                 ->orWhere('product_system_barcode', 'LIKE', "%$prod_barcode%")
                                 ->orWhere('supplier_barcode', 'LIKE', "%$prod_barcode%");
                                 
                    }
                    else
                    {
                        $prod_barcode   =   $this->productsearch;
                        $prod_name      =   $this->productsearch;
                        $prquery->where('product_name', 'LIKE', "%$prod_name%")
                                 ->orWhere('product_system_barcode', 'LIKE', "%$prod_barcode%");

                    }

                    $prodresult  =  $prquery->get();
                  

                     $inward_product->whereIn('product_id',$prodresult);
                }

                $inward_product = $inward_product->groupBy('product_id','batch_no')
                                ->orderBy('inward_product_detail_id','DESC');

       
        return $inward_product;
    }

}


