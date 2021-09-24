<?php

namespace Retailcore\SalesReport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Auth;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Retailcore\Company_Profile\Models\company_profile\company_profile;

class bill_template implements WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        $bill_header = [];

        $tax_type_company = company_profile::where('company_id',Auth::user()->company_id)
            ->select('tax_type','tax_title','currency_title','bill_excel_column_check','bill_calculation')->first();

        $tax_name = "GST";
        $tax_currency = "₹";
        if(isset($tax_type_company) && !empty($tax_type_company) && $tax_type_company['tax_type'] != '' && $tax_type_company['tax_type'] == 1)
        {
            $tax_name = $tax_type_company['tax_title'];
            $tax_currency = $tax_type_company['currency_title'];
        }

        if($tax_type_company['bill_excel_column_check']==1)
        {
            $check_column_name =  'Product Code';
        }
        else
        {
            $check_column_name =  'Barcode';
        }

        $bill_header[] = 'Order ID/PO NO';
        $bill_header[] = 'Date';
        $bill_header[] = 'Month';
        $bill_header[] = 'Year';
        $bill_header[] = 'Name';
        $bill_header[] = 'CONTACT NO';
        $bill_header[] = 'City';
        $bill_header[] = 'State';
        $bill_header[] =  $check_column_name;
        $bill_header[] = 'Order Qty';
        if($tax_type_company['bill_calculation']==1)
        {
           $bill_header[] = 'Price';
        }        
        $bill_header[] = 'Portal';
        $bill_header[] = 'GST NO';

        return $bill_header;
    }
}
