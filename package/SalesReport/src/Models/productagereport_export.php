<?php

namespace Retailcore\SalesReport\Models;

use Illuminate\Database\Eloquent\Model;
use Retailcore\Products\Models\product\product;
use Retailcore\Sales\Models\sales_bill;
use Retailcore\Sales\Models\reference;
use Retailcore\SalesReturn\Models\return_bill;
use Retailcore\SalesReturn\Models\return_product_detail;
use Retailcore\SalesReturn\Models\returnbill_product;
use Retailcore\Sales\Models\sales_product_detail;
use Retailcore\Sales\Models\sales_bill_payment_detail;
use Retailcore\Sales\Models\payment_method;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Retailcore\ProductAge_Range\Models\productage_range;
use Retailcore\Stock_Transfer\Models\stock_transfer\stock_transfer;
use Retailcore\Stock_Transfer\Models\stock_transfer\stock_transfer_detail;
use Retailcore\Consignment\Models\consign_bill;
use Retailcore\Consignment\Models\consign_products_detail;
use Retailcore\DamageProducts\Models\damageproducts\damage_product;
use Retailcore\DamageProducts\Models\damageproducts\damage_product_detail;
use Retailcore\Debit_Note\Models\debit_note\debit_note;
use Retailcore\Debit_Note\Models\debit_note\debit_product_detail;
use App\state;
use App\country;
use Auth;
use Retailcore\Store_Profile\Models\store_profile\company_relationship_tree;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use App\company;
use Retailcore\Inward_Stock\Models\inward\inward_stock;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use DB;
use Excel;

class productagereport_export implements FromArray, WithHeadings
{
    use Exportable;

    private $myArray;
    private $myHeadings;
    
    public function __construct($myArray, $myHeadings,$companyname){
        $this->myArray = $myArray;
        $this->myHeadings = $myHeadings;
        $this->companyname = $companyname;
       
    }

    public function array(): array{

         $get_store       =    company_relationship_tree::where('warehouse_id',Auth::user()->company_id)
                                                             ->with('company_profile')
                                                             ->get();

        $newArray        =   array();
        $age_range       =   productage_range::where('company_id',Auth::user()->company_id)
                                              ->where('deleted_at',NULL)
                                              //->whereIn('productage_range_id',array(1,2))
                                              ->get();
       

        //return $this->myArray;
            foreach($this->myArray['products'] as $products)
            {
                    $count = '';
                    $rows    = [];

                    if($products['supplier_barcode']!='' || $products['supplier_barcode']!=NULL)
                    {
                       $barcode = $products['supplier_barcode'];
                    }
                    else
                    {
                      $barcode = $products['product_system_barcode'];
                    }
                  
                   
                  if(sizeof($get_store)!=0)
                   {
                      $rows[] = $this->companyname;
                   }
                   $rows[] = $products['product_name'];
                   $rows[] = $barcode;
                   $rows[] = $products['hsn_sac_code'];
                   $totalsinward = 0;
                   $totalsinstock = 0;
                   foreach($age_range AS $rangekey=>$range_value) 
                  {
                    $html_id   =  round($range_value['range_from']).' - '.round($range_value['range_to']);
                    $html_exp  =  explode(' - ',$products[''.$html_id.'']);
                      
                      $totalsinward    +=  $html_exp[0];
                      $totalsinstock   +=  $html_exp[1];
                     
                      $rows[] =  $html_exp[0];
                      $rows[] =  $html_exp[1];
                  }
                   $rows[] =  $totalsinward!=''?$totalsinward:'0';
                   $rows[] =  $totalsinstock!=''?$totalsinstock:'0';
                 
               $newArray[]  = $rows;

        }

     

       return $newArray;

    }
    public function headings(): array{      
       return $this->myHeadings;
    }


}

