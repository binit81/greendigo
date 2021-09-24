<?php

namespace Retailcore\Products\Models\product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Auth;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Retailcore\Company_Profile\Models\company_profile\company_profile;

class product_template implements WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        $product_header = [];

        $tax_type_company = company_profile::where('company_id',Auth::user()->company_id)
            ->select('tax_type','tax_title','currency_title','product_calculation')->first();

        $tax_name = "GST";
        $tax_currency = "₹";
        if(isset($tax_type_company) && !empty($tax_type_company) && $tax_type_company['tax_type'] != '' && $tax_type_company['tax_type'] == 1)
        {
            $tax_name = $tax_type_company['tax_title'];
            $tax_currency = $tax_type_company['currency_title'];
        }

        $product_features =  ProductFeatures::getproduct_feature('');
        $product_header[] = 'Supplier Barcode';
        $product_header[] = 'Product Name';

        if($tax_type_company['product_calculation'] != 3) {
            $product_header[] = 'Cost Rate';
            $product_header[] = 'Cost ' . $tax_name . ' %';
            $product_header[] = 'Extra Charge';
            $product_header[] = 'Profit %';
            $product_header[] = 'Selling Rate';
            $product_header[] = 'Sell ' . $tax_name . ' %';
            $product_header[] = 'Offer Price';
            $product_header[] = 'Product MRP';
            $product_header[] = 'Wholesale Price';
        }


        $product_header[] = 'SKU';
        $product_header[] = 'Product Code';
        $product_header[] = 'Product Description';
        $product_header[] = 'HSN';
        $product_header[] = 'UQC';
        $product_header[] = 'Alert Before Product Expiry(Days)';
        $product_header[] = 'Low Stock Alert';
        $product_header[] = 'MOQ';
        $product_header[] = 'Note';

        if(isset($product_features) && !empty($product_features))
        {
            foreach($product_features AS $key=>$value)
            {
                $product_header[] = $value['product_features_name'];
            }
        }




        return $product_header;
    }
}
