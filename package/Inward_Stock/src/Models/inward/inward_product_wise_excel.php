<?php

namespace Retailcore\Inward_Stock\Models\inward;
use App\Providers\AppServiceProvider;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Auth;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Retailcore\Inward_Stock\Models\inward\inward_stock;
use Retailcore\Inward_Stock\Models\inward\inward_product_detail;
use Config;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Retailcore\Products\Models\product\product;
use Retailcore\Products\Models\product\ProductFeatures;

class inward_product_wise_excel  implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;


    public $from_date = '';
    public $to_date = '';
    public $barcode = '';
    public $product_name = '';
    public $batch_no = '';
    public $invoice_no = '';
    public $product_code = '';

    public function __construct($from_date,$to_date,$barcode,$product_name,$batch_no,$invoice_no,$product_code)
    {
        $this->from_date = $from_date;
        $this->to_date = $to_date;
        $this->barcode = $barcode;
        $this->product_name = $product_name;
        $this->batch_no = $batch_no;
        $this->invoice_no = $invoice_no;
        $this->product_code = $product_code;

        $inward_type_from_comp = company_profile::where('company_id',Auth::user()->company_id)->select('inward_type','tax_type','tax_title','currency_title','inward_calculation')->first();

        $this->inward_type = 1;
        $this->currency_title = '₹';
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
        $tax_type = $this->tax_type;
        $tax_title = $this->tax_title;
        $currency_title = $this->currency_title;

        $return_header_array = [];


        if($inward_type == 1)
        {
            $return_header_array[] = 'Barcode';
            $return_header_array[] = 'Invoice No.';
            $return_header_array[] = 'Supplier Name';
            $return_header_array[] = 'Batch No.';
            $return_header_array[] = 'Inward Date';
            $return_header_array[] = 'Product Name';
            $return_header_array[] = 'Product Code';

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
                            $dynamic_header .= $return_header_array[] = $feature_value['product_features_name'];
                        }
                    }
                }
            }


            $dynamic_header;
            $return_header_array[] = 'UQC';
            if($this->inward_calculation != 3) {
                if ($tax_type == 1) {
                    $return_header_array[] = 'Cost Rate ' . $currency_title . '(Without ' . $tax_title . ')';
                    $return_header_array[] = "Cost " . $tax_title . ' ' . $currency_title;
                    $return_header_array[] = 'Cost Price ' . $currency_title . '(With ' . $tax_title . ')';
                }
                if ($tax_type == 2) {
                    $return_header_array[] = 'Cost Rate ₹(Without GST)';
                    $return_header_array[] = 'Cost IGST ₹';
                    $return_header_array[] = 'Cost CGST ₹';
                    $return_header_array[] = 'Cost SGST ₹';
                    $return_header_array[] = 'Cost Price ₹(With GST)';
                }
            }

            $return_header_array[] = 'Qty';
            $return_header_array[] = 'Free Qty';
            $return_header_array[] = 'Pending Return Qty';
            if($this->inward_calculation != 3) {
                $return_header_array[] = 'Total Cost Rate';
                if ($tax_type == 1) {
                    $return_header_array[] = "Total " . $tax_title . " %";
                    $return_header_array[] = "Total " . $tax_title . ' ' . $currency_title;
                }
                if ($tax_type == 2) {
                    $return_header_array[] = 'Total CGST %';
                    $return_header_array[] = 'Total CGST ₹';
                    $return_header_array[] = 'Total SGST %';
                    $return_header_array[] = 'Total SGST ₹';
                    $return_header_array[] = 'Total IGST %';
                    $return_header_array[] = 'Total IGST ₹';
                }
                $return_header_array[] = 'Total Cost Price ' . $currency_title;
                $return_header_array[] = 'Profit ' . $currency_title;
                $return_header_array[] = 'Sell Price ' . $currency_title;
                $return_header_array[] = 'Selling GST ' . $currency_title;
                $return_header_array[] = 'Offer Price ' . $currency_title;
                $return_header_array[] = 'Product MRP ' . $currency_title;
            }
            $return_header_array[] = 'Mfg Date';
            $return_header_array[] = 'Expiry Date';

            return $return_header_array;
        }
        if($inward_type == 2)
        {

                $return_header_array[] = 'Barcode';
                $return_header_array[] = 'Invoice No.';
                $return_header_array[] = 'Supplier Name';
                $return_header_array[] = 'Inward Date';
                $return_header_array[] = 'Product Name';
                $return_header_array[] = 'Product Code';
            $dynamic_header = '';

            if (isset($this->product_features) && $this->product_features != '' && !empty($this->product_features))
            {
                foreach ($this->product_features AS $feature_key => $feature_value)
                {
                    if ($feature_value['show_feature_url'] != '' && $feature_value['show_feature_url'] != 'NULL' && $feature_value['show_feature_url'] != null)
                    {
                        $search =  $this->page_url;

                        if(strstr($feature_value['show_feature_url'],$search))
                        {
                            $this->show_dynamic_feature[$feature_value['html_id']] = $feature_value['product_features_id'];
                            $dynamic_header .= $return_header_array[] = $feature_value['product_features_name'];
                        }
                    }
                }
            }

            $dynamic_header;
                $return_header_array[] = 'UQC';

            if($this->inward_calculation != 3) {
                if ($tax_type == 1) {
                    $return_header_array[] = 'Cost Rate ' . $currency_title . '(Without ' . $tax_title . ')';
                    $return_header_array[] = "Cost " . $tax_title . ' ' . $currency_title;
                    $return_header_array[] = 'Cost Price ' . $currency_title . '(With ' . $tax_title . ')';
                }
                if ($tax_type == 2) {
                    $return_header_array[] = 'Cost Rate ₹(Without GST)';
                    $return_header_array[] = 'Cost IGST ₹';
                    $return_header_array[] = 'Cost CGST ₹';
                    $return_header_array[] = 'Cost SGST ₹';
                    $return_header_array[] = 'Cost Price ₹(With GST)';
                }
            }

            $return_header_array[] = 'Qty';
            $return_header_array[] = 'Pending Return Qty';
            if($this->inward_calculation != 3) {
                $return_header_array[] = 'Total Cost Rate';

                if ($tax_type == 1) {
                    $return_header_array[] = "Total " . $tax_title . " %";
                    $return_header_array[] = "Total " . $tax_title . ' ' . $currency_title;
                }
                if ($tax_type == 2) {
                    $return_header_array[] = 'Total CGST %';
                    $return_header_array[] = 'Total CGST ₹';
                    $return_header_array[] = 'Total SGST %';
                    $return_header_array[] = 'Total SGST ₹';
                    $return_header_array[] = 'Total IGST %';
                    $return_header_array[] = 'Total IGST ₹';
                }

                $return_header_array[] = 'Total Cost Price ' . $currency_title;
                $return_header_array[] = 'Profit ' . $currency_title;
                $return_header_array[] = 'Sell Price ' . $currency_title;
                $return_header_array[] = 'Selling GST ' . $currency_title;
                $return_header_array[] = 'Offer Price ' . $currency_title;
                $return_header_array[] = 'Product MRP ' . $currency_title;
            }
            $return_header_array[] = 'Mfg Date';
            $return_header_array[] = 'Expiry Date';

                return $return_header_array;
        }



    }

    public function map($product_wise_report): array
    {
        $count = '';

        $rows    = [];

        $barcode = '';

        $inward_type = $this->inward_type;

        $tax_type = $this->tax_type;
        $tax_title = $this->tax_title;
        $currency_title = $this->currency_title;


        if($product_wise_report['product_detail']['supplier_barcode'] != '')
        {
            $barcode =  $product_wise_report['product_detail']['supplier_barcode'];
        }
        else
        {
            $barcode = $product_wise_report['product_detail']['product_system_barcode'];
        }

        $uqc_name = '';

        if($product_wise_report['product_detail']['uqc_id'] != '' && $product_wise_report['product_detail']['uqc_id'] != null && $product_wise_report['product_detail']['uqc_id'] != 0)
        {
            $uqc_name = $product_wise_report['product_detail']['uqc']['uqc_shortname'];
        }

        $product_code = '';

        if($product_wise_report['product_detail']['product_code'] != '' && $product_wise_report['product_detail']['product_code'] != null )
        {
            $product_code = $product_wise_report['product_detail']['product_code'];
        }


        $feature_show_val = array();


        if($this->show_dynamic_feature != '')
        {
            foreach($this->show_dynamic_feature AS $fea_key=>$fea_val)
            {
                $feature_data_id = $product_wise_report['product_detail']['product_features_relationship'][$fea_key];

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

        $supplier_company_name = '';
        if(isset($product_wise_report['inward_stock']['supplier_gst_id']) && $product_wise_report['inward_stock']['supplier_gst_id'] != '')
        {
            $supplier_company_name = $product_wise_report['inward_stock']['supplier_gstdetail']['supplier_company_info']['supplier_company_name'];
        }


        $rows[] = $barcode;
        $rows[] = $product_wise_report->inward_stock->invoice_no;
        $rows[] = $supplier_company_name;
        if($inward_type == 1)
        {
            $rows[] = $product_wise_report->batch_no;
        }
        $rows[] = $product_wise_report->inward_stock->inward_date;
        $rows[] = $product_wise_report->product_detail->product_name;
        $rows[] = $product_code;

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
        if($this->inward_calculation != 3) {
            $rows[] = $product_wise_report->cost_rate != '' ? $product_wise_report->cost_rate : '0';

            if ($tax_type == 1) {
                $rows[] = $product_wise_report->cost_igst_amount != '' ? $product_wise_report->cost_igst_amount : '0';
            }
            if ($tax_type == 2) {
                $rows[] = $product_wise_report->cost_igst_amount != '' ? $product_wise_report->cost_igst_amount : '0';
                $rows[] = $product_wise_report->cost_cgst_amount != '' ? $product_wise_report->cost_cgst_amount : '0';
                $rows[] = $product_wise_report->cost_sgst_amount != '' ? $product_wise_report->cost_sgst_amount : '0';
            }

            $rows[] = $product_wise_report->cost_price != '' ? $product_wise_report->cost_price : '0';
        }
        $rows[] = $product_wise_report->product_qty != '' ? $product_wise_report->product_qty : '0';
        if($inward_type == 1)
        {
            $rows[] = $product_wise_report->free_qty != '' ? $product_wise_report->free_qty : '0';
        }
        $rows[] = $product_wise_report->pending_return_qty != '' ? $product_wise_report->pending_return_qty : '0';
        if($this->inward_calculation != 3) {
            $rows[] = $product_wise_report->total_cost_rate_with_qty != '' ? $product_wise_report->total_cost_rate_with_qty : '0';

            if ($tax_type == 1) {
                $rows[] = $product_wise_report->cost_igst_percent != '' ? $product_wise_report->cost_igst_percent : '0';
                $rows[] = $product_wise_report->total_igst_amount_with_qty != '' ? $product_wise_report->total_igst_amount_with_qty : '0';
            }
            if ($tax_type == 2) {
                $rows[] = $product_wise_report->cost_cgst_percent != '' ? $product_wise_report->cost_cgst_percent : '0';
                $rows[] = $product_wise_report->total_cgst_amount_with_qty != '' ? $product_wise_report->total_cgst_amount_with_qty : '0';
                $rows[] = $product_wise_report->cost_sgst_percent != '' ? $product_wise_report->cost_sgst_percent : '0';
                $rows[] = $product_wise_report->total_sgst_amount_with_qty != '' ? $product_wise_report->total_sgst_amount_with_qty : '0';
                $rows[] = $product_wise_report->total_igst_amount_with_qty != '' ? $product_wise_report->total_igst_amount_with_qty : '0';
                $rows[] = $product_wise_report->cost_igst_percent != '' ? $product_wise_report->cost_igst_percent : '0';
            }

            $rows[] = $product_wise_report->total_cost != '' ? $product_wise_report->total_cost : '0';
            $rows[] = $product_wise_report->profit_amount != '' ? $product_wise_report->profit_amount : '0';
            $rows[] = $product_wise_report->sell_price != '' ? $product_wise_report->sell_price : '0';
            $rows[] = $product_wise_report->selling_gst_amount != '' ? $product_wise_report->selling_gst_amount : '0';
            $rows[] = $product_wise_report->offer_price != '' ? $product_wise_report->offer_price : '0';
            $rows[] = $product_wise_report->product_mrp != '' ? $product_wise_report->product_mrp : '0';
        }
        $rows[] = $product_wise_report->mfg_date != '' ? $product_wise_report->mfg_date : '';
        $rows[] = $product_wise_report->expiry_date != '' ? $product_wise_report->expiry_date : '';
        return $rows;
    }



    public function query()
    {
        $from_date   =   $this->from_date;
        $to_date   =   $this->to_date;
        $barcode =   $this->barcode;
        $product_name =   $this->product_name;
        $batch_no =   $this->batch_no;
        $invoice_no =   $this->invoice_no;
        $product_code =   $this->product_code;

        $sort_by = 'inward_product_detail_id';
        $sort_type = 'desc';
        $product_wise_report = inward_product_detail::query()
            ->where('company_id',Auth::user()->company_id)
            ->where('deleted_at','=',NULL)
            ->with('inward_stock')
            ->with('product_detail.product_features_relationship')
            ->orderBy($sort_by,$sort_type);

        if($from_date !='' && $to_date !='')
        {
            $start_date = date("Y-m-d",strtotime($from_date));
            $end_date  =  date("Y-m-d",strtotime($to_date));
            $product_wise_report->whereHas('inward_stock',function($q) use($start_date,$end_date)
            {
                //$q->whereBetween('inward_date',[$from_date,$to_date]);
                $q->whereRaw("STR_TO_DATE(inward_date,'%d-%m-%Y') between '$start_date' and '$end_date'");
            });
        }

        if($barcode !='')
        {
            $product_wise_report->whereHas('product_detail',function ($q) use ($barcode)
                {
                    $q->where('product_system_barcode','=',$barcode);
                    $q->orWhere('supplier_barcode','=',$barcode);
                });
        }
        if($product_name !='')
        {
            $product_wise_report->whereHas('product_detail',function ($q) use ($product_name)
                {
                    $q->where('product_name','LIKE','%'.$product_name.'%');
                });
        }

        if ($batch_no != '')
        {
            $product_wise_report->where('batch_no','=', $batch_no)->where('batch_no','!=',NULL);
        }

        if ($invoice_no != '')
        {
            $product_wise_report->whereHas('inward_stock',function ($q) use ($invoice_no)
            {
                $q->where('invoice_no',$invoice_no);
            });
        }

        if($product_code !='')
        {
            $product_wise_report->whereHas('product_detail',function ($q) use ($product_code)
            {
                $q->where('product_code','=',$product_code);
            });
        }

        return $product_wise_report;
    }
}
