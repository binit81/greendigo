<?php

namespace Retailcore\SalesReport\Models;

use Retailcore\Products\Models\product\ProductFeatures;
use Retailcore\Sales\Models\sales_bill;
use Retailcore\Sales\Models\sales_product_detail;
use Retailcore\Sales\Models\sales_bill_payment_detail;
use Retailcore\SalesReturn\Models\return_product_detail;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Retailcore\Customer\Models\customer\customer;
use Retailcore\Sales\Models\payment_method;
use Retailcore\Products\Models\product\product;
use Retailcore\Store_Profile\Models\store_profile\company_relationship_tree;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use App\company;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;

// , WithBatchInserts, WithChunkReading
class profitloss_export implements FromArray, WithHeadings
{

   // use Exportable;

    private $myArray;
    private $myHeadings;
    private $show_dynamic_feature;

    public function __construct($myArray, $myHeadings,$show_dynamic_feature,$companyname){
        $this->myArray = $myArray;
        $this->myHeadings = $myHeadings;
        $this->companyname = $companyname;
        $this->show_dynamic_feature = $show_dynamic_feature;


    }

    public function array(): array{

         $get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();

        $newArray  = array();

        //return $this->myArray;
            foreach($this->myArray['productdetails'] as $sales_value)
            {
                    $count = '';
                    $rows    = [];
                    $state_id  =  company_profile::select('state_id','billtype','decimalpoints_forview')->where('company_id',Auth::user()->company_id)->get();

                    $decimalpoints_forview   =  $state_id[0]['decimalpoints_forview'];

                    $totalsellingprice   =   $sales_value['qty'] * $sales_value['sellingprice_before_discount'];
                    $totaldiscount       =   $sales_value['discount_amount']  + $sales_value['overalldiscount_amount'];
                    $totaldiscount       =   $totaldiscount!=''?round($totaldiscount,2):'0';

                     $inwardids   = explode(',' ,substr($sales_value['inwardids'],0,-1));
                     $inwardqtys  = explode(',' ,substr($sales_value['inwardqtys'],0,-1));
                     $total_price = 0;

                     if($sales_value['inwardids'] !='' || $sales_value['inwardids'] !=null)
                    {

                      foreach($inwardids as $inidkey=>$inids)
                      {
                            $cost_price = inward_product_detail::select('cost_rate')->find($inids);

                            $total_price += $cost_price['cost_rate'] * $inwardqtys[$inidkey];
                      }
                      $averagecost      =   ($total_price / $sales_value['qty']) * $sales_value['qty'];
                      $profitamt        =   $sales_value->sellingprice_afteroverall_discount  - $averagecost;
                      $profitper        =   ($profitamt * 100)/$averagecost;
                    }
                    else
                    {
                      $averagecost      =   0;
                      $profitamt        =   $sales_value->sellingprice_afteroverall_discount  - $averagecost;
                      $profitper        =   0;
                    }

                      if($sales_value['product']['supplier_barcode']!='' || $sales_value['product']['supplier_barcode']!=NULL)
                      {
                         $barcode = $sales_value['product']['supplier_barcode'];
                      }
                      else
                      {
                        $barcode = $sales_value['product']['product_system_barcode'];
                      }
                      $uqc_name = '';

                        if($sales_value['product']['uqc_id'] != '' && $sales_value['product']['uqc_id'] != null && $sales_value['product']['uqc_id'] != 0)
                        {
                            $uqc_name = $sales_value['product']['uqc']['uqc_shortname'];
                        }

                $feature_show_val = array();
                if($this->show_dynamic_feature != '')
                {
                    foreach($this->show_dynamic_feature AS $fea_key=>$fea_val)
                    {
                        $feature_data_id = $sales_value['product']['product_features_relationship'][$fea_key];

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





                   if(sizeof($get_store)!=0)
                   {
                      $rows[] = $this->companyname;
                   }
                   $rows[] = $sales_value['sales_bill']['bill_no'];
                   $rows[] = $sales_value['sales_bill']['bill_date'];
                   $rows[] = $sales_value['product']['product_name'];
                   $rows[] = $barcode;
                   $rows[] = $sales_value['product']['hsn_sac_code'];
                   $rows[] = $sales_value['product']['product_code'];
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
                   if($state_id[0]['billtype']==3)
                   {
                    $rows[] = $sales_value['batchprice_master']['batch_no'];
                   }
                   $rows[] = $sales_value['qty'];
                   $rows[] = number_format($totalsellingprice,$decimalpoints_forview);
                   $rows[] = $totaldiscount;
                   $rows[] = number_format($sales_value->sellingprice_afteroverall_discount,$decimalpoints_forview);
                   $rows[] = number_format($averagecost,$decimalpoints_forview);
                   $rows[] = number_format($profitamt,$decimalpoints_forview);
                   $rows[] = number_format($profitper,$decimalpoints_forview);


               $newArray[]  = $rows;

        }

        foreach($this->myArray['rproductdetails'] as $return_value)
            {
                    $count = '';
                    $rows    = [];
                    $state_id  =  company_profile::select('state_id','billtype','decimalpoints_forview')->where('company_id',Auth::user()->company_id)->get();
                    $decimalpoints_forview   =  $state_id[0]['decimalpoints_forview'];

                    $totalsellingprice   =   $return_value['qty'] * $return_value['sellingprice_before_discount'];
                    $totaldiscount       =   $return_value['discount_amount']  + $return_value['overalldiscount_amount'];
                    $totaldiscount       =   $totaldiscount!=''?round($totaldiscount,2):'-0';

                     $inwardids  = explode(',' ,substr($return_value['inwardids'],0,-1));
                     $inwardqtys = explode(',' ,substr($return_value['inwardqtys'],0,-1));
                     $total_price = 0;

                    if($return_value['inwardids'] !='' || $return_value['inwardids'] !=null)
                   {
                      foreach($inwardids as $inidkey=>$inids)
                      {
                            $cost_price = inward_product_detail::select('cost_rate')->find($inids);

                            $total_price += $cost_price['cost_rate'] * $inwardqtys[$inidkey];
                      }
                      $averagecost      =   ($total_price / $return_value['qty']) * $return_value['qty'];
                      $profitamt        =   $return_value->sellingprice_afteroverall_discount  - $averagecost;
                      $profitper        =   ($profitamt * 100)/$averagecost;
                    }
                    else
                    {
                        $averagecost      =   0;
                        $profitamt        =   $return_value->sellingprice_afteroverall_discount  - $averagecost;
                        $profitper        =   0;
                    }

                      if($return_value['product']['supplier_barcode']!='' || $return_value['product']['supplier_barcode']!=NULL)
                      {
                         $barcode = $return_value['product']['supplier_barcode'];
                      }
                      else
                      {
                        $barcode = $return_value['product']['product_system_barcode'];
                      }
                      $uqc_name = '';
                        
                        $uqc_name = '';
                        if($return_value['product']['uqc_id'] != '' && $return_value['product']['uqc_id'] != null && $return_value['product']['uqc_id'] != 0)
                        {
                            $uqc_name = $return_value['product']['uqc']['uqc_shortname'];
                        }
                        $feature_show_val = array();
                if($this->show_dynamic_feature != '')
                {
                    foreach($this->show_dynamic_feature AS $fea_key=>$fea_val)
                    {
                        $feature_data_id = $return_value['product']['product_features_relationship'][$fea_key];

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

                      
                   if(sizeof($get_store)!=0)
                   {
                      $rows[] = $this->companyname;
                   }  
                     $rows[] = $return_value['return_bill']['sales_bill']['bill_no'];
                     $rows[] = $return_value['return_bill']['bill_date'];
                     $rows[] = $return_value['product']['product_name'];
                     $rows[] = $barcode;
                     $rows[] = $return_value['product']['hsn_sac_code'];
                     $rows[] = $return_value['product']['product_code'];
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
                     if($state_id[0]['billtype']==3)
                     {
                      $rows[] = $return_value['rbatchprice_master']['batch_no'];
                     }
                     $rows[] = -1 *($return_value['qty']);
                     $rows[] = -1 *(number_format($totalsellingprice,$decimalpoints_forview));
                     $rows[] =  $totaldiscount;
                     $rows[] = -1*(number_format($return_value->sellingprice_afteroverall_discount,$decimalpoints_forview));
                     $rows[] = -1 *(number_format($averagecost,$decimalpoints_forview));
                     $rows[] = -1 *(number_format($profitamt,$decimalpoints_forview));
                     $rows[] = -1 *(number_format($profitper,$decimalpoints_forview));


               $newArray[]  = $rows;

        }
        // echo '<pre>';
        // print_r($newArray);
        // echo '</pre>';
        // exit;

       return $newArray;

    }
    public function headings(): array{
        return $this->myHeadings;

    }


}

