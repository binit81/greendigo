<?php

namespace App\Providers;

use App\company;
use App\home_navigation;
use App\home_navigations_data;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use Auth;
use Hash;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Retailcore\EmployeeMaster\Models\employee\employee_role;
use Retailcore\EmployeeMaster\Models\employee\employee_role_permission;
use Retailcore\Products\Models\product\ProductFeatures;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Request $request)
    {
        Schema::defaultStringLength(191);

        $checkTbl1   =   Schema::hasTable('companies');

        if($checkTbl1)
        {
            $company_code    =   company::select('company_code')->get();
            view()->share('company_code',$company_code);
        }
        else
        {
            $company_code   =   '';
        }

        $checkTbl   =   Schema::hasTable('home_navigations');

        if($checkTbl)
        {
            session(['ccompany_profile'=>1]);

            view()->composer('*', function ($view)
            {
                if(Auth::check())
                {
                    $user   =   user::select('employee_role_id','is_master')->where('user_id','=',Auth::User()->user_id)->get();

                    if($user[0]['is_master']==1)
                    {
                        $home_navigation = home_navigation::where('is_active','=','1')->orderBy('ordering', 'ASC')
                        ->with('home_navigations_data')->get();
                    }
                    else
                    {

                        $home_navigation    =   employee_role_permission::where('employee_role_id',$user[0]['employee_role_id'])
                            ->where('permission_view',1)
                            ->where('home_navigation_data_id',NULL)
                            ->with('home_navigations')
                            ->whereNull('deleted_by')
                            ->groupBy('home_navigation_id','home_navigation_data_id')
                            ->get();

                        foreach ($home_navigation as $key => $value)
                        {
                            $home_navigation[$key]['sub'] = employee_role_permission::where('home_navigation_id',$value['home_navigation_id'])
                             ->where('home_navigation_data_id','!=',NULL)
                             ->where('employee_role_id',$user[0]['employee_role_id'])
                             ->where('permission_view',1)
                             ->with('home_navigations_data_s')
                             ->get();
                        }
                    }
                    $navData = array(
                        'navLinks' => $home_navigation,
                        'chk_master' => $user[0]['is_master'],
                    );

                    view()->share('navLinks',$navData);
                }
           });



            view()->composer('*', function ($view)
            {
                if(Auth::check())
                {
                   // $current_url    =   url()->current();
                    $current_url    = $_SERVER['REQUEST_URI'];
                    if($_SERVER['QUERY_STRING'] != '')
                    {
                        $current_url    = $_SERVER['HTTP_REFERER'];
                    }


                    $strArray       =   explode('/',$current_url);
                    $pageUrl        =   end($strArray);



                    $breadcrumb     =   home_navigations_data::select('home_navigation_id','home_navigation_data_id','nav_tab_display_name','nav_url')->where('nav_url','=',$pageUrl)
                    ->where('is_active','=','1')->with('home_navigation')->get();

                    if(sizeof($breadcrumb)==0)
                    {
                        $navurl    =   '1'; //'dashboard';
                    }
                    else
                    {
                        $navurl    =   $breadcrumb[0]['home_navigation_data_id'];//$breadcrumb[0]['nav_url'];
                    }

                    $urlData = array(
                        'breadcrumb' => $breadcrumb,
                        'navurl' => $navurl,
                    );

                    view()->share('urlData',$urlData);

                    $employee_role_id     =   user::where('user_id','=',Auth::User()->user_id)->get();

                    $role_permissions     =   user::where('user_id','=',Auth::User()->user_id)
                    ->with([
                    'employee_role_permission' => function($fquery) use ($navurl,$employee_role_id) {
                        $fquery->select('*');
                        // $fquery->where('home_navigation_id','=',$navurl);
                        $fquery->where('home_navigation_data_id','=',$navurl);
                        $fquery->where('employee_role_id','=',$employee_role_id[0]['employee_role_id']);
                        $fquery->whereNull('deleted_by');
                    }
                    ])->get();

                    // echo '<pre>'; print_r($role_permissions); exit;

                    if($employee_role_id[0]['is_master']==1)
                    {
                        $permission_view        =   1;
                        $permission_add         =   1;
                        $permission_edit        =   1;
                        $permission_delete      =   1;
                        $permission_export      =   1;
                        $permission_print       =   1;
                        $permission_upload      =   1;
                        $userType               =   1;
                    }
                    else
                    {
                        if(sizeof($role_permissions[0]['employee_role_permission'])==0)
                        {
                            $permission_view        =   0;
                            $permission_add         =   0;
                            $permission_edit        =   0;
                            $permission_delete      =   0;
                            $permission_export      =   0;
                            $permission_print       =   0;
                            $permission_upload      =   0;
                            $userType               =   0;
                        }
                        else
                        {
                            $permission_view        =   $role_permissions[0]['employee_role_permission'][0]['permission_view'];
                            $permission_add         =   $role_permissions[0]['employee_role_permission'][0]['permission_add'];
                            $permission_edit        =   $role_permissions[0]['employee_role_permission'][0]['permission_edit'];
                            $permission_delete      =   $role_permissions[0]['employee_role_permission'][0]['permission_delete'];
                            $permission_export      =   $role_permissions[0]['employee_role_permission'][0]['permission_export'];
                            $permission_print       =   $role_permissions[0]['employee_role_permission'][0]['permission_print'];
                            $permission_upload      =   $role_permissions[0]['employee_role_permission'][0]['permission_upload'];
                            $userType               =   $employee_role_id[0]['is_master'];
                        }
                    }

                    $role_permissions = array(
                        'permission_view' => $permission_view,
                        'permission_add' => $permission_add,
                        'permission_edit' => $permission_edit,
                        'permission_delete' => $permission_delete,
                        'permission_export' => $permission_export,
                        'permission_print' => $permission_print,
                        'permission_upload' => $permission_upload,
                        'userType' => $userType
                    );

                    view()->share('role_permissions',$role_permissions);

                    $company_data    =   company::select('company_code','company_project_code','company_installation_code')->where('company_id',Auth::User()->company_id)->first();

                    if($company_data->company_code!='')
                    {
                        $txt_code       =   CLIENT_URL.'client_'.$company_data->company_code.'_'.$company_data->company_project_code.'_'.$company_data->company_installation_code.'.txt';

                        // print_r($txt_code); exit;

                        $client_url     =   DEFAULT_COMPANY_URL.$company_data->company_code.".txt";
                        $read   =   @file_get_contents($client_url);

                        $values         =   @file_get_contents($client_url);
                        $exp_values     =   explode(',',$values);

                        $license                =   str_replace('license=','',$exp_values[8]);
                        $license_               =   explode('(',$license);
                        $software_license       =   $license_[0];
                        $license_dates          =   str_replace(')','',$license_[1]);
                        $license_dates_         =   explode(':',$license_dates);
                        $license_from           =   $license_dates_[0];
                        $license_to             =   $license_dates_[1];
                        $license_days           =   $license_dates_[2];

                        $installation           =   str_replace('installation=','',$exp_values[9]);
                        $installation_           =   explode('(',$installation);
                        $software_installation  =   $installation_[0];
                        $installation_dates     =   str_replace(')','',$installation_[1]);
                        $installation_dates_    =   explode(':',$installation_dates);
                        $installation_from      =   $installation_dates_[0];
                        $installation_to        =   $installation_dates_[1];
                        $installation_days      =   $installation_dates_[2];

                        $current_date           =   date('Y-m-d');
                        $license_diff   =   strtotime($current_date) - strtotime($license_to);
                        $license_diff_  =   abs(round($license_diff / 86400));

                        if($license_to<$current_date)
                        {
                            $lic_neg    =   '-';
                        }
                        else
                        {
                            $lic_neg    =   '';
                        }

                        $installation_diff   =   strtotime($current_date) - strtotime($installation_to);
                        $installation_diff_  =   abs(round($installation_diff / 86400));

                        if($installation_to<$current_date)
                        {
                            $ins_neg    =   '-';
                        }
                        else
                        {
                            $ins_neg    =   '';
                        }

                        $software = array(
                            'software_license' => $software_license,
                            'license_from' => $license_from,
                            'license_to' => $license_to,
                            'license_days' => $license_days,
                            'license_remaining_days' => $lic_neg.$license_diff_,
                            'software_installation' => $software_installation,
                            'installation_from' => $installation_from,
                            'installation_to' => $installation_to,
                            'installation_days' => $installation_days,
                            'installation_remaining_days' => $ins_neg.$installation_diff_,
                        );

                        view()->share('software',$software);

                    // print_r($neg); exit;
                    }
                    else
                    {
                        $software = array(
                            'software_license' => '',
                            'license_from' => '',
                            'license_to' => '',
                            'license_days' => '',
                            'license_remaining_days' => '',
                            'software_installation' => '',
                            'installation_from' => '',
                            'installation_to' => '',
                            'installation_days' => '',
                            'installation_remaining_days' => '',
                        );
                        view()->share('software',$software);
                    }

                }
            });


        }

        view()->composer('*', function ($view)
        {
            if(Auth::check())
            {
                $nav_type = company_profile::select('*')->where('company_id',Auth::User()->company_id)->get();
                $view->with('currentUser', Auth::user());
                $product_features=ProductFeatures::
                    //where('company_id',Auth::user()->company_id)
                    where('deleted_at','=',NULL)
                    ->where('feature_type','=',1)
                    ->where('is_active','=',1)
                    ->orderBy('ordering','ASC')
                    ->get();

            }
            else
            {
                $nav_type = '';
                $product_features = '';
                $view->with('currentUser', null);
            }

            if($nav_type == '')
            {
                $nav_type   =   [];
                $product_features   =   [];
                view()->share(['nav_type' =>$nav_type,'product_features' =>$product_features]);

            }
            else
            {

                view()->share(['nav_type' => $nav_type,'product_features' =>$product_features]);
            }
        });

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
