<?php

namespace Retailcore\DamageProducts\Models\damageproducts;

use Retailcore\Products\Models\product\product;
use Retailcore\Products\Models\product\colour;
use Retailcore\Products\Models\product\price_master;
use Retailcore\Products\Models\product\ProductFeatures;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use DB;

class damage_product_export implements FromQuery, WithHeadings, WithMapping
{

    use Exportable;

    public $from_date = '';
    public $to_date = '';
    public $damageproductsearch = '';
    public $DamageType='';
    public $product_code ='';

    public function __construct($from_date,$to_date,$damageproductsearch,$DamageType,$product_code)
    {

        $this->from_date = isset($from_date) && $from_date != '' ? date("Y-m-d",strtotime($from_date)) : '';
        $this->to_date = isset($to_date) && $to_date != '' ? date("Y-m-d",strtotime($to_date)) : '';

        $this->damageproductsearch = $damageproductsearch;
        $this->DamageType=$DamageType;
        $this->product_code=$product_code;

        $inward_type_from_comp = company_profile::where('company_id',Auth::user()->company_id)->select('inward_type','tax_type','tax_title','currency_title','inward_calculation')->first();


        $this->tax_type = $inward_type_from_comp['tax_type'];
        $this->tax_title = $inward_type_from_comp['tax_title'];
        $this->tax_currency = $inward_type_from_comp['currency_title'];
        $this->inward_calculation = $inward_type_from_comp['inward_calculation'];


        $this->product_features = ProductFeatures::getproduct_feature('');
        $this->page_url = ProductFeatures::get_current_page_url();
        $this->show_dynamic_feature = array();

    }

