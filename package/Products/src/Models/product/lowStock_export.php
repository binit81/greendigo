<?php

namespace Retailcore\Products\Models\product;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Auth;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Retailcore\Inward_Stock\Models\inward\inward_stock;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

use DB;
use App\User;
use App\state;
use App\country;

class lowStock_export implements FromQuery, WithHeadings, WithMapping
{

   use Exportable;
    public $product_name = '';
    public $barcode = '';

    public $uqc_id='';

    public function __construct($product_name,$barcode,$uqc_id) {
        $this->product_name = $product_name;
        $this->barcode = $barcode;
        $this->uqc_id=$uqc_id;

        $this->product_features = ProductFeatures::getproduct_feature('');
        $this->page_url = ProductFeatures::get_current_page_url();
        $this->show_dynamic_feature = array();
    }

    public function headings(): array
    {
        $low_stock_report_header = [];
        $low_stock_report_header[] = 'Barcode';
        $low_stock_report_header[] = 'Product Name';
        $low_stock_report_header[] = 'Pcode';

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
                        $dynamic_header .= $low_stock_report_header[] = $feature_value['product_features_name'];
                    }
                }
            }
        }
        $dynamic_header;

        $low_stock_report_header[] = 'UQC';
        $low_stock_report_header[] = 'In Stock';
        $low_stock_report_header[] = 'Stock Alert';

        return $low_stock_report_header;

    }

    public function map($lowStock): array
   {
       $feature_show_val = array();
       if($this->show_dynamic_feature != '')
       {
           foreach($this->show_dynamic_feature AS $fea_key=>$fea_val)
           {
               $feature_data_id = $lowStock['product_features_relationship'][$fea_key];

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
        $rows    = [];

        $rows[]         =   $lowStock->product_system_barcode;
        $rows[]         =   $lowStock->product_name;
        $rows[]         =   $lowStock->product_code;
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
        $rows[]         =   $lowStock['uqc']['uqc_name'];
        $rows[]         =   $lowStock->totalstock;
        $rows[]         =   $lowStock->alert_product_qty;

        return $rows;

    }

    public function query()
    {
        $lquery = product::select('*')->where('company_id', Auth::User()->company_id);

        if($this->product_name!='')
        {
            $lquery->whereRaw("product_name LIKE '%$this->product_name%'");
        }

        if($this->barcode!='')
        {
            $lquery->whereRaw("product_system_barcode='$this->barcode' or supplier_barcode='$this->barcode'");
        }

        /*if($this->brand_id!=0)
        {
            $lquery->whereRaw("brand_id='$this->brand_id'");
        }

        if($this->category_id!=0)
        {
            $lquery->whereRaw("category_id='$this->category_id'");
        }

        if($this->subcategory_id!=0)
        {
            $lquery->whereRaw("subcategory_id='$this->subcategory_id'");
        }

        if($this->colour_id!=0)
        {
            $lquery->whereRaw("colour_id='$this->colour_id'");
        }

        if($this->size_id!=0)
        {
            $lquery->whereRaw("size_id='$this->size_id'");
        }*/

        if($this->uqc_id!=0)
        {
            $lquery->whereRaw("uqc_id='$this->uqc_id'");
        }


        $lowStock     =   $lquery->where('products.alert_product_qty', '>' ,DB::raw("(SELECT SUM(price_masters.product_qty) FROM price_masters WHERE price_masters.product_id = products.product_id)"))
        ->withCount([
            'price_master as totalstock' => function($fquery)  {
                $fquery->select(DB::raw('SUM(product_qty)'));
            }
        ])->with('uqc','product_features_relationship');
        // dd($lowStock); exit;
        // echo '<pre>';
        // print_r($users); exit;
        return $lowStock;
    }
}

