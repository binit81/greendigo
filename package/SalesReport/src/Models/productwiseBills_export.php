<?php

namespace Retailcore\SalesReport\Models;

use Retailcore\Sales\Models\sales_bill;
use Retailcore\Sales\Models\sales_product_detail;
use Retailcore\Sales\Models\sales_bill_payment_detail;
use Retailcore\SalesReturn\Models\return_product_detail;
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
class productwiseBills_export implements FromArray, WithHeadings
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
        $state_id  =  company_profile::select('state_id','tax_type','decimal_points','billtype','bill_calculation','decimalpoints_forview')->where('company_id',Auth::user()->company_id)->get();

        $tax_type        = $state_id[0]['tax_type'];
        $decimal_points  = $state_id[0]['decimal_points'];
        $decimalpoints_forview = $state_id[0]['decimalpoints_forview'];

        //return $this->myArray;
            foreach($this->myArray['sales'] as $sales)
            {
                    $count = '';
                    $rows    = [];


                    $state_id  =  company_profile::select('state_id','billtype','bill_calculation')->where('company_id',Auth::user()->company_id)->get();
                   if($tax_type==1)
                   {
                              $totaligstper      =   $sales->igst_percent;
                              $totaligstamount   =   $sales->igst_amount;
                   }
                   else
                   {
                      if($sales['sales_bill']['state_id']==$state_id[0]['state_id'])
                      {
                                  $totalcgstper      =   $sales->cgst_percent!=''?$sales->cgst_percent:'0';
                                  $totalcgstamount   =   $sales->cgst_amount!=''?$sales->cgst_amount:'0';
                                  $totalsgstper      =   $sales->sgst_percent!=''?$sales->sgst_percent:'0';
                                  $totalsgstamount   =   $sales->sgst_amount!=''?$sales->sgst_amount:'0';
                                  $totaligstper      =   '0';
                                  $totaligstamount   =   '0';
                      }
                      else
                      {
                                  $totalcgstper      =   '0';
                                  $totalcgstamount   =   '0';
                                  $totalsgstper      =   '0';
                                  $totalsgstamount   =   '0';
                                  $totaligstper      =   $sales->igst_percent?$sales->igst_percent:'0';
                                  $totaligstamount   =   $sales->igst_amount?$sales->igst_amount:'0';
                      }
                   }
                   if($sales['product']['supplier_barcode']!='' || $sales['product']['supplier_barcode']!=NULL)
                  {
                     $barcode = $sales['product']['supplier_barcode'];
                  }
                  else
                  {
                    $barcode = $sales['product']['product_system_barcode'];
                  }
                  $uqc_name = '';

                  if($sales['product']['uqc_id'] != '' && $sales['product']['uqc_id'] != null && $sales['product']['uqc_id'] != 0)
                  {
                      $uqc_name = $sales['product']['uqc']['uqc_shortname'];
                  }

                $feature_show_val = array();
                if($this->show_dynamic_feature != '')
                {
                    foreach($this->show_dynamic_feature AS $fea_key=>$fea_val)
                    {
                        $feature_data_id = $sales['product']['product_features_relationship'][$fea_key];

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
                   $rows[] = $sales['sales_bill']['bill_no'];
                   $rows[] = $sales['sales_bill']['bill_date'];
                   $rows[] = $sales['sales_bill']['customer']['customer_name'];
                   $rows[] = $sales['product']['product_name'];
                   $rows[] = $barcode;
                   $rows[] = $sales['product']['hsn_sac_code'];
                   $rows[] = $sales['product']['product_code'];
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
                    $rows[] = $sales['batchprice_master']['batch_no'];
                   }
                 if($state_id[0]['bill_calculation']==1)
                 {
                      $rows[] = number_format($sales->sellingprice_before_discount,$decimalpoints_forview);
                 }

                   $rows[] = $sales->qty;
                if($state_id[0]['bill_calculation']==1)
                {
                   $rows[] = $sales->discount_percent!=''?$sales->discount_percent:'0';
                   $rows[] = $sales->discount_amount!=''?number_format($sales->discount_amount,$decimalpoints_forview):'0';
                   $rows[] = $sales->overalldiscount_amount!=''?number_format($sales->overalldiscount_amount,$decimalpoints_forview):'0';
                   $rows[] = $sales->sellingprice_afteroverall_discount!=''?number_format($sales->sellingprice_afteroverall_discount,$decimalpoints_forview):'0';
                   if($tax_type==1)
                   {
                        $rows[] = $totaligstper;
                        $rows[] = number_format($totaligstamount,$decimalpoints_forview);
                   }
                   else
                   {
                       $rows[] = $totalcgstper;
                       $rows[] = number_format($totalcgstamount,$decimalpoints_forview);
                       $rows[] = $totalsgstper;
                       $rows[] = number_format($totalsgstamount,$decimalpoints_forview);
                       $rows[] = $totaligstper;
                       $rows[] = number_format($totaligstamount,$decimalpoints_forview);
                   }

                   $rows[] = number_format($sales->total_amount,$decimalpoints_forview);
                }
                   $rows[] = $sales['sales_bill']['reference']['reference_name'];


               $newArray[]  = $rows;

        }

        foreach($this->myArray['returnbill'] as $returnbill)
            {
                    $count = '';
                    $rows    = [];


                    $state_id  =  company_profile::select('state_id','decimal_points','billtype','bill_calculation','decimalpoints_forview')->where('company_id',Auth::user()->company_id)->get();
                    $decimal_points   =  $state_id[0]['decimal_points'];
                   $decimalpoints_forview  = $state_id[0]['decimalpoints_forview'];
                  

                  if($tax_type==1)
                   {
                         $totaligstper      =   $returnbill->igst_percent;
                         $totaligstamount   =   -1 *($returnbill->igst_amount);
                   }
                   else
                   {
                      if($returnbill['return_bill']['state_id']==$state_id[0]['state_id'])
                      {
                                  $totalcgstper      =   $returnbill->cgst_percent;
                                  $totalcgstamount   =   -1 *($returnbill->cgst_amount);
                                  $totalsgstper      =   $returnbill->sgst_percent;
                                  $totalsgstamount   =   -1 *($returnbill->sgst_amount);
                                  $totaligstper      =   '0';
                                  $totaligstamount   =   '0';
                      }
                      else
                      {
                                  $totalcgstper      =   '0';
                                  $totalcgstamount   =   '0';
                                  $totalsgstper      =   '0';
                                  $totalsgstamount   =   '0';
                                  $totaligstper      =   $returnbill->igst_percent;
                                  $totaligstamount   =   -1 *($returnbill->igst_amount);
                      }
                  }

                  if($returnbill['product']['supplier_barcode']!='' || $returnbill['product']['supplier_barcode']!=NULL)
                  {
                     $barcode = $returnbill['product']['supplier_barcode'];
                  }
                  else
                  {
                    $barcode = $returnbill['product']['product_system_barcode'];
                  }


                  $uqc_name = '';
                  if($returnbill['product']['uqc_id'] != '' && $returnbill['product']['uqc_id'] != null && $returnbill['product']['uqc_id'] != 0)
                  {
                      $uqc_name = $returnbill['product']['uqc']['uqc_shortname'];
                  }

                $feature_show_val = array();
                if($this->show_dynamic_feature != '')
                {
                    foreach($this->show_dynamic_feature AS $fea_key=>$fea_val)
                    {
                        $feature_data_id = $returnbill['product']['product_features_relationship'][$fea_key];

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
                   $rows[] = $returnbill['return_bill']['sales_bill']['bill_no'];
                   $rows[] = $returnbill['return_bill']['bill_date'];
                   $rows[] = $returnbill['return_bill']['customer']['customer_name'];
                   $rows[] = $returnbill['product']['product_name'];
                   $rows[] = $barcode;
                   $rows[] = $returnbill['product']['hsn_sac_code'];
                   $rows[] = $returnbill['product']['product_code']; 
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
                    $rows[] = $returnbill['rbatchprice_master']['batch_no'];
                   }
                if($state_id[0]['bill_calculation']==1)
                {
                   $rows[] = '-'.(number_format($returnbill->sellingprice_before_discount,$decimalpoints_forview));
                }
                   $rows[] = -1 *($returnbill->qty);
                if($state_id[0]['bill_calculation']==1)
                {
                   $rows[] = $returnbill->discount_percent!=''?$returnbill->discount_percent:'0';
                   $rows[] = $returnbill->discount_amount!=''?'-'.(number_format($returnbill->discount_amount,$decimalpoints_forview)):'0';
                   $rows[] = $returnbill->overalldiscount_amount!=''?'-'.(number_format($returnbill->overalldiscount_amount,$decimalpoints_forview)):'0';
                   $rows[] = $returnbill->sellingprice_afteroverall_discount!=''?'-'.(number_format($returnbill->sellingprice_afteroverall_discount,$decimalpoints_forview)):'0';
                    if($tax_type==1)
                   {
                       $rows[] = $totaligstper;
                       $rows[] = number_format($totaligstamount,$decimalpoints_forview);
                   }
                   else
                   {
                       $rows[] = $totalcgstper;
                       $rows[] = number_format($totalcgstamount,$decimalpoints_forview);
                       $rows[] = $totalsgstper;
                       $rows[] = number_format($totalsgstamount,$decimalpoints_forview);
                       $rows[] = $totaligstper;
                       $rows[] = number_format($totaligstamount,$decimalpoints_forview);
                   }

                   $rows[] = '-'.(number_format($returnbill->total_amount,$decimalpoints_forview));
                 }
                   $rows[] = $returnbill['return_bill']['reference']['reference_name'];


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

