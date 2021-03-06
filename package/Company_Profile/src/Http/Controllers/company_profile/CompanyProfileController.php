<?php

namespace Retailcore\Company_Profile\Http\Controllers\company_profile;
use App\home_navigations_data;
use App\Http\Controllers\Controller;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Illuminate\Http\Request;
use App\state;
use App\country;
use Illuminate\Validation\Rule;
use Auth;
use DB;
use App\company;
use App\User;
use App\home_navigation;
use Retailcore\EmployeeMaster\Models\employee\employee_role;
use Retailcore\EmployeeMaster\Models\employee\employee_role_permission;
use Retailcore\Store_Profile\Models\store_profile\company_relationship_tree;
use Log;
class CompanyProfileController extends Controller
{

    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);


        $is_store = 0;
        $state = state::all();
        $country = country::all();
        $company_profile = company_profile::where('company_id', Auth::user()->company_id)->first();
        return view('company_profile::company_profile/company_profile', compact('company_profile', 'state', 'country' ,'is_store'));
    }

    public function get_mobilecode(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
             $data = $request->all();
             //print_r($data['country_id']);
             $country_code       =  country::where('country_id',$data['country_id'])->first();
             $mobile_dialcode    =  $country_code['country_code'];

             return json_encode(array("Success" => "True", "Data" => $mobile_dialcode));
    }
    public function company_profile_create(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $data = $request->all();
        $company_profile_data =  array();

        parse_str($data['formdata'],$company_profile_data);

        //$company_profile_data = preg_replace('/\s+/', ' ', $company_profile_data);

        $validate_error = \Validator::make($company_profile_data,
            [
                'full_name' => ['required'],
                'personal_mobile_no' => ['required', Rule::unique('company_profiles')->ignore($company_profile_data['company_profile_id'], 'company_profile_id')],
                'personal_email' => ['required',Rule::unique('company_profiles')->ignore($company_profile_data['company_profile_id'], 'company_profile_id')],
                'company_name' => ['required'],
                'company_address' => ['required'],
                'company_area' => ['required'],
                'company_city' => ['required'],
                //'company_pincode' => ['required'],
            ]);
        if($validate_error-> fails())
        {
            Log::error(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.' '.$validate_error-> messages().' '.PHP_EOL);
            return json_encode(array("Success"=>"False","status_code"=>409,"Message"=>$validate_error->messages()));
            exit;
        }

        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;
        $created_by = $userId;

        try {
            $url = "";
            $defualturl='home';

            DB::beginTransaction();
            // for srore profile
            if($company_profile_data['is_store'] == 1)
            {
                $url = 'view_store';
                $defualturl = 'view_store';


                $add_company = company::updateOrCreate(
                    ['company_id' => $company_profile_data['company_id']],
                    [
                        'company_name' => (isset($company_profile_data['company_name']) ? $company_profile_data['company_name'] : ''),
                        'company_contact_person' => (isset($company_profile_data['full_name']) ? $company_profile_data['full_name'] : ''),
                        'company_address' => (isset($company_profile_data['company_address']) ? $company_profile_data['company_address'] : ''),
                        'company_contact_no' => (isset($company_profile_data['personal_mobile_no']) ? $company_profile_data['personal_mobile_no'] : ''),
                        'company_mobile_no' => (isset($company_profile_data['company_mobile']) ? $company_profile_data['company_mobile'] : ''),
                        'company_email' => (isset($company_profile_data['personal_email']) ? $company_profile_data['personal_email'] : ''),
                    ]);
                $wherehouse_id = $add_company->company_id;
            }
            if($company_profile_data['is_store'] == 1)
            {
                $cmp_id = $wherehouse_id;
            }
            else
            {
                $cmp_id = $company_id;
            }
            // end
            $update_cmp_profile = company_profile::updateOrCreate(
                ['company_profile_id' => $company_profile_data['company_profile_id'],
                    'company_id' => $cmp_id],
                [
                    'full_name' => (isset($company_profile_data['full_name']) ? $company_profile_data['full_name'] : ''),
                    'personal_mobile_dial_code' => (isset($company_profile_data['personal_mobile_dial_code']) ? $company_profile_data['personal_mobile_dial_code'] : ''),
                    'personal_mobile_no' => (isset($company_profile_data['personal_mobile_no']) ? $company_profile_data['personal_mobile_no'] : ''),
                    'personal_email' => (isset($company_profile_data['personal_email']) ? $company_profile_data['personal_email'] : ''),
                    'company_name' => (isset($company_profile_data['company_name']) ? $company_profile_data['company_name'] : ''),
                    'company_mobile_dial_code' => (isset($company_profile_data['company_mobile_dial_code']) ? $company_profile_data['company_mobile_dial_code'] : ''),
                    'company_mobile' => (isset($company_profile_data['company_mobile']) ? $company_profile_data['company_mobile'] : ''),
                    'company_email' => (isset($company_profile_data['company_email']) ? $company_profile_data['company_email'] : ''),
                    'website' => (isset($company_profile_data['website']) ? $company_profile_data['website'] : ''),
                    'gstin' => (isset($company_profile_data['gstin']) ? $company_profile_data['gstin'] : ''),
                    'state_id' => (isset($company_profile_data['state_id']) && $company_profile_data['state_id'] != '' ? $company_profile_data['state_id'] : NULL),
                    'whatsapp_mobile_dial_code' => (isset($company_profile_data['whatsapp_mobile_dial_code']) ? $company_profile_data['whatsapp_mobile_dial_code'] : ''),
                    'whatsapp_mobile_number' => (isset($company_profile_data['whatsapp_mobile_number']) ? $company_profile_data['whatsapp_mobile_number'] : ''),
                    'facebook' => (isset($company_profile_data['facebook']) ? $company_profile_data['facebook'] : ''),
                    'instagram' => (isset($company_profile_data['instagram']) ? $company_profile_data['instagram'] : ''),
                    'pinterest' => (isset($company_profile_data['pinterest']) ? $company_profile_data['pinterest'] : ''),
                    'company_address' => (isset($company_profile_data['company_address']) ? $company_profile_data['company_address'] : ''),
                    'company_area' => (isset($company_profile_data['company_area']) ? $company_profile_data['company_area'] : ''),
                    'company_city' => (isset($company_profile_data['company_city']) ? $company_profile_data['company_city'] : ''),
                    'company_pincode' => (isset($company_profile_data['company_pincode']) ? $company_profile_data['company_pincode'] : ''),
                    'country_id' => (isset($company_profile_data['country_id']) && $company_profile_data['country_id'] != '' ? $company_profile_data['country_id'] : NULL),
                    'authorized_signatory_for' => (isset($company_profile_data['authorized_signatory_for']) ? $company_profile_data['authorized_signatory_for'] : ''),
                    'terms_and_condition' => (isset($company_profile_data['terms_and_condition']) ? $company_profile_data['terms_and_condition'] : ''),
                    'additional_message' => (isset($company_profile_data['additional_message']) ? $company_profile_data['additional_message'] : ''),
                    'return_days' => (isset($company_profile_data['returndays']) ? $company_profile_data['returndays'] : ''),
                    'bill_number_prefix' => (isset($company_profile_data['bill_number_prefix']) ? $company_profile_data['bill_number_prefix'] : ''),
                    'credit_receipt_prefix' => (isset($company_profile_data['credit_receipt_prefix']) ? $company_profile_data['credit_receipt_prefix'] : ''),
                    'debit_receipt_prefix' => (isset($company_profile_data['debit_receipt_prefix']) ? $company_profile_data['debit_receipt_prefix'] : ''),
                    'po_number_prefix' => (isset($company_profile_data['po_number_prefix']) ? $company_profile_data['po_number_prefix'] : ''),
                    'account_holder_name' => (isset($company_profile_data['account_holder_name']) ? $company_profile_data['account_holder_name'] : ''),
                    'bank_name' => (isset($company_profile_data['bank_name']) ? $company_profile_data['bank_name'] : ''),
                    'account_number' => (isset($company_profile_data['account_number']) ? $company_profile_data['account_number'] : NULL),
                    'ifsc_code' => (isset($company_profile_data['ifsc_code']) ? $company_profile_data['ifsc_code'] : ''),
                    'branch' => (isset($company_profile_data['branch']) ? $company_profile_data['branch'] : ''),
                    'po_terms_and_condition' => (isset($company_profile_data['po_terms_and_condition']) ? $company_profile_data['po_terms_and_condition'] : '')
                ]);

                // for store profile
                $store_id = $update_cmp_profile->company_profile_id;
                if($company_profile_data['is_store'] == 1)
                {
                    $add_store_profile = company_relationship_tree::updateOrCreate(
                    [
                        'warehouse_id' => $company_id,
                        'store_id' => $store_id,
                        'created_by' => $userId,
                        'modified_by' =>$userId
                    ]);
                }

            $company_profile_id = $update_cmp_profile->company_profile_id;

            if($company_profile_data['is_store'] == 1)
            {
                //////////////////////////////////////////////
                // Creating Master Admin for Store
                //////////////////////////////////////////////

                if(preg_match('/\s/',$company_profile_data['full_name']))
                {
                    $exp_name       =   explode(' ',$company_profile_data['full_name']);
                    $emp_firstname  =   $exp_name[0];
                    $emp_lastname   =   $exp_name[1];
                    $storeName      =   $emp_firstname.' '.$emp_lastname;
                }
                else
                {
                    $emp_firstname  =   $company_profile_data['full_name'];
                    $emp_lastname   =   '';
                    $storeName      =   $emp_firstname;
                }


                $home_navigation = home_navigation::select('*')->where('home_navigation_id', '!=', '7')->where('home_navigation_id', '!=', '8')->whereNull('deleted_at')
                    ->with('home_navigations_data')->get();

                $employee_role = employee_role::updateOrCreate([
                    'employee_role_id' => '',
                    'company_id' => $cmp_id,
                    'role_name' => 'Admin ('.$storeName.')',
                    'is_active' => '1',
                    'created_by' => $userId
                ]);

                $employee_role_id = $employee_role->employee_role_id;

                foreach ($home_navigation as $i => $mainNav)
                {
                    $employee_role_permissions = employee_role_permission::updateOrCreate(
                        [
                            'employee_role_permission_id' => '',
                            'company_id' => $cmp_id,
                            'employee_role_id' => $employee_role_id,
                            'home_navigation_id' => $mainNav->home_navigation_id,
                            'home_navigation_data_id' => NULL,
                            'permission_view' => '1',
                            'permission_add' => '0',
                            'permission_edit' => '0',
                            'permission_delete' => '0',
                            'permission_export' => '0',
                            'permission_print' => '0',
                            'permission_upload' => '0',
                            'created_by' => $userId
                        ]);

                    foreach ($mainNav->home_navigations_data as $j => $subNav)
                    {
                        $employee_role_permissions = employee_role_permission::updateOrCreate(
                            [
                                'employee_role_permission_id' => '',
                                'company_id' => $cmp_id,
                                'employee_role_id' => $employee_role_id,
                                'home_navigation_id' => $mainNav->home_navigation_id,
                                'home_navigation_data_id' => $subNav->home_navigation_data_id,
                                'permission_view' => $subNav->option_view,
                                'permission_add' => $subNav->option_add,
                                'permission_edit' => $subNav->option_edit,
                                'permission_delete' => $subNav->option_delete,
                                'permission_export' => $subNav->option_export,
                                'permission_print' => $subNav->option_print,
                                'permission_upload' => $subNav->option_upload,
                                'created_by' => $userId
                            ]);
                    }

                }

                // $emp_firstname = $exp_name[0];
                // $emp_lastname = $exp_name[1]==''?'':$exp_name[1];
                $emp_dialcode = $company_profile_data['personal_mobile_dial_code'];
                $emp_mobileno = $company_profile_data['personal_mobile_no'];
                $emp_joindate = date('Y-m-d');
                $emp_login = 1;
                $emp_email = $company_profile_data['personal_email'];

                $emp_password = bcrypt('password');
                $emp_cpassword = bcrypt('password');

                $emp_address = $company_profile_data['company_address'];
                $emp_state = $company_profile_data['state_id'];
                $emp_country = $company_profile_data['country_id'];
                $emp_storeid = $store_id;
                $emp_appid = md5(microtime() . $emp_firstname);
                $emp_appsecret = md5(microtime() . $emp_email);
                $emp_active = 1;

                $admin_default_user = user::updateOrCreate(
                    [
                        'user_id' => '',
                        'store_id' => $store_id,
                        'company_id' => $cmp_id,
                        'employee_role_id' => $employee_role_id,
                        'employee_firstname' => $emp_firstname,
                        'employee_lastname' => $emp_lastname,
                        'employee_mobileno_dial_code' => $emp_dialcode,
                        'employee_mobileno' => $emp_mobileno,
                        'employee_joiningdate' => $emp_joindate,
                        'employee_login' => $emp_login,
                        'email' => $emp_email,
                        'password' => $emp_password,
                        'encrypt_password' => $emp_cpassword,
                        'employee_address' => $emp_address,
                        'state_id' => $emp_state,
                        'country_id' => $emp_country,
                        'store_id' => $emp_storeid,
                        'app_id' => $emp_appid,
                        'app_secret' => $emp_appsecret,
                        'is_active' => $emp_active,
                        'created_by' => $userId,
                        'modified_by' => $userId
                    ]);
            }

                // end
            DB::commit();

            }catch(\Illuminate\Database\QueryException $e) {

                DB::rollback();
            Log::error(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.' '.$e-> getMessage().' '.PHP_EOL);
                return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
            }




            if ($update_cmp_profile) {
                if ($company_profile_data['company_profile_id'] != '') {

                    return json_encode(array("Success" => "True", "Message" => "Company Profile has been successfully updated.", "url" => $url, "company_profile_id" => $company_profile_id));
                } else {

                    return json_encode(array("Success" => "True", "Message" => "Company Profile has been successfully added.", "url" => $url, "company_profile_id" => $company_profile_id));

                }
            } else {
                return json_encode(array("Success" => "False", "Message" => "Something Went Wrong"));
            }
    }

    /*public function software_configuration_create(Request $request)
    {
        $data = $request->all();
        $software_configuration_data =  array();

        parse_str($data['formdata'], $software_configuration_data);

        $software_configuration_data = preg_replace('/\s+/', ' ', $software_configuration_data);

        $userId = Auth::User()->user_id;
        $company_id = Auth::User()->company_id;

        try {
            DB::beginTransaction();
            $software_configuration = company_profile::updateOrCreate(
                ['company_profile_id' => $software_configuration_data['company_profile_id'],
                    'company_id' => $company_id,],
                [
                    'created_by' => $userId,
                    'company_id' => $company_id,
                    'country_id' => 102,
                    'tax_type' => (isset($software_configuration_data['tax_type']) ? $software_configuration_data['tax_type'] : ''),
                    'tax_title' => (isset($software_configuration_data['tax_title']) ? $software_configuration_data['tax_title'] : ''),
                    'currency_title' => (isset($software_configuration_data['currency_title']) ? $software_configuration_data['currency_title'] : ''),
                    'decimal_points' => (isset($software_configuration_data['decimal_points']) ? $software_configuration_data['decimal_points'] : 0),
                    'billtype' => (isset($software_configuration_data['billtype']) ? $software_configuration_data['billtype'] : ''),
                    'series_type' => (isset($software_configuration_data['series_type']) ? $software_configuration_data['series_type'] : ''),
                    'billprint_type' => (isset($software_configuration_data['billprint_type']) ? $software_configuration_data['billprint_type'] : ''),
                    'navigation_type' => (isset($software_configuration_data['navigation_type']) ? $software_configuration_data['navigation_type'] : ''),
                    'inward_type' => (isset($software_configuration_data['inward_type']) ? $software_configuration_data['inward_type'] : ''),
                    'inward_calculation' => (isset($software_configuration_data['inward_calculation']) ? $software_configuration_data['inward_calculation'] : 1),
                ]
            );
            DB::commit();
            $company_profile_id = $software_configuration->company_profile_id;
        }catch (\Illuminate\Database\QueryException $e)
        {
            DB::rollback();
            return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
        }


        if(isset($software_configuration_data['inward_type']) && $software_configuration_data['inward_type'] !== '')
        {
            $hide_module = array();
            $hide_module_name = '';
            if($software_configuration_data['inward_type'] ==1)
            {
                //fmcg
                $hide[0]['module_name'] = 'inward_stock_show';
                $hide[0]['active'] = 0;

                $hide[1]['module_name'] = 'inward_stock';
                $hide[1]['active'] = 1;

                $hide[2]['module_name'] = 'batch_no_wise_report';
                $hide[2]['active'] = 1;

            }
            if($software_configuration_data['inward_type'] ==2)
            {
                //garment
                $hide[0]['module_name'] = 'inward_stock';
                $hide[0]['active'] = 0;

                $hide[1]['module_name'] = 'inward_stock_show';
                $hide[1]['active'] = 1;

                $hide[2]['module_name'] = 'batch_no_wise_report';
                $hide[2]['active'] = 0;
            }

            if($hide != '')
            {
                foreach ($hide as $item) {
                    home_navigations_data::where('company_id',$company_id)
                    ->where('nav_url',$item['module_name'])
                    ->update(array(
                       'is_active' => $item['active'],
                        'modified_by' =>$userId
                    ));
                }
            }
        }

        if($software_configuration)
        {
            if ($software_configuration_data['company_profile_id'] != '')
            {

                return json_encode(array("Success"=>"True","Message"=>"Software Configuration has been successfully updated.","url"=>"","company_profile_id"=>$company_profile_id));
            }
            else
            {
                return json_encode(array("Success"=>"True","Message"=>"Software Configuration has been successfully added.","url"=>"company_profile","company_profile_id"=>$company_profile_id));

            }
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong"));
        }
    }*/

    /*public function valid_technical_team(Request $request)
    {
        $data = $request->all();
        $validate_team =  array();

        parse_str($data['formdata'], $validate_team);

        $validate_team = preg_replace('/\s+/', ' ', $validate_team);

        if(md5($validate_team['configuration_password']) == ('fc6fa2d0aaab4a80fba1832f23960331'))
        {
            return json_encode(array("Success"=>"True"));
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Successfull!"));
        }

    }*/


}
