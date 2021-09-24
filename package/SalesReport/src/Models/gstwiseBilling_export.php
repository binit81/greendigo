<?php

namespace Retailcore\SalesReport\Models;

use Retailcore\Sales\Models\sales_bill;
use Retailcore\Sales\Models\sales_product_detail;
use Retailcore\SalesReturn\Models\return_product_detail;
use Retailcore\SalesReturn\Models\return_bill;
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
class gstwiseBilling_export implements FromArray, WithHeadings
{

   // use Exportable;

    private $myArray;
    private $myHeadings;

    public function __construct($myArray, $myHeadings,$companyname,$company_id){
        $this->myArray = $myArray;
        $this->myHeadings = $myHeadings;
        $this->companyname = $companyname;
        $this->company_id = $company_id;
    }

    public function array(): array{

        $get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();

        $gst_slabs = sales_product_detail::select('cgst_percent','sgst_percent','igst_percent')
            ->where('company_id',$this->company_id)
            ->where('deleted_at','=',NULL)
            ->orderBy('igst_percent', 'ASC')
            ->groupBy('igst_percent')
            ->get();
        $newArray  = array();
        
        //return $this->myArray;
            foreach($this->myArray['sales'] as $sales)
            {
                    $count = '';
                    $rows    = [];
                   
                    
                    $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title','decimal_points','decimalpoints_forview')->where('company_id',Auth::user()->company_id)->get();
                    $company_state   = $state_id[0]['state_id'];
                    $tax_type        = $state_id[0]['tax_type'];
                    $decimal_points  = $state_id[0]['decimal_points'];
                    $decimalpoints_forview = $state_id[0]['decimalpoints_forview'];
                   
                    if(sizeof($get_store)!=0)
                   {
                      $rows[] = $this->companyname;
                   }       
                   $rows[] = $sales['bill_no'];
                   $rows[] = $sales->bill_date;
                   $rows[] = $sales['customer']['customer_name'];
                   foreach($gst_slabs AS $gstkey=>$gst_value)
                    {
                         $scount  = 0;
                         $billtariff = 0;
                         $billcgst = 0;
                         $billsgst = 0;
                         $billigst = 0;

                        foreach($sales->sales_product_detail AS $salesroom_key=>$salesroom_value)
                        {
                            if($gst_value->igst_percent == $salesroom_value->igst_percent){
                                
                                $billtariff  +=  $salesroom_value->sellingprice_afteroverall_discount;
                                $billcgst    +=  $salesroom_value->cgst_amount;
                                $billsgst    +=  $salesroom_value->sgst_amount;
                                $billigst    +=  $salesroom_value->igst_amount;
                                $scount++;
                             }

                         }
                         if($tax_type==1)
                         {
                                if($scount == 0)
                                 {
                                  
                                    $rows[] =  '0';
                                    $rows[] =  '0';
                                 
                                 }
                                 else
                                 {
                                  
                                    $rows[] =  number_format($billtariff,$decimalpoints_forview);
                                    $rows[] =  number_format($billigst,$decimalpoints_forview);
                                 

                                 }
                         }
                         else
                         {
                                if($scount == 0)
                                 {
                                  
                                    $rows[] =  '0';
                                    $rows[] =  '0';
                                    $rows[] =  '0';
                                    $rows[] =  '0';
                                 
                                 }
                                 else
                                 {
                                  
                                    $rows[] =  number_format($billtariff,$decimalpoints_forview);
                                  
                                    if($sales['state_id']==$company_state)
                                    {
                                      
                                        $rows[] =  number_format($billcgst,$decimalpoints_forview);
                                        $rows[] =  number_format($billsgst,$decimalpoints_forview);
                                        $rows[] =  '0.00';
                                       
                                    } 
                                    else
                                    {
                                       
                                        $rows[] =  '0.00';
                                        $rows[] =  '0.00';
                                        $rows[] =  number_format($billigst,$decimalpoints_forview);
                                        
                                    }  
                                   

                                 }
                         }
                         
                        

                    }
                   $rows[] = number_format($sales->sellingprice_after_discount,$decimalpoints_forview);
                   if($tax_type==1)
                   {
                        $rows[] = number_format($sales->total_igst_amount,$decimalpoints_forview);
                   }
                   else
                   {
                        if($sales['state_id']==$company_state)
                        {
                           
                             $rows[] = number_format($sales->total_cgst_amount,$decimalpoints_forview);
                             $rows[] = number_format($sales->total_sgst_amount,$decimalpoints_forview);
                             $rows[] = '0.00';
                            
                        }
                        else
                        {
                           
                                $rows[] = '0.00';
                                $rows[] = '0.00';
                                $rows[] = number_format($sales->total_igst_amount,$decimalpoints_forview);
                            
                        }
                   }
                   
                   $rows[] = number_format($sales->total_bill_amount,$decimalpoints_forview);
                   $rows[] = $sales['reference']['reference_name'];
           

               $newArray[]  = $rows;
 
        }

        foreach($this->myArray['returnbill'] as $returnbill)
        {
                    $count = '';
                    $rows    = [];
                   
                    
                    $state_id  =  company_profile::select('state_id','tax_type','tax_title','currency_title','decimal_points','decimalpoints_forview')->where('company_id',Auth::user()->company_id)->get();
                    $company_state   = $state_id[0]['state_id'];
                    $tax_type        = $state_id[0]['tax_type'];
                    $decimal_points  = $state_id[0]['decimal_points'];
                    $decimalpoints_forview   = $state_id[0]['decimalpoints_forview'];

                    if(sizeof($get_store)!=0)
                   {
                      $rows[] = $this->companyname;
                   }
                   $rows[] = $returnbill['sales_bill']['bill_no'];
                   $rows[] = $returnbill->bill_date;
                   $rows[] = $returnbill['customer']['customer_name'];
                   foreach($gst_slabs AS $gstkey=>$gst_value)
                    {
                         $scount  = 0;
                         $billtariff = 0;
                         $billcgst = 0;
                         $billsgst = 0;
                         $billigst = 0;

                        foreach($returnbill->return_product_detail AS $returnroom_key=>$returnroom_value)
                        {
                            if($gst_value->igst_percent == $returnroom_value->igst_percent){
                                
                                $billtariff  +=  $returnroom_value->sellingprice_afteroverall_discount;
                                $billcgst    +=  $returnroom_value->cgst_amount;
                                $billsgst    +=  $returnroom_value->sgst_amount;
                                $billigst    +=  $returnroom_value->igst_amount;
                                $scount++;
                             }

                         }
                         if($tax_type==1)
                         {
                                if($scount == 0)
                                 {
                                  
                                    $rows[] =  '0';
                                    $rows[] =  '0';
                                   
                                 
                                 }
                                 else
                                 {
                                  
                                    $rows[] =  -1 * (number_format($billtariff,$decimalpoints_forview));
                                    $rows[] =  -1 * (number_format($billigst,$decimalpoints_forview));
                                  

                                 }
                         }
                         else
                         {
                                if($scount == 0)
                                 {
                                  
                                    $rows[] =  '0';
                                    $rows[] =  '0';
                                    $rows[] =  '0';
                                    $rows[] =  '0';
                                 
                                 }
                                 else
                                 {
                                  
                                    $rows[] =  -1 * (number_format($billtariff,$decimalpoints_forview));
                                  
                                    if($sales['state_id']==$company_state)
                                    {
                                      
                                        $rows[] =  -1 * (number_format($billcgst,$decimalpoints_forview));
                                        $rows[] =  -1 * (number_format($billsgst,$decimalpoints_forview));
                                        $rows[] =  '0.00';
                                       
                                    } 
                                    else
                                    {
                                       
                                        $rows[] =  '0.00';
                                        $rows[] =  '0.00';
                                        $rows[] =  -1 * (number_format($billigst,$decimalpoints_forview));
                                        
                                    }  
                                   

                                 }
                         }
                         
                        

                    }
                   

                   $rows[] = '-'.(number_format($returnbill->sellingprice_after_discount,$decimalpoints_forview));
                   if($tax_type==1)
                   {
                        $rows[] = '-'.(number_format($returnbill->total_igst_amount,$decimalpoints_forview));
                   }
                   else
                   {
                        if($returnbill['state_id']==$company_state)
                        {
                           
                             $rows[] = '-'.(number_format($returnbill->total_cgst_amount,$decimalpoints_forview));
                             $rows[] = '-'.(number_format($returnbill->total_sgst_amount,$decimalpoints_forview));
                             $rows[] = '0.00';
                            
                        }
                        else
                        {
                           
                                $rows[] = '0.00';
                                $rows[] = '0.00';
                                $rows[] = '-'.(number_format($returnbill->total_igst_amount,$decimalpoints_forview));
                            
                        }
                   }
                   
                   $rows[] = '-'.(number_format($returnbill->total_bill_amount,$decimalpoints_forview));
                   $rows[] = $returnbill['reference']['reference_name'];
           

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

