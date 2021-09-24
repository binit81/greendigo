<?php

namespace Retailcore\Customer\Models\Customer;
use Retailcore\Company_Profile\Models\company_profile\company_profile;

use App\country;
use Illuminate\Database\Eloquent\Model;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Auth;
use Maatwebsite\Excel\Concerns\WithHeadings;

class customer_template implements WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        $customer_header = [];

        $inward_type_from_comp = company_profile::where('company_id',Auth::user()->company_id)
            ->select('inward_type','tax_type','tax_title','currency_title')->first();

        $tax_name = "GSTIN";
        if(isset($inward_type_from_comp) && !empty($inward_type_from_comp) && $inward_type_from_comp['tax_type'] != '' && $inward_type_from_comp['tax_type'] == 1)
        {
            $tax_name = $inward_type_from_comp['tax_title'];
        }


            $customer_header[] = 'Customer Name';
            $customer_header[] = 'Gender';
            $customer_header[] = 'Customer Mobile Country Code';
            $customer_header[] = 'Mobile No.';
            $customer_header[] = 'Email';
            $customer_header[] = $tax_name;
            $customer_header[] = 'Day of Birth(DD)';
            $customer_header[] = 'Month of Birth(MM)';
            $customer_header[] = 'Year of Birth(YYYY)';
            $customer_header[] = 'Flat no.,Building,Street etc.';
            $customer_header[] = 'Area';
            $customer_header[] = 'City / Town';
            $customer_header[] = 'Pin / Zip Code';
            $customer_header[] = 'State / Region';
            $customer_header[] = 'Country';
            $customer_header[] = 'Credit Period(days)';
            $customer_header[] = 'How did you came to know about us?';
            $customer_header[] = 'Note';

            return $customer_header;
    }

}
