<?php

namespace Retailcore\Supplier\Http\Controllers\supplier;
use App\Http\Controllers\Controller;
use Retailcore\Inward_Stock\Models\inward\inward_stock;
use Retailcore\Supplier\Models\supplier\supplier_bank;
use Retailcore\Supplier\Models\supplier\supplier_company_info;
use Retailcore\Company_Profile\Models\company_profile\company_profile;

use Retailcore\Supplier\Models\supplier\supplier_gst;
use Retailcore\Supplier\Models\supplier\supplier_treatment;
use Retailcore\Supplier\Models\supplier\supplier_contact_details;
use Retailcore\Supplier\Models\supplier\salutation;
use Retailcore\Supplier\Models\supplier\supplier_template;
use Retailcore\Supplier\Models\supplier\supplier_company_infos;
use App\state;
use App\country;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Auth;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use Log;

class SupplierCompanyInfoController extends Controller
{

    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $supplier= supplier_company_info::
        //where('company_id',Auth::user()->company_id)
            whereNull('deleted_at')
            ->orderBy('supplier_company_info_id', 'DESC')
            ->paginate(10);

        foreach($supplier AS $key=>$value)
        {
            if(isset($value['supplier_gst'][0]) && $value['supplier_gst'][0] != '')
            {
                $supplier_count = inward_stock::
                /*where('company_id', Auth::user()->company_id)*/
                    whereNull('deleted_at')
                    ->where('supplier_gst_id', $value['supplier_gst'][0]['supplier_gst_id'])
                    ->count();

                $supplier[$key]['delete_option'] = 1;
                if ($supplier_count > 0) {
                    $supplier[$key]['delete_option'] = 0;
                }
            }
        }

        $supplier_treatments = supplier_treatment::where('is_active','=',1)->whereNull('deleted_at')->get();
        $salutation = salutation::get();
        $state = state::get();
        $country = country::get();


