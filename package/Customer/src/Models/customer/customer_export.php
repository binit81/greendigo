<?php

namespace Retailcore\Customer\Models\customer;

use Illuminate\Database\Eloquent\Model;


use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Auth;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use App\country;
use Retailcore\Customer\Models\customer\customer_address_detail;
use Retailcore\Customer_Source\Models\customer_source\customer_source;



class customer_export implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;
    public $query = '';

    public function __construct($query) {

       $this->query = $query;

        $inward_type_from_comp = company_profile::where('company_id',Auth::user()->company_id)->select('tax_type','tax_title','currency_title')->first();

        $this->tax_type = $inward_type_from_comp['tax_type'];
        $this->tax_title = $inward_type_from_comp['tax_title'];
    }

    public function headings(): array
    {
        $tax_name = 'GSTIN';
        if($this->tax_type == 1)
        {
            $tax_name = $this->tax_title;
        }

        $customer_header = [];
        $customer_header[] = 'Name';
        $customer_header[] = 'Gender';
        $customer_header[] = 'Mobile No.';
        $customer_header[] = 'Email';
        $customer_header[] = 'DOB';
        $customer_header[] = $tax_name;
        $customer_header[] = 'Address';
        $customer_header[] = 'Area';
        $customer_header[] = 'City / Town';
        $customer_header[] = 'Pin / Zip Code';
        $customer_header[] = 'State / Region';
        $customer_header[] = 'Country';
        $customer_header[] = 'Source';
        $customer_header[] = 'Customer Since';
        $customer_header[] = 'Note';

        return $customer_header;
    }

    public function map($customer): array
    {
        $rows    = [];
        $rows[] = $customer->customer_name;
        $customer_gender = '';
        if(isset($customer['customer_gender']) && $customer['customer_gender'] != '' && $customer['customer_gender'] != 0)
        {

            if($customer['customer_gender'] == 1)
            {
                $customer_gender = "Male";
            }
            elseif($customer['customer_gender'] == 2)
            {
                $customer_gender = "Female";
            }
            else
            {
                $customer_gender = "Transgender";
            }
        }

        $rows[] = $customer_gender;

        $customer_mobile_no = '';
        if($customer['customer_mobile'] != ''){
            $customer_mobile_no = $customer->customer_mobile_dial_code.' ' .$customer->customer_mobile;
        }

        $rows[] = $customer_mobile_no;
        $rows[] = $customer->customer_email;
        $dob = '';
        if($customer->customer_date_of_birth != '')
        {
            $date =$customer->customer_date_of_birth;
            $dob = date('d-m-Y',strtotime($date));
        }


        $rows[] = $dob;
        $gstin = '';
        if(isset($customer) && isset($customer['customer_address_detail']) && isset($customer['customer_address_detail']['customer_gstin']))
        {
            $gstin = $customer['customer_address_detail']['customer_gstin'];
        }

        $rows[] = $gstin;
        $customeraddress = '';
        if(isset($customer) && isset($customer['customer_address_detail']) && isset($customer['customer_address_detail']['customer_address']))
        {
            $customeraddress = $customer['customer_address_detail']['customer_address'];
         }

        $rows[] = $customeraddress;
        $customer_area = '';
        if(isset($customer) && isset($customer['customer_address_detail']) && isset($customer['customer_address_detail']['customer_area']))
        {
            $customer_area = $customer['customer_address_detail']['customer_area'];
        }

        $rows[] = $customer_area;

        $customer_city = '';
        if(isset($customer) && isset($customer['customer_address_detail']) && isset($customer['customer_address_detail']['customer_city']))
        {
            $customer_city = $customer['customer_address_detail']['customer_city'];
        }

        $rows[] = $customer_city;
        $customer_pincode = '';
        if(isset($customer) && isset($customer['customer_address_detail'])
            && isset($customer['customer_address_detail']['customer_pincode']))
        {
            $customer_pincode = $customer['customer_address_detail']['customer_pincode'];
            }

        $rows[] = $customer_pincode;
        $state_name_val = '';
        if(isset($customer) && isset($customer['customer_address_detail'])
            && isset($customer['customer_address_detail']['state_id']))
        {
            $state_name_val = $customer['customer_address_detail']['state_name']['state_name'];
           }
        $rows[] = $state_name_val;



        $country_name_val = '';
        if(isset($customer) && isset($customer['customer_address_detail']) && isset($customer['customer_address_detail']['country_id']))
        {
            $country_name_val = $customer['customer_address_detail']['country_name']['country_name'];
        }
        $rows[] = $country_name_val;
        $customer_source_name = '';
        if($customer['customer_source_id'] != null)
        {
            $customer_source_name = $customer->customer_source->source_name;
        }
        $rows[] = $customer_source_name;

        $date = explode(' ',$customer->created_at)[0];

        $rows[] = date('d-m-Y', strtotime($date));

        $customer_note = '';
        if(isset($customer) && isset($customer['note']) && $customer['note'] != '')
        {
            $customer_note = $customer['note'];
        }
        $rows[] = $customer_note;
        return $rows;
    }


    public function query()
    {

        $customer = customer::
        /*where('company_id',Auth::user()->company_id)*/
            where('deleted_at','=',NULL)
            ->with('customer_address_detail.country_name')
            ->orderBy('customer_id', 'DESC');



        if(isset($this->query) && $this->query != '' && $this->query['customer_name'] != '')
        {
            $customer->where('customer_name', 'like', '%'.$this->query['customer_name'].'%');
        }

        if(isset($this->query) && $this->query != '' && $this->query['customer_mobile'] != '')
        {
            $customer->where('customer_mobile', 'like', '%'.$this->query['customer_mobile'].'%');
        }

        if(isset($this->query) && $this->query != '' && $this->query['customer_email'] != '')
        {
            $customer->where('customer_email', 'like', '%'.$this->query['customer_email'].'%');
        }

        if(isset($this->query) && $this->query != '' && $this->query['customer_gstin'] != '')
        {
            $customer_gstin = $this->query['customer_gstin'];
            $customer->whereHas('customer_address_detail',function ($q) use($customer_gstin){
                $q->where('customer_gstin', 'like', '%'.$customer_gstin.'%');
            });
        }

        if(isset($this->query) && $this->query != '' && $this->query['customer_date_of_birth'] != '')
        {
            $customer->where('customer_date_of_birth', 'like', '%'.$this->query['customer_date_of_birth'].'%');
        }
        if(isset($this->query) && $this->query != '' && $this->query['customer_area'] != '')
        {
            $customer_area = $this->query['customer_area'];
            $customer->whereHas('customer_address_detail',function ($q) use($customer_area){
                $q->where('customer_area', 'like', '%'.$customer_area.'%');
            });
        }
        if(isset($this->query) && $this->query != '' && $this->query['customer_city'] != '')
        {
            $customer_city = $this->query['customer_city'];
            $customer->whereHas('customer_address_detail',function ($q) use($customer_city){
                $q->where('customer_city', 'like', '%'.$customer_city.'%');
            });
        }
        if(isset($this->query) && $this->query != '' && $this->query['customer_pincode'] != '')
        {
            $customer_pincode = $this->query['customer_pincode'];
            $customer->whereHas('customer_address_detail',function ($q) use($customer_pincode){
                $q->where('customer_pincode', 'like', '%'.$customer_pincode.'%');
            });
        }
        if(isset($this->query) && $this->query != '' && $this->query['state_id'] != ''  && $this->query['state_id'] != 0)
        {
            $state_id = $this->query['state_id'];
            $customer->whereHas('customer_address_detail',function ($q) use($state_id){
                $q->where('state_id', 'like', '%'.$state_id.'%');
            });
        }
        if(isset($this->query) && $this->query != '' && $this->query['country_id'] != '' && $this->query['country_id'] != 0)
        {
            $country_id = $this->query['country_id'];
            $customer->whereHas('customer_address_detail',function ($q) use($country_id){
                $q->where('country_id', 'like', '%'.$country_id.'%');
            });
        }


        return $customer;
    }

}