    public function headings(): array
    {
        $damage_product_wise_header = [];
            $damage_product_wise_header[] = 'Date';
            $damage_product_wise_header[] = 'Barcode';
            $damage_product_wise_header[] = 'Product Name';
            $damage_product_wise_header[] = 'Product Code';

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
                        $dynamic_header .= $damage_product_wise_header[] = $feature_value['product_features_name'];
                   }
                }
            }
        }
            $dynamic_header;

            $damage_product_wise_header[] = 'UQC';
            $damage_product_wise_header[] = 'Batch No.';
            $damage_product_wise_header[] = 'Invoice No.';
            if($this->inward_calculation != 3) {
                $damage_product_wise_header[] = 'Cost Rate';
            }
            $damage_product_wise_header[] = 'Quantity';
        if($this->inward_calculation != 3) {
            $damage_product_wise_header[] = 'Total Cost Rate';

            if ($this->tax_type == 1) {
                $damage_product_wise_header[] = $this->tax_title . "%";
                $damage_product_wise_header[] = $this->tax_title . ' ' . $this->tax_currency;
            } else {
                $damage_product_wise_header[] = 'Cost CGST %';
                $damage_product_wise_header[] = 'Cost CGST Amount';
                $damage_product_wise_header[] = 'Cost SGST %';
                $damage_product_wise_header[] = 'Cost SGST Amount';
                $damage_product_wise_header[] = 'Cost IGST %';
                $damage_product_wise_header[] = 'Cost IGST Amount';
            }


            $damage_product_wise_header[] = 'Total Cost Price';
            $damage_product_wise_header[] = 'MRP';
        }
            $damage_product_wise_header[] = 'Notes';
            $damage_product_wise_header[] = 'Status';

            return $damage_product_wise_header;
    }

    public function map($damageproducts): array
   {
        $rows    = [];

       $barcode = '';

       if($damageproducts['product']['supplier_barcode'] != '')
       {
           $barcode =  $damageproducts['product']['supplier_barcode'];
       }
       else
       {
           $barcode = $damageproducts['product']['product_system_barcode'];
       }

       $uqc_name = '';
       if($damageproducts['product']['uqc_id'] != '' && $damageproducts['product']['uqc_id'] != null && $damageproducts['product']['uqc_id'] != 0)
       {
           $uqc_name = $damageproducts['product']['uqc']['uqc_shortname'];
       }

       $product_code = '';
       if($damageproducts['product']['product_code'] != '' && $damageproducts['product']['product_code'] != null)
       {
           $product_code = $damageproducts['product']['product_code'];
       }

       $feature_show_val = array();
       if($this->show_dynamic_feature != '')
       {
           foreach($this->show_dynamic_feature AS $fea_key=>$fea_val)
           {
               $feature_data_id = $damageproducts['product']['product_features_relationship'][$fea_key];

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



       $rows[]         =   $damageproducts->damage_product->damage_date;
       $rows[]         =   $barcode;
       $rows[]         =   $damageproducts->product['product_name'];
       $rows[]         =   $product_code;

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

       $rows[]         =   $uqc_name;
       $rows[]         =   $damageproducts->batch_no;
       $rows[]         =   $damageproducts->inward_product_detail->inward_stock->invoice_no;
       if($this->inward_calculation != 3) {
           $rows[] = $damageproducts->product_cost_rate != '' ? $damageproducts->product_cost_rate : '0';
       }
       $rows[]         =   $damageproducts->product_damage_qty != '' ? $damageproducts->product_damage_qty : '0';
       if($this->inward_calculation != 3) {
           $rows[] = $damageproducts->product_total_cost_rate != '' ? $damageproducts->product_total_cost_rate : '0';

           if ($this->tax_type == 1) {
               $rows[] = $damageproducts->product_cost_igst_percent != '' ? $damageproducts->product_cost_igst_percent : '0';
               $rows[] = $damageproducts->product_cost_igst_amount_with_qty != '' ? $damageproducts->product_cost_igst_amount_with_qty : '0';
           } else {
               $rows[] = $damageproducts->product_cost_cgst_percent != '' ? $damageproducts->product_cost_cgst_percent : '0';
               $rows[] = $damageproducts->product_cost_cgst_amount_with_qty != '' ? $damageproducts->product_cost_cgst_amount_with_qty : '0';
               $rows[] = $damageproducts->product_cost_sgst_percent != '' ? $damageproducts->product_cost_sgst_percent : '0';
               $rows[] = $damageproducts->product_cost_sgst_amount_with_qty != '' ? $damageproducts->product_cost_sgst_amount_with_qty : '0';
               $rows[] = $damageproducts->product_cost_igst_percent != '' ? $damageproducts->product_cost_igst_percent : '0';
               $rows[] = $damageproducts->product_cost_igst_amount_with_qty != '' ? $damageproducts->product_cost_igst_amount_with_qty : '0';
           }

           $rows[] = $damageproducts->product_total_cost_price != '' ? $damageproducts->product_total_cost_price : '0';
           $rows[] = $damageproducts->product_mrp != '' ? $damageproducts->product_mrp : '0';
       }
       $rows[]         =   $damageproducts->product_notes;
       $rows[]         =   $damageproducts->damage_product->damage_types->damage_type;

       return $rows;

    }

    public function query()
    {
        $company_id     =   Auth::user()->company_id;
        $fromDate       =   $this->from_date;
        $toDate         =   $this->to_date;
        $damageTypeids         =   $this->DamageType;
        $product_code         =   $this->product_code;

        $damageproducts = damage_product_detail::select('*')
        ->whereRaw('company_id='.$company_id)
        ->where('deleted_at','=',NULL)
        ->with('damage_product')
        ->with('product.product_features_relationship');


        if($this->from_date!='')
        {
            $damageproducts->whereHas('damage_product',function ($q) use($fromDate,$toDate)
            {
                //$q->whereBetween('damage_date', [$from_date,$to_date]);
                $q->whereRaw("STR_TO_DATE(damage_date,'%d-%m-%Y') between '$fromDate' and '$toDate'");
            });
        }
        else{
            $date      =   date('Y-m-d',strtotime(date("d-m-Y")));

            $damageproducts->whereHas('damage_product',function ($q) use($date)
            {
                $q->whereRaw("STR_TO_DATE(damage_date,'%d-%m-%Y') between '$date' and '$date'");
            });
        }


        if($this->DamageType!='')
        {
            //$damageproducts->where('damage_type_id','=',$this->DamageType);
            $damageproducts->whereHas('damage_product', function($q) use ($damageTypeids)
            {
                $q->whereRaw("FIND_IN_SET('".$damageTypeids."',damage_type_id)");
            });
        }

        if($this->damageproductsearch!='')
        {
            /*$exp                    =   explode('_',$this->damageproductsearch);
            $searchBarcode          =   $exp[0];
            $searchProductName      =   $exp[1];

            if($exp[0]!='')
            {
               $damageproducts->whereRaw("product_system_barcode='".$exp[0]."'");
            }*/
            $damageproducts->whereRaw("product_id='" . $this->damageproductsearch . "'");
        }

        if($this->product_code!='')
        {
            $damageproducts->whereHas('product',function ($q) use($product_code){
                $q->whereRaw("product_code='" . $product_code . "'");
            });
        }

        $damageproducts->get();


        return $damageproducts;
    }
}