        return view('supplier::supplier/supplier_show',compact('supplier','supplier_treatments','salutation','state','country'));
    }

    public function supplier_fetch_data(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->ajax())
        {
            $data = $request->all();
            $sort_by = $data['sortby'];
            $sort_type = $data['sorttype'];
            $query = $data['query'];

            $query = str_replace(" ", "%", $query);

            $supplier = supplier_company_info::where('company_id',Auth::user()->company_id)
                ->select('*')
                ->where('deleted_at','=',NULL)
                ->with('supplier_contact_detail')
                ->with('supplier_gst');

            if(isset($query) && $query != '' && $query['company_name'] != '')
            {
                $supplier->where('supplier_company_name', 'like', '%'.$query['company_name'].'%');
            }

            if(isset($query) && $query != '' && $query['supplier_mobile'] != '')
            {
                 $supplier->whereHas('supplier_contact_detail',function ($q) use($query)
                 {
                    $q->where('supplier_contact_mobile_no', 'like', '%'.$query['supplier_mobile'].'%');
                });
            }

            if(isset($query) && $query != '' && $query['supplier_name'] != '')
            {
                $supplier->whereHas('supplier_contact_detail',function ($q) use($query)
                {
                    $q->where('supplier_contact_firstname', 'like', '%'.$query['supplier_name'].'%');
                });
             }
             if(isset($query) && $query != '' && $query['supplier_gstin'] != '')
            {
                $supplier->whereHas('supplier_gst',function ($q) use($query)
                {
                    $q->where('supplier_gstin', 'like', '%'.$query['supplier_gstin'].'%');
                });
            }

            if(isset($query) && $query != '' && $query['supplier_city'] != '')
            {
                 $supplier->where('supplier_company_city', 'like', '%'.$query['supplier_city'].'%');
            }

            if(isset($query) && $query != '' && $query['state_id'] != ''  && $query['state_id'] != 0)
            {
                $supplier->where('state_id', 'like', '%'.$query['state_id'].'%');
            }
            if(isset($query) && $query != '' && $query['country_id'] != '' && $query['country_id'] != 0)
            {
                $supplier->where('country_id', 'like', '%'.$query['country_id'].'%');
            }

            $supplier = $supplier->orderBy($sort_by,$sort_type)->paginate(10);

              foreach($supplier AS $key=>$value)
            {
            if(isset($value['supplier_gst'][0]) && $value['supplier_gst'][0] != '') {
                $supplier_count = inward_stock::
                /*where('company_id', Auth::user()->company_id)*/
                    whereNull('deleted_at')
                    ->where('supplier_gst_id', $value['supplier_gst'][0]['supplier_gst_id'])
                    ->count();

                $supplier[$key]['delete_option'] = 1;
                if ($supplier_count > 0) {
                    $supplier[$key]['delete_option'] = 0;
                }
            }
            }

            $state = state::all();
            $country = country::all();

            return view('supplier::supplier/supplier_data', compact('supplier','state','country'))->render();
        }
    }

    public function add_supplier(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $data = $request->all();
        $company_id = Auth::User()->company_id;
        $validate_error = \Validator::make($data['supplier_company_info'],
            [
                'supplier_company_name' => ['required', Rule::unique('supplier_company_infos')->ignore($data['supplier_company_info']['supplier_company_info_id'], 'supplier_company_info_id')->whereNull('deleted_at')->whereNotNull('supplier_company_name')],
               // 'supplier_first_name' => ['required', Rule::unique('supplier_company_infos')->ignore($data['supplier_company_info']['supplier_company_info_id'], 'supplier_company_info_id')->whereNull('deleted_at')->whereNotNull('supplier_first_name')],
               // 'supplier_pan_no' => ['required', Rule::unique('supplier_company_infos')->ignore($data['supplier_company_info']['supplier_company_info_id'], 'supplier_company_info_id')->whereNull('deleted_at')->whereNotNull('supplier_pan_no')],
            ]);


        if($validate_error-> fails())
        {
            return json_encode(array("Success"=>"False","status_code"=>409,"Message"=>$validate_error->messages()));
            exit;
        }

        foreach($data['supplier_bank_info'] AS $key=>$value)
        {
            $validate_bank_error = \Validator::make($value,
                [
                    //'supplier_bank_name' => ['required', Rule::unique('supplier_banks')->ignore($data['supplier_company_info']['supplier_company_info_id'], 'supplier_company_info_id')->ignore($value['supplier_bank_id'],'supplier_bank_id')->whereNull('deleted_at')->whereNotNull('supplier_bank_name')],
                    'supplier_bank_account_name' => [ Rule::unique('supplier_banks')->ignore($data['supplier_company_info']['supplier_company_info_id'], 'supplier_company_info_id')->ignore($value['supplier_bank_id'],'supplier_bank_id')->whereNull('deleted_at')->whereNotNull('supplier_bank_account_name')],
                    'supplier_bank_account_no' => [ Rule::unique('supplier_banks')->ignore($data['supplier_company_info']['supplier_company_info_id'], 'supplier_company_info_id')->ignore($value['supplier_bank_id'],'supplier_bank_id')->whereNull('deleted_at')->whereNotNull('supplier_bank_account_no')],
                ]);
            if($validate_bank_error-> fails())
            {
                return json_encode(array("Success"=>"False","status_code"=>409,"Message"=>$validate_bank_error->messages()));
                exit;
            }
        }
       foreach($data['supplier_gst_info'] AS $key=>$value)
        {
           $supplier_gst_id =  isset($value['supplier_gst_id']) ? $value['supplier_gst_id'] : '';

           if($value['supplier_treatment_id'] == 1)
           {
                $validate_gst_error = \Validator::make($value,
                    [
                        'supplier_gstin' => ['required',
                            Rule::unique('supplier_gsts')->ignore($data['supplier_company_info']['supplier_company_info_id'], 'supplier_company_info_id')
                                ->ignore($supplier_gst_id, 'supplier_gst_id')
                                ->whereNull('deleted_at')
                                ->whereNotNull('supplier_gstin')],
                    ]);

                if ($validate_gst_error->fails()) {
                    return json_encode(array("Success" => "False", "status_code" => 409, "Message" => $validate_gst_error->messages()));
                    exit;
                }
            }
        }


        foreach($data['supplier_contact_info'] AS $key=>$value)
        {
            $validate_gst_error = \Validator::make($value,
                [
                   // 'salutation_id' => ['required',Rule::unique('supplier_contact_details')->ignore($data['supplier_company_info']['supplier_company_info_id'], 'supplier_company_info_id')->ignore($value['supplier_contact_details_id'],'supplier_contact_details_id')->whereNull('deleted_at')->whereNotNull('salutation_id')],
                    'supplier_contact_firstname' => [Rule::unique('supplier_contact_details')->ignore($data['supplier_company_info']['supplier_company_info_id'], 'supplier_company_info_id')->ignore($value['supplier_contact_details_id'],'supplier_contact_details_id')->whereNull('deleted_at')->whereNotNull('supplier_contact_firstname')],
                    'supplier_contact_email_id' => [
                        Rule::unique('supplier_contact_details')
                            ->ignore($data['supplier_company_info']['supplier_company_info_id'], 'supplier_company_info_id')->ignore($value['supplier_contact_details_id'],'supplier_contact_details_id')->whereNull('deleted_at')->whereNotNull('supplier_contact_email_id')],
                    'supplier_contact_mobile_no' => [Rule::unique('supplier_contact_details')->ignore($data['supplier_company_info']['supplier_company_info_id'], 'supplier_company_info_id')->ignore($value['supplier_contact_details_id'],'supplier_contact_details_id')->whereNull('deleted_at')->whereNotNull('supplier_contact_mobile_no')],
                ]);
            if($validate_gst_error-> fails())
            {
                return json_encode(array("Success"=>"False","status_code"=>409,"Message"=>$validate_gst_error->messages()));
                exit;
            }
        }

        try {
            DB::beginTransaction();

            //update all value of supplier bank set deleted_by  and deleted
            supplier_bank::where('supplier_company_info_id', $data['supplier_company_info']['supplier_company_info_id'])->update(array(
                'deleted_by' => Auth::User()->user_id,
                'deleted_at' => date('Y-m-d H:i:s')
            ));
            //update all value of supplier gst set deleted_by  and deleted
            supplier_gst::where('supplier_company_info_id', $data['supplier_company_info']['supplier_company_info_id'])->update(array(
                'deleted_by' => Auth::User()->user_id,
                'deleted_at' => date('Y-m-d H:i:s')
            ));
            //update all value of supplier Customer set deleted_by  and deleted
            supplier_contact_details::where('supplier_company_info_id', $data['supplier_company_info']['supplier_company_info_id'])->update(array(
                'deleted_by' => Auth::User()->user_id,
                'deleted_at' => date('Y-m-d H:i:s')
            ));


            // supplier company info add
            $supplier_company_infos_insert = supplier_company_info::updateOrCreate(
                ['supplier_company_info_id' => $data['supplier_company_info']['supplier_company_info_id'], 'company_id' => $company_id,],
                $data['supplier_company_info']
            );
            $supplier_company_info_id = $supplier_company_infos_insert->supplier_company_info_id;

            //supplier bank add
            foreach ($data['supplier_bank_info'] AS $key => $value) {
                $value['deleted_at'] = NULL;
                $value['deleted_by'] = NULL;

                $supplier_bank_insert = supplier_bank::updateOrCreate(
                    ['supplier_company_info_id' => $supplier_company_info_id,
                        'company_id' => $company_id,
                        'supplier_bank_id' => $value['supplier_bank_id'],
                    ],
                    $value);
            }
            //end of supplier bank add


            //supplier gst add
            foreach ($data['supplier_gst_info'] AS $key => $value) {
                $supplier_gst_id = isset($value['supplier_gst_id']) ? $value['supplier_gst_id'] : '';
                $value['deleted_at'] = NULL;
                $value['deleted_by'] = NULL;
                $supplier_gst_insert = supplier_gst::updateOrCreate(
                    ['supplier_company_info_id' => $supplier_company_info_id,
                        'company_id' => $company_id,
                        'supplier_gst_id' => $supplier_gst_id,
                    ],
                    $value);


            }
            //end of supplier gst add
            //supplier Customer add
            foreach ($data['supplier_contact_info'] AS $key => $value) {
                $value['deleted_at'] = NULL;
                $value['deleted_by'] = NULL;
                $supplier_contact_insert = supplier_contact_details::updateOrCreate(
                    ['supplier_company_info_id' => $supplier_company_info_id,
                        'company_id' => $company_id,
                        'supplier_contact_details_id' => $value['supplier_contact_details_id'],
                    ],
                    $value);
            }
            //end of supplier Customer add

            DB::commit();
        }catch(\Illuminate\Database\QueryException $e)
        {
            DB::rollback();
            return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
        }

        if($supplier_company_infos_insert)
        {
            if($data['supplier_company_info']['supplier_company_info_id'] != '')
            {
                return json_encode(array("Success"=>"True","Message"=>"Supplier successfully Update!"));
            }
            else
            {
                return json_encode(array("Success"=>"True","Message"=>"Supplier successfully Added!"));
            }
        }
    }

    public function supplier_data(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        if($request->ajax())
        {
            $supplier = supplier_company_info::where('company_id',Auth::user()->company_id)
                ->where('deleted_at','=',NULL)
                ->orderBy('supplier_company_info_id', 'DESC')
                ->paginate(10);

            foreach($supplier AS $key=>$value)
            {
                $supplier_count = inward_stock::
                //where('company_id',Auth::user()->company_id)
                    whereNull('deleted_at')
                    ->where('supplier_gst_id',$value['supplier_gst'][0]['supplier_gst_id'])
                    ->count();

                $supplier[$key]['delete_option'] = 1;
                if($supplier_count > 0)
                {
                    $supplier[$key]['delete_option'] = 0;
                }
            }

            $supplier_treatments = supplier_treatment::where('is_active','=',1)->whereNull('deleted_at')->get();
            $salutation = salutation::get();
            $state = state::get();
            $country = country::get();

            return view('supplier::supplier/supplier_data', compact('supplier','supplier_treatments','salutation','state','country'))->render();
        }
    }

    public function supplier_edit(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $supplier_company_info_id = decrypt($request->supplier_company_info_id);

        $supplier_data = supplier_company_info::where([
            ['supplier_company_info_id','=',$supplier_company_info_id],
            ['company_id',Auth::user()->company_id]])
            ->select('*')
            ->with('supplier_bank')
            ->with('supplier_gst')
            ->with('supplier_contact_detail')
            ->whereNull('deleted_at')
            ->first();


        return json_encode(array("Success"=>"True","Data"=>$supplier_data));
    }

    public function supplier_delete(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $userId = Auth::User()->user_id;

        try {
            DB::beginTransaction();

            $supplier_company_delete = supplier_company_info::whereIn('supplier_company_info_id',
                $request->deleted_id)
                ->update([
                    'deleted_by' => $userId,
                    'deleted_at' => date('Y-m-d H:i:s')
                ]);


            $supplier_banks_delete = supplier_bank::whereIn('supplier_company_info_id', $request->deleted_id)
                ->update([
                    'deleted_by' => $userId,
                    'deleted_at' => date('Y-m-d H:i:s')
                ]);

            $supplier_gst_delete = supplier_gst::whereIn('supplier_company_info_id', $request->deleted_id)
                ->update([
                    'deleted_by' => $userId,
                    'deleted_at' => date('Y-m-d H:i:s')
                ]);

            $supplier_contact_delete = supplier_contact_details::whereIn('supplier_company_info_id', $request->deleted_id)
                ->update([
                    'deleted_by' => $userId,
                    'deleted_at' => date('Y-m-d H:i:s')
                ]);

            DB::commit();
        }
        catch (\Illuminate\Database\QueryException $e)
        {
            DB::rollback();
            return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
        }

        if($supplier_company_delete)
        {
            return json_encode(array("Success"=>"True","Message"=>"Supplier has been successfully deleted.!"));
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong!"));
        }
    }

    public function supplier_dependency(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $supplier_company_info_id = decrypt($request->id);
        $dependent_array = [];
        $supplier_gst = supplier_gst::where('company_id',Auth::User()->company_id)
                                      ->where('supplier_company_info_id',$supplier_company_info_id)
                                      ->select('supplier_gst_id')
                                      ->whereNull('deleted_at')
                                      ->get();

        foreach ($supplier_gst AS $gst_key=>$gst_value)
        {
            $inward_stock = inward_stock::where('company_id',Auth::User()->company_id)
                          ->whereIn('supplier_gst_id',[$gst_value['supplier_gst_id']])
                          ->whereNull('deleted_at')
                          ->select('invoice_date','inward_date','invoice_no')
                          ->groupBy('inward_stock_id')
                          ->get();


            foreach ($inward_stock AS $inward_key=>$inward_value)
            {
                $detail = array('Invoice No' => $inward_value->invoice_no,
                                'Invoice Date' => $inward_value->invoice_date,
                                'Inward Date' => $inward_value->inward_date
                    );
                $dependent_array[] = array(
                    'Module_Name'=> "Inward Stock",
                    'detail' => $detail
                );
            }
        }

       return json_encode(array("Success"=>"True","Data"=>$dependent_array));
    }

    public function download_supplier_tmpate(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        return Excel::download(new supplier_template,'suppliers.xlsx');
    }


    public function supplier_check(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $supplier_excel_data = $request->all();

        if(isset($supplier_excel_data) && $supplier_excel_data != '' && !empty($supplier_excel_data))
        {
            $error = 0;

            $gst_name = company_profile::where('company_id',Auth::user()->company_id)
                ->select('inward_type','tax_type','tax_title','currency_title')->first();

            $tax_name = "GSTIN";
            if(isset($gst_name) && !empty($gst_name) && $gst_name['tax_type'] != '' && $gst_name['tax_type'] == 1)
            {
                $tax_name = $gst_name['tax_title'];
            }
            foreach ($supplier_excel_data AS $key=>$value) {


                if (supplier_company_info::where('supplier_company_name', $value['Company Name'])->whereNull('deleted_at')->exists())
                {
                    $error = 1;
                    return json_encode(array("Success" => "False", "Message" => "Company Name " . $value['Company Name'] . " Already exist!"));
                    exit;
                }

                if ($value['State'] != '')
                {
                    if (!state::where('state_name', $value['State'])->exists())
                    {
                        $error = 1;
                        return json_encode(array("Success" => "False", "Message" => "State " . $value['State'] . " Not Found!"));
                        exit;
                    }
                }

                if ($value['Country'] != '')
                {
                    if (!country::where('country_name', $value['Country'])->exists())
                    {
                        $error = 1;
                        return json_encode(array("Success" => "False", "Message" => "Country " . $value['Country'] . " Not Found!"));
                        exit;
                    }
                }

                if (supplier_bank::where('supplier_bank_account_no', $value['Bank Account No.'])->whereNull('deleted_at')->whereNotNull('supplier_bank_account_no')->exists())
                {
                    $error = 1;
                    return json_encode(array("Success" => "False", "Message" => "Bank Account No. " . $value['Bank Account No.'] . " Already exist!"));
                    exit;
                }



                if (supplier_gst::where('supplier_gstin', $value[$tax_name])->whereNull('deleted_at')->whereNotNull('supplier_gstin')->exists())
                {
                    $error = 1;
                    return json_encode(array("Success" => "False", "Message" => "GST " . $value[$tax_name] . " Already exist!"));
                    exit;
                }

                if (supplier_contact_details::where('supplier_contact_email_id', $value['Email Id'])->whereNull('deleted_at')->whereNotNull('supplier_contact_email_id')->exists())
                {
                    $error = 1;
                    return json_encode(array("Success" => "False", "Message" => "Email Id " . $value['Email Id'] . " Already exist!"));
                    exit;
                }
                if (supplier_contact_details::where('supplier_contact_mobile_no', $value['Supplier Contact Mobile No.'])->whereNotNull('supplier_contact_mobile_no')->whereNull('deleted_at')->exists())
                {
                    $error = 1;
                    return json_encode(array("Success" => "False", "Message" => "Mobile No. " . $value['Supplier Contact Mobile No.'] . " Already exist!"));
                    exit;
                }

            }

            if($error == 0)
            {
                $userId = Auth::User()->user_id;
                $company_id = Auth::User()->company_id;
                $created_by = $userId;
                try{
                    DB::beginTransaction();
                    $state_id = company_profile::select('state_id')->where('company_id',Auth::user()->company_id)->get();

                    foreach ($supplier_excel_data AS $key=>$value)
                    {

                        if ($value['State'] != '')
                        {
                            $state_excel = state::select('state_id')->where('state_name', $value['State'])->first();
                            $state_id_excel = $state_excel['state_id'];
                        }


                        $dob = '';
                        if(($value['Day of Birth(DD)'] != '' && $value['Day of Birth(DD)'] != null) || ($value['Month of Birth(MM)'] != '' && $value['Month of Birth(MM)'] != null) || ($value['Year of Birth(YYYY)'] != '' && $value['Year of Birth(YYYY)'] != null))
                        {
                            $dob = $value['Day of Birth(DD)'].'-'.$value['Month of Birth(MM)'].'-'.$value['Year of Birth(YYYY)'];
                        }

                        if ($value['Country'] != '')
                        {
                            $country_excel = country::select('country_id')->where('country_name', $value['Country'])->first();
                            $country_id_excel = $country_excel['country_id'];
                        }


                        // supplier company info add
                        $supplier_company_insert = supplier_company_info::updateOrCreate(
                            ['supplier_company_info_id' => '',
                                'company_id' => $company_id,],
                           [
                               'company_id' => $company_id,
                               'supplier_company_name' => $value['Company Name'],
                               'supplier_first_name' => $value['Supplier First Name'],
                               'supplier_last_name' => $value['Supplier Last Name'],
                               'supplier_company_dial_code' => '',
                               'supplier_company_mobile_no' => $value['Phone No.'],
                               'supplier_pan_no' => $value['Pan No.'],
                               'supplier_company_address' => $value['Shop no.,Building,Street etc.'],
                               'supplier_company_area' => $value['Area'],
                               'supplier_company_zipcode' => $value['Pin / Zip Code'],
                               'supplier_company_city' => $value['City / Town'],
                               'state_id' => (isset($state_id_excel) && $state_id_excel != 0 ? $state_id_excel : $state_id[0]['state_id']),
                               'country_id' => (isset($country_id_excel) && $country_id_excel != '' ? $country_id_excel : 102),
                               'supplier_payment_due_days' => $value['Due Days'],
                               'supplier_payment_due_date' => $value['Due Date'],
                               'note' => $value['Note'],
                               'is_active' => 1,
                               'created_by' => $userId,
                           ]
                        );


                        $supplier_company_id = $supplier_company_insert->supplier_company_info_id;

                        $supplier_bank = supplier_bank::updateOrCreate(
                            ['supplier_company_info_id' => $supplier_company_id,
                                'company_id' => $company_id,
                                'supplier_bank_id' => '',
                            ],
                            ['company_id' => $company_id,
                            'supplier_bank_name' => $value['Bank Name'],
                            'supplier_bank_account_name' => $value['Bank Account Name'],
                            'supplier_bank_account_no' => $value['Bank Account No.'],
                            'supplier_bank_ifsc_code' => $value['Bank IFSC Code'],
                            'is_active' => 1,
                            'created_by' => $userId,
                            ]);

                        if(isset($value[$tax_name]) && $value[$tax_name] != '')
                        {
                            $gst_state_id = substr($value[$tax_name],0,2);
                             $supplier_treatment = 1;
                        }
                        if(isset($value[$tax_name]) == ''){
                            $state_excel = state::select('state_id')->where('state_name', $value['State'])->first();
                            $gst_state_id = $state_excel['state_id'];
                            $supplier_treatment = 2;
                        }
                        $gst_var = ltrim($gst_state_id, '0');



                         $supplier_gst_insert = supplier_gst::updateOrCreate(
                             ['supplier_company_info_id' => $supplier_company_id,
                                 'company_id' => $company_id,
                                 'supplier_gst_id' => '',
                             ],
                             ['company_id' => $company_id,
                             'supplier_treatment_id' => $supplier_treatment,
                             'supplier_gstin' =>(isset($value[$tax_name]) ? $value[$tax_name] : ''),
                             'state_id' => (isset($gst_var) && $gst_var != '' ? $gst_var : ''),
                             'supplier_address' => $value[$tax_name .' Address'],
                             'supplier_area' => $value[$tax_name .' Area'],
                             'supplier_gst_zipcode' => $value[$tax_name .' Zipcode'],
                             'supplier_gst_city' => $value[$tax_name .' City'],
                             'country_id' => '102',
                             'is_active' => 1,
                             'created_by' => $userId,
                             ]);

                        $dob = '';


                        if(($value['Day of Birth(DD)'] != '' && $value['Day of Birth(DD)'] != null) || ($value['Month of Birth(MM)'] != '' && $value['Month of Birth(MM)'] != null) || ($value['Year of Birth(YYYY)'] != '' && $value['Year of Birth(YYYY)'] != null))
                        {
                            $dob = $value['Day of Birth(DD)'].'-'.$value['Month of Birth(MM)'].'-'.$value['Year of Birth(YYYY)'];
                        }
                        $supplier_contact_insert = supplier_contact_details::updateOrCreate(
                          ['supplier_company_info_id' => $supplier_company_id,
                              'company_id' => $company_id,
                              'supplier_contact_details_id' => '',
                          ],
                            ['company_id' => $company_id,
                            'salutation_id' => 1,
                            'supplier_contact_firstname' => $value['Supplier Contact First Name'],
                            'supplier_contact_lastname' => $value['Supplier Contact Last Name'],
                            'supplier_contact_designation' => $value['Designation'],
                            'supplier_contact_email_id' => $value['Email Id'],
                            'supplier_date_of_birth' => $dob,
                            'supplier_contact_mobile_no' => $value['Supplier Contact Mobile No.'],
                            'supplier_contact_dial_code' => '',
                            'supplier_whatsapp_no' => $value['Supplier Contact Whatsapp No.'],
                            'supplier_whatsapp_dial_code' => '',
                            'is_active' => 1,
                            'created_by' => $userId,
                            ]
                            );
                        DB::commit();
                        if ($supplier_company_insert)
                        {
                            if(!next($supplier_excel_data))
                            {

                                return json_encode(array("Success" => "True", "Message" => "Supplier has been successfully Added."));
                            }
                        }
                        else
                        {
                            return json_encode(array("Success" => "False", "Message" => "Something Went Wrong"));
                            exit;
                        }
                    }


                }catch (\Illuminate\Database\QueryException $e)
                {

                    DB::rollback();
                    return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
                }
            }
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"No Row!"));
        }
    }
}
