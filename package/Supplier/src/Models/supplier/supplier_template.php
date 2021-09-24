<?php


namespace Retailcore\Supplier\Models\supplier;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Auth;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Retailcore\Company_Profile\Models\company_profile\company_profile;

class supplier_template implements WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        $supplier_header = [];

        $inward_type_from_comp = company_profile::where('company_id',Auth::user()->company_id)
            ->select('inward_type','tax_type','tax_title','currency_title')->first();

        $tax_name = "GSTIN";
        if(isset($inward_type_from_comp) && !empty($inward_type_from_comp) && $inward_type_from_comp['tax_type'] != '' && $inward_type_from_comp['tax_type'] == 1)
        {
            $tax_name = $inward_type_from_comp['tax_title'];
        }


        $supplier_header[] = 'Company Name';
        $supplier_header[] = 'Pan No.';
        $supplier_header[] = 'Supplier First Name';
        $supplier_header[] = 'Supplier Last Name';
        $supplier_header[] = 'Note';
        $supplier_header[] = 'Due Days';
        $supplier_header[] = 'Due Date';
        $supplier_header[] = 'Shop no.,Building,Street etc.';
        $supplier_header[] = 'Area';
        $supplier_header[] = 'Pin / Zip Code';
        $supplier_header[] = 'City / Town';
        $supplier_header[] = 'State';
        $supplier_header[] = 'Country';
        $supplier_header[] = 'Phone No.';
        $supplier_header[] = 'Bank Name';
        $supplier_header[] = 'Bank Account Name';
        $supplier_header[] = 'Bank Account No.';
        $supplier_header[] = 'Bank IFSC Code';
        $supplier_header[] = $tax_name;
        $supplier_header[] = $tax_name .' Address';
        $supplier_header[] = $tax_name .' Area';
        $supplier_header[] = $tax_name .' Zipcode';
        $supplier_header[] = $tax_name .' City';
        $supplier_header[] = 'Supplier Contact First Name';
        $supplier_header[] = 'Supplier Contact Last Name';
        $supplier_header[] = 'Designation';
        $supplier_header[] = 'Email Id';
        $supplier_header[] = 'Day of Birth(DD)';
        $supplier_header[] = 'Month of Birth(MM)';
        $supplier_header[] = 'Year of Birth(YYYY)';
        $supplier_header[] = 'Supplier Contact Mobile No.';
        $supplier_header[] = 'Supplier Contact Whatsapp No.';


        return $supplier_header;
    }
}
