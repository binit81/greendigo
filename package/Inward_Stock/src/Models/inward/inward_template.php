<?php

namespace Retailcore\Inward_Stock\Models\inward;

use Illuminate\Database\Eloquent\Model;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Auth;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Retailcore\Products\Models\product\ProductFeatures;

class inward_template implements WithHeadings
{
    use Exportable;

    public $inward_type = '';

    public function __construct($inward_type,$unique_inward)
    {
        $this->inward_type = $inward_type;
        $this->unique_inward = $unique_inward;

        $inward_type_from_comp = company_profile::where('company_id',Auth::user()->company_id)->select('inward_type','tax_type','tax_title','currency_title','inward_calculation')->first();

        $this->tax_type = $inward_type_from_comp['tax_type'];
        $this->tax_title = $inward_type_from_comp['tax_title'];
        $this->tax_currency = $inward_type_from_comp['currency_title'];
        $this->company_inward_type = $inward_type_from_comp['inward_type'];
        $this->inward_calculation = $inward_type_from_comp['inward_calculation'];
    }

    public function headings(): array
    {
        $inward_type = $this->inward_type;
        $inward_template_header = [];

        $product_features =  ProductFeatures::getproduct_feature('');

        if($this->inward_type == 1)
        {
            $inward_template_header[] = 'Barcode';
            if($this->inward_type != 3)
            {
                $inward_template_header[] = 'Product name';
            }

            if($this->inward_calculation != 3)
            {
                $inward_template_header[] = 'Base price/cost rate';
                $inward_template_header[] = 'Discount percent';
                $inward_template_header[] = 'Scheme percent';

                if ($this->tax_type == 1) {
                    $inward_template_header[] = 'Cost ' . $this->tax_title . ' %';
                } else {
                    $inward_template_header[] = 'Cost gst %';
                }
                $inward_template_header[] = 'Extra charge';
                $inward_template_header[] = 'Profit %';
                $inward_template_header[] = 'Selling price';
                if ($this->tax_type == 1) {
                    $inward_template_header[] = 'Sell ' . $this->tax_title . ' %';
                } else {
                    $inward_template_header[] = 'Sell gst %';
                }
                $inward_template_header[] = 'Offer price';
                $inward_template_header[] = 'Product mrp';
            }

            if($this->unique_inward != 1)
            {
                $inward_template_header[] = 'Batch no';
            }
            $inward_template_header[] = 'Add qty';
            $inward_template_header[] = 'Free qty';
            $inward_template_header[] = 'Mfg date(DD)';
            $inward_template_header[] = 'Mfg month(MM)';
            $inward_template_header[] = 'Mfg year(YYYY)';
            $inward_template_header[] = 'Expiry date(DD)';
            $inward_template_header[] = 'Expiry month(MM)';
            $inward_template_header[] = 'Expiry year(YYYY)';
            if($this->unique_inward != 1)
            {
                $inward_template_header[] = 'Days before product expiry';
                $inward_template_header[] = 'Product description';
                $inward_template_header[] = 'Product code';
                $inward_template_header[] = 'SKU';
                $inward_template_header[] = 'HSN';

                if(isset($product_features) && !empty($product_features))
                {
                    foreach($product_features AS $key=>$value)
                    {
                        $inward_template_header[] = $value['product_features_name'];
                    }
                }

                $inward_template_header[] = 'UQC';
                $inward_template_header[] = 'Alert product qty';
                $inward_template_header[] = 'MOQ';
            }

            return $inward_template_header;
        }
        if($this->inward_type == 2)
        {
            $inward_template_header[] = 'Barcode';
            if($this->inward_type != 3)
            {
                $inward_template_header[] = 'Product name';
            }

            if($this->inward_calculation != 3) {
                $inward_template_header[] = 'Base price/cost rate';
                $inward_template_header[] = 'Discount percent';
                if ($this->tax_type == 1) {
                    $inward_template_header[] = 'Cost ' . $this->tax_title . ' %';
                } else {
                    $inward_template_header[] = 'Cost gst %';
                }
                $inward_template_header[] = 'Extra charge';
                $inward_template_header[] = 'Profit %';
                $inward_template_header[] = 'Selling price';
                if ($this->tax_type == 1) {
                    $inward_template_header[] = 'Sell ' . $this->tax_title . ' %';
                } else {
                    $inward_template_header[] = 'Sell gst %';
                }
                $inward_template_header[] = 'Offer price';
                $inward_template_header[] = 'Product mrp';
            }


            $inward_template_header[] = 'Add qty';
            if($this->unique_inward != 1)
            {
                $inward_template_header[] = 'Product description';
                $inward_template_header[] = 'Product code';
                $inward_template_header[] = 'SKU';
                $inward_template_header[] = 'HSN';
                if(isset($product_features) && !empty($product_features))
                {
                    foreach($product_features AS $key=>$value)
                    {
                        $inward_template_header[] = $value['product_features_name'];
                    }
                }
                $inward_template_header[] = 'UQC';
                $inward_template_header[] = 'Days before product expiry';
                $inward_template_header[] = 'Alert product qty';
                $inward_template_header[] = 'MOQ';
            }

            return $inward_template_header;

        }
    }





}

