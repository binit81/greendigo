<?php

namespace Retailcore\SalesReport\Models;

use Retailcore\Sales\Models\sales_bill;
use Retailcore\Sales\Models\sales_product_detail;
use Retailcore\Sales\Models\sales_bill_payment_detail;
use Retailcore\SalesReturn\Models\return_product_detail;
use Retailcore\Inward_Stock\Models\inward\inward_stock;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Retailcore\Supplier\Models\supplier\supplier_gst;
use Retailcore\Supplier\Models\supplier\supplier_company_info;
use Retailcore\Customer\Models\customer\customer;
use Retailcore\Sales\Models\payment_method;
use Retailcore\Products\Models\product\product;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
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
class supplier_salereport_export implements FromArray, WithHeadings
{

   // use Exportable;

    private $myArray;
    private $myHeadings;

    public function __construct($myArray, $myHeadings){
        $this->myArray = $myArray;
        $this->myHeadings = $myHeadings;
    }

    public function array(): array{

       
        $newArray  = array();
        $state_id  =  company_profile::select('state_id','tax_type','decimal_points','billtype','bill_calculation','decimalpoints_forview')->where('company_id',Auth::user()->company_id)->get();
       
        $tax_type        = $state_id[0]['tax_type'];
        $decimal_points  = $state_id[0]['decimal_points'];
        $decimalpoints_forview  = $state_id[0]['decimalpoints_forview'];

        //return $this->myArray;
            foreach($this->myArray['productdetails'] as $sales_value)
            {
                    $count = '';
                    
                   
                

                     $inwardids   = explode(',' ,substr($sales_value['inwardids'],0,-1));
                     $inwardqtys  = explode(',' ,substr($sales_value['inwardqtys'],0,-1));
                     $total_price = 0;

                     if($sales_value['inwardids'] !='' || $sales_value['inwardids'] !=null)
                    {

                      foreach($inwardids as $inidkey=>$inids)
                      {
                            $rows    = [];
                             $cost_price = inward_product_detail::select('cost_rate','supplier_gst_id','inward_stock_id')->find($inids);
                            $invoice_no = inward_stock::select('invoice_no')->find($cost_price['inward_stock_id']);
                            $supplier_id = supplier_gst::select('supplier_company_info_id')->find($cost_price['supplier_gst_id']);

                            $supplier_name = supplier_company_info::select('supplier_company_name')->find($supplier_id['supplier_company_info_id']);
                             
                            $total_price      =    $cost_price['cost_rate'] * $inwardqtys[$inidkey];  
              

                             $taxable          =   ($sales_value->sellingprice_afteroverall_discount / $sales_value['qty']) * $inwardqtys[$inidkey];
                            $totalable        =   ($sales_value->total_amount / $sales_value['qty']) * $inwardqtys[$inidkey];

                            $averagecost      =   ($total_price / $inwardqtys[$inidkey]) * $inwardqtys[$inidkey];
                            $profitamt        =   $taxable  - $averagecost; 
                            $profitper        =   ($profitamt * 100)/$averagecost;  

                            if($sales_value['product']['supplier_barcode']!='' || $sales_value['product']['supplier_barcode']!=NULL)
                            {
                               $barcode = $sales_value['product']['supplier_barcode'];
                            }
                            else
                            {
                              $barcode = $sales_value['product']['product_system_barcode'];
                            }

                             $uqc_name = '';
                            $size_name = '';
                            if($sales_value['product']['size_id'] != '' && $sales_value['product']['size_id'] != null && $sales_value['product']['size_id'] != 0)
                            {
                                $size_name = $sales_value['product']['size']['size_name'];
                            }
                            $uqc_name = '';
                            if($sales_value['product']['uqc_id'] != '' && $sales_value['product']['uqc_id'] != null && $sales_value['product']['uqc_id'] != 0)
                            {
                                $uqc_name = $sales_value['product']['uqc']['uqc_shortname'];
                            }
                             if($tax_type==1)
                             {
                                        $igst_amount       =   ($sales_value->igst_amount / $sales_value['qty']) * $inwardqtys[$inidkey];
                                        $totaligstper      =   $sales_value->igst_percent;
                                        $totaligstamount   =   $igst_amount;
                             } 
                             else
                             {
                                if($sales_value['sales_bill']['state_id']==$state_id[0]['state_id'])
                                {
                                            $cgst_amount       =   ($sales_value->cgst_amount / $sales_value['qty']) * $inwardqtys[$inidkey];
                                            $sgst_amount       =   ($sales_value->sgst_amount / $sales_value['qty']) * $inwardqtys[$inidkey];
                                            $totalcgstper      =   $sales_value->cgst_percent;
                                            $totalcgstamount   =   $cgst_amount;
                                            $totalsgstper      =   $sales_value->sgst_percent;
                                            $totalsgstamount   =   $sgst_amount;
                                            $totaligstper      =   '0';
                                            $totaligstamount   =   '0';
                                }
                                else
                                {
                                            $igst_amount       =   ($sales_value->igst_amount / $sales_value['qty']) * $inwardqtys[$inidkey];
                                            $totalcgstper      =   '0';
                                            $totalcgstamount   =   '0';
                                            $totalsgstper      =   '0';
                                            $totalsgstamount   =   '0';
                                            $totaligstper      =   $sales_value->igst_percent;
                                            $totaligstamount   =   $igst_amount;
                                }
                             }
                         
                         $rows[] = $supplier_name['supplier_company_name'];
                         $rows[] = $invoice_no['invoice_no'];   
                         $rows[] = $sales_value['sales_bill']['bill_no'];
                         $rows[] = $sales_value['sales_bill']['bill_date'];
                         $rows[] = $sales_value['product']['product_name'];
                         $rows[] = $barcode;
                         $rows[] = $sales_value['product']['hsn_sac_code'];
                         $rows[] = $size_name .' '.$uqc_name;
                         if($state_id[0]['billtype']==3)
                        {
                            $rows[] = $sales_value['batchprice_master']['batch_no'];
                        }
                         $rows[] = $inwardqtys[$inidkey];
                         if($state_id[0]['bill_calculation']==1)
                         {
                         $rows[] = number_format($taxable,$decimalpoints_forview);
                         if($tax_type==1)
                         {
                             $rows[] = $totaligstper!=''?$totaligstper:'0';
                             $rows[] = $totaligstamount!=''?$totaligstamount:'0';
                         }
                         else
                         {
                             $rows[] = $totalcgstper!=''?$totalcgstper:'0';
                             $rows[] = $totalcgstamount!=''?$totalcgstamount:'0';
                             $rows[] = $totalsgstper!=''?$totalsgstper:'0';
                             $rows[] = $totalsgstamount!=''?$totalsgstamount:'0';
                             $rows[] = $totaligstper!=''?$totaligstper:'0';
                             $rows[] = $totaligstamount!=''?$totaligstamount:'0';
                         }
                         $rows[] = number_format($totalable,$decimal_points);
                         $rows[] = number_format($averagecost,$decimalpoints_forview);
                         $rows[] = number_format($profitamt,$decimalpoints_forview);
                         $rows[] = number_format($profitper,$decimalpoints_forview);
                        }

                       
                        $newArray[]  = $rows;
  
                      } 
                       
                     
                    }





        }

        foreach($this->myArray['rproductdetails'] as $return_value)
            {
                    $count = '';




                     $inwardids  = explode(',' ,substr($return_value['inwardids'],0,-1));
                     $inwardqtys = explode(',' ,substr($return_value['inwardqtys'],0,-1));
                     $total_price = 0;

                    if($return_value['inwardids'] !='' || $return_value['inwardids'] !=null)
                   {
                      foreach($inwardids as $inidkey=>$inids)
                      {
                         $rows    = [];
                            $cost_price = inward_product_detail::select('cost_rate','supplier_gst_id','inward_stock_id')->find($inids);
                            $invoice_no = inward_stock::select('invoice_no')->find($cost_price['inward_stock_id']);
                            $supplier_id = supplier_gst::select('supplier_company_info_id')->find($cost_price['supplier_gst_id']);

                            $supplier_name = supplier_company_info::select('supplier_company_name')->find($supplier_id['supplier_company_info_id']);
                             
                            $total_price      =    $cost_price['cost_rate'] * $inwardqtys[$inidkey];  
                            

                            $taxable          =   ($return_value->sellingprice_afteroverall_discount / $return_value['qty']) * $inwardqtys[$inidkey];
                            $totalable        =   ($return_value->total_amount / $return_value['qty']) * $inwardqtys[$inidkey];

                            $averagecost      =   ($total_price / $inwardqtys[$inidkey]) * $inwardqtys[$inidkey];
                            $profitamt        =   $taxable  - $averagecost;
                            
                            $profitper        =   ($profitamt * 100)/$averagecost;

                             if($return_value['product']['supplier_barcode']!='' || $return_value['product']['supplier_barcode']!=NULL)
                              {
                                 $barcode = $return_value['product']['supplier_barcode'];
                              }
                              else
                              {
                                $barcode = $return_value['product']['product_system_barcode'];
                              }
                              $uqc_name = '';
                                $size_name = '';
                                if($return_value['product']['size_id'] != '' && $return_value['product']['size_id'] != null && $return_value['product']['size_id'] != 0)
                                {
                                    $size_name = $return_value['product']['size']['size_name'];
                                }
                                $uqc_name = '';
                                if($return_value['product']['uqc_id'] != '' && $return_value['product']['uqc_id'] != null && $return_value['product']['uqc_id'] != 0)
                                {
                                    $uqc_name = $return_value['product']['uqc']['uqc_shortname'];
                                }
                              if($tax_type==1)
                             {
                                        $igst_amount       =   ($return_value->igst_amount / $return_value['qty']) * $inwardqtys[$inidkey];
                                        $totaligstper      =   $return_value->igst_percent;
                                        $totaligstamount   =   $igst_amount;
                             } 
                             else
                             {
                                if($return_value['return_bill']['state_id']==$state_id[0]['state_id'])
                                {
                                            $cgst_amount       =   ($sales_value->cgst_amount / $sales_value['qty']) * $inwardqtys[$inidkey];
                                            $sgst_amount       =   ($sales_value->sgst_amount / $sales_value['qty']) * $inwardqtys[$inidkey];
                                            $totalcgstper      =   $sales_value->cgst_percent;
                                            $totalcgstamount   =   $cgst_amount;
                                            $totalsgstper      =   $sales_value->sgst_percent;
                                            $totalsgstamount   =   $sgst_amount;
                                            $totaligstper      =   '0';
                                            $totaligstamount   =   '0';
                                }
                                else
                                {
                                            $igst_amount       =   ($sales_value->igst_amount / $sales_value['qty']) * $inwardqtys[$inidkey];
                                            $totalcgstper      =   '0';
                                            $totalcgstamount   =   '0';
                                            $totalsgstper      =   '0';
                                            $totalsgstamount   =   '0';
                                            $totaligstper      =   $sales_value->igst_percent;
                                            $totaligstamount   =   $igst_amount;
                                }
                             }

                             $rows[] = $supplier_name['supplier_company_name'];
                             $rows[] = $invoice_no['invoice_no'];  
                             $rows[] = $return_value['return_bill']['sales_bill']['bill_no'];
                             $rows[] = $return_value['return_bill']['bill_date'];
                             $rows[] = $return_value['product']['product_name'];
                             $rows[] = $barcode;
                             $rows[] = $return_value['product']['hsn_sac_code'];
                             $rows[] = $size_name .' '.$uqc_name;
                             if($state_id[0]['billtype']==3)
                            {
                                $rows[] = $return_value['rbatchprice_master']['batch_no'];
                            }
                             $rows[] = -1*($inwardqtys[$inidkey]);
                             if($state_id[0]['bill_calculation']==1)
                            {
                             $rows[] =  -1*(number_format($taxable,$decimalpoints_forview));
                             if($tax_type==1)
                             {
                                 $rows[] = $totaligstper!=''?$totaligstper:'0';
                                 $rows[] = $totaligstamount!=''?-1*($totaligstamount):'0';
                             } 
                             else
                             {
                                 $rows[] = $totalcgstper!=''?$totalcgstper:'0';
                                 $rows[] = $totalcgstamount!=''?-1*($totalcgstamount):'0';
                                 $rows[] = $totalsgstper!=''?$totalsgstper:'0';
                                 $rows[] = $totalsgstamount!=''?-1*($totalsgstamount):'0'; 
                                 $rows[] = $totaligstper!=''?$totaligstper:'0';
                                 $rows[] = $totaligstamount!=''?-1*($totaligstamount):'0';
                             }
                             $rows[] =  -1*(number_format($totalable,$decimalpoints_forview));
                             $rows[] =  -1*(number_format($averagecost,$decimalpoints_forview));
                             $rows[] =  -1*(number_format($profitamt,$decimalpoints_forview));
                             $rows[] = number_format($profitper,$decimalpoints_forview);  
                             }    
                             $newArray[]  = $rows;
  
                      } 
                     
                    }
                    

                     
           

               
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

