<?php

namespace Retailcore\Inward_Stock\Models\inward;

use Illuminate\Database\Eloquent\Model;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Auth;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Retailcore\Inward_Stock\Models\inward\inward_stock;
use Retailcore\Products\Models\product\price_master;

use Retailcore\Inward_Stock\Models\inward\inward_product_detail;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Retailcore\Products\Models\product\product;
use Retailcore\Products\Models\product\ProductFeatures;

class inward_pricemaster_report_excel implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public $from_date = '';
    public $to_date = '';
    public $batch_no = '';
    public $product_name = '';

    public function __construct($barcode, $product_name)
    {
        $this->barcode = $barcode;
        $this->product_name = $product_name;

        $inward_type_from_comp = company_profile::where('company_id',Auth::user()->company_id)->select('inward_type','tax_type','tax_title','currency_title','inward_calculation')->first();

        $this->inward_type = 1;
        if(isset($inward_type_from_comp) && !empty($inward_type_from_comp) && $inward_type_from_comp['inward_type'] != '')
        {
            $this->inward_type = $inward_type_from_comp['inward_type'];
        }
        $this->tax_type = $inward_type_from_comp['tax_type'];
        $this->tax_title = $inward_type_from_comp['tax_title'];
        $this->currency_title = $inward_type_from_comp['currency_title'];
        $this->inward_calculation = $inward_type_from_comp['inward_calculation'];

        $this->product_features = ProductFeatures::getproduct_feature('');

        $this->page_url = ProductFeatures::get_current_page_url();

        $this->show_dynamic_feature = array();
    }

    public function headings(): array
    {

        $inward_type = $this->inward_type;
        $tax_currency = '₹';
        $tax_title = 'GST';

        if($this->tax_type == 1)
        {
            $tax_title = $this->tax_title;
            $tax_currency =$this->currency_title;
        }
        $price_master_header = [];



        if($inward_type == 1) {

            $price_master_header[] = 'Batch No';
            $price_master_header[] = 'Barcode';
            $price_master_header[] = 'Product Name';

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
                            $dynamic_header .= $price_master_header[] = $feature_value['product_features_name'];
                        }
                    }
                }
            }
            $price_master_header[] = 'UQC';
            $price_master_header[] = 'Qty';
            if($this->inward_calculation != 3) {
                $price_master_header[] = 'Selling Price ' . $tax_currency;
                $price_master_header[] = 'Selling ' . $tax_title . ' %';
                $price_master_header[] = 'Selling ' . $tax_title . ' ' . $tax_currency;
                $price_master_header[] = 'Offer Price ' . $tax_currency;
                $price_master_header[] = 'Product MRP ' . $tax_currency;
                $price_master_header[] = 'Wholesaler Price ' . $tax_currency;
            }

        }else
        {
            $price_master_header[] = 'Barcode';
            $price_master_header[] = 'Product Name';
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
                            $dynamic_header .= $price_master_header[] = $feature_value['product_features_name'];
                        }
                    }
                }
            }
            $price_master_header[] = 'UQC';
            $price_master_header[] = 'Qty';
            if($this->inward_calculation != 3) {
                $price_master_header[] = 'Selling Price ' . $tax_currency;
                $price_master_header[] = 'Selling ' . $tax_title . ' %';
                $price_master_header[] = 'Selling ' . $tax_title . ' ' . $tax_currency;
                $price_master_header[] = 'Offer Price ' . $tax_currency;
                $price_master_header[] = 'Product MRP ' . $tax_currency;
                $price_master_header[] = 'Wholesaler Price ' . $tax_currency;
            }
        }
        return $price_master_header;
    }

    public function map($price_master_excel): array
    {
        $count = '';

        $rows = [];

        $barcode = '';

        $inward_type = $this->inward_type;

        if ($price_master_excel['product']['supplier_barcode'] != '') {
            $barcode = $price_master_excel['product']['supplier_barcode'];
        } else {
            $barcode = $price_master_excel['product']['product_system_barcode'];
        }


        $uqc_name = '';
        if($price_master_excel['product']['uqc_id'] != '' && $price_master_excel['product']['uqc_id'] != null && $price_master_excel['product']['uqc_id'] != 0)
        {
            $uqc_name = $price_master_excel['product']['uqc']['uqc_shortname'];
        }



        $feature_show_val = array();


        if($this->show_dynamic_feature != '')
        {
            foreach($this->show_dynamic_feature AS $fea_key=>$fea_val)
            {
                $feature_data_id = $price_master_excel['product']['product_features_relationship'][$fea_key];

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

        if($inward_type == 1){
            $rows[] = $price_master_excel->batch_no;
        }
        $rows[] = $barcode;
        $rows[] = $price_master_excel->product->product_name;

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
        $rows[] = $price_master_excel->product_qty != '' ?$price_master_excel->product_qty : '0';
        if($this->inward_calculation != 3) {
            $rows[] = $price_master_excel->sell_price != '' ? $price_master_excel->sell_price : '0';
            $rows[] = $price_master_excel->selling_gst_percent != '' ? $price_master_excel->selling_gst_percent : '0';
            $rows[] = $price_master_excel->selling_gst_amount != '' ? $price_master_excel->selling_gst_amount : '0';
            $rows[] = $price_master_excel->offer_price != '' ? $price_master_excel->offer_price : '0';
            $rows[] = $price_master_excel->product_mrp != '' ? $price_master_excel->product_mrp : '0';
            $rows[] = $price_master_excel->wholesaler_price != '' ? $price_master_excel->wholesaler_price : '0';
        }

        return $rows;
    }


    public function query()
    {

        $barcode = $this->barcode;
        $product_name = $this->product_name;


        $price_master_excel = price_master::query()->where('company_id', Auth::user()->company_id)
            ->where('deleted_at', '=', NULL)
            ->with('inward_stock')
            ->with('product');

        if ($barcode != '') {

            $price_master_excel->whereHas('product',function ($q) use($barcode){
                $q->where('product_system_barcode', 'like', '%'.$barcode.'%');
                $q->orWhere('supplier_barcode', 'like', '%'.$barcode.'%');
            });
        }
        if ($product_name != '') {
            $price_master_excel->whereHas('product',function ($q) use($product_name){
                $q->where('product_name', 'like', '%'.$product_name.'%');
            });

        }
        $price_master_excel = $price_master_excel->orderBy('updated_at', 'desc');
        return $price_master_excel;


    }
}

