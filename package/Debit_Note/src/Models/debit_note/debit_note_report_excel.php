<?php

namespace Retailcore\Debit_Note\Models\debit_note;


use Illuminate\Database\Eloquent\Model;


use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Auth;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Retailcore\Products\Models\product\product;
use Retailcore\Products\Models\product\ProductFeatures;

class debit_note_report_excel implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public $from_date = '';
    public $to_date = '';
    public $debit_no = '';
    public $product_code_filter = '';

    public function __construct($from_date,$to_date,$debit_no,$product_code_filter) {
        $this->from_date = $from_date;
        $this->to_date = $to_date;
        $this->debit_no = $debit_no;
        $this->product_code_filter = $product_code_filter;

        $this->product_features = ProductFeatures::getproduct_feature('');
        $this->page_url = ProductFeatures::get_current_page_url();
        $this->show_dynamic_feature = array();

        $inward_type_from_comp = company_profile::where('company_id',Auth::user()->company_id)->select('inward_type','tax_type','tax_title','currency_title','inward_calculation')->first();

        $this->tax_type = $inward_type_from_comp['tax_type'];
        $this->tax_title = $inward_type_from_comp['tax_title'];
        $this->inward_calculation = $inward_type_from_comp['inward_calculation'];
    }

    public function headings(): array
    {
        $tax_name = 'GST';
        if($this->tax_type == 1)
        {
            $tax_name = $this->tax_title;
        }

        $debit_note_product = [];
        $debit_note_product[] = 'Debit Receipt No.';
        $debit_note_product[] = 'Product Name';
        $debit_note_product[] = 'Product Code';

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
                        $dynamic_header .= $debit_note_product[] = $feature_value['product_features_name'];
                    }
                }
            }
        }


        $dynamic_header;

        $debit_note_product[] = 'UQC';
        $debit_note_product[] = 'Supplier Name	';
        $debit_note_product[] = 'Return Qty';
        if($this->inward_calculation != 3) {
            $debit_note_product[] = 'Total Cost Rate';
            $debit_note_product[] = 'Total ' . $tax_name;
            $debit_note_product[] = 'Debit Amt';
        }
        $debit_note_product[] = 'Remarks';

        return $debit_note_product;
    }

    public function map($debit_note_result): array
    {

        $uqc_name = '';

        if($debit_note_result['product']['uqc_id'] != '' && $debit_note_result['product']['uqc_id'] != null && $debit_note_result['product']['uqc_id'] != 0)
        {
            $uqc_name = $debit_note_result['product']['uqc']['uqc_shortname'];
        }

        $product_code = '';

        if($debit_note_result['product']['product_code'] != '' && $debit_note_result['product']['product_code'] != null )
        {
            $product_code = $debit_note_result['product']['product_code'];
        }

        $feature_show_val = array();
        if($this->show_dynamic_feature != '')
        {
            foreach($this->show_dynamic_feature AS $fea_key=>$fea_val)
            {
                $feature_data_id = $debit_note_result['product']['product_features_relationship'][$fea_key];

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
        $rows[] = $debit_note_result->debit_note->debit_no;
        $rows[] = $debit_note_result->product->product_name;
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
        $rows[] = $debit_note_result->debit_note->supplier_gstdetail->supplier_company_info->supplier_first_name;
        $rows[] = $debit_note_result->return_qty;
        if($this->inward_calculation != 3) {
            $rows[] = $debit_note_result->total_cost_rate;
            $rows[] = $debit_note_result->total_gst;
            $rows[] = $debit_note_result->total_cost_price;
        }
        $rows[] = $debit_note_result->remarks;
        return $rows;
    }



    public function query()
    {

        $from_date   =   $this->from_date;
        $to_date   =   $this->to_date;
        $debit_no =   $this->debit_no;
        $product_code_filter =   $this->product_code_filter;

        $debit_note_result =  debit_product_detail::where('company_id',Auth::user()->company_id)
            ->whereNull('deleted_at')
            ->with('debit_note')
            ->with('product.product_features_relationship');

        if($from_date !='' && $to_date !='')
        {
//            $debit_note_result->whereHas('debit_note',function ($q) use($from_date,$to_date)
//            {
//                $q->whereBetween('debit_date', [$from_date,$to_date]);
//            });

            $start_date = date("Y-m-d",strtotime($from_date));
            $end_date  =  date("Y-m-d",strtotime($to_date));
            $debit_note_result->whereHas('debit_note',function ($q) use($start_date,$end_date)
            {
                //$q->whereBetween('debit_date', [$from_date,$to_date]);
                $q->whereRaw("STR_TO_DATE(debit_date,'%d-%m-%Y') between '$start_date' and '$end_date'");
            });

        }
        if($debit_no !='')
        {
            $debit_note_result->whereHas('debit_note',function ($q) use($debit_no)
            {
                $q->whereRaw("debit_notes.debit_no='" . $debit_no . "'");
            });
        } if($product_code_filter !='')
        {
            $debit_note_result->whereHas('product',function ($q) use($product_code_filter)
            {
                $q->whereRaw("product_code='" . $product_code_filter . "'");
            });
        }

        return $debit_note_result;
    }

}
