<?php

namespace Illuminate\Foundation\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\company;
use Retailcore\Company_Profile\Models\company_profile\company_profile;
use App\home_navigation;
use App\home_navigations_data;
use DB;

trait AuthenticatesUsers
{
    use RedirectsUsers, ThrottlesLogins;

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);



        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        if(!$sock = @fsockopen('www.google.com',80))
        {
            $connection     =   '0';
        }
        else
        {
            $connection     =   '1';
        }

        if($connection==1)
        {
            $company_data    =   company::where('company_id',Auth::User()->company_id)->get();

            $company_code                   =   $company_data[0]['company_code'];
            $company_project_code           =   $company_data[0]['company_project_code'];
            $company_installation_code      =   $company_data[0]['company_installation_code'];

            if($company_code!='')
            {
                $url    =   CLIENT_URL.'client_'.$company_code.'_'.$company_project_code.'_'.$company_installation_code.'.txt';
                $read    =   @file_get_contents($url);

                if($read!='')
                {
                    $client_url     =   DEFAULT_COMPANY_URL.$company_code.".txt";
                    $fp = fopen($client_url, "w");
                    fwrite($fp,$read);
                    fclose($fp);

                    $query  =   company::where('company_id', Auth::User()->company_id)
                    ->update([
                       'company_code' => $company_code,
                       'company_project_code' => $company_project_code,
                       'company_installation_code' => $company_installation_code,
                    ]);

                    $values         =   @file_get_contents($client_url);
                    $exp_values     =   explode(',',$values);

                    $tax_type               =   str_replace('tax_type=','',$exp_values[0]);   
                    $decimal_points         =   str_replace('decimal_points=','',$exp_values[1]);
                    $billtype               =   str_replace('billtype=','',$exp_values[2]);   
                    $billprint_type         =   str_replace('billprint_type=','',$exp_values[3]);   
                    $series_type            =   str_replace('series_type=','',$exp_values[4]);   
                    $navigation_type        =   str_replace('navigation_type=','',$exp_values[5]);   
                    $inward_type            =   str_replace('inward_type=','',$exp_values[6]);   
                    $inward_calculation     =   str_replace('inward_calculation=','',$exp_values[7]);                    

                    if($tax_type==2)
                    {
                        $tax_title              =   'GST';
                        $currency_title         =   '&#8377;';
                    }
                    else
                    {
                        $tax_title              =   str_replace('tax_title=','',$exp_values[13]);
                        $currency_title         =   str_replace('currency_title=','',$exp_values[14]);
                    }

                    $company_profile_id     =   company_profile::select('company_profile_id')->where('company_id',Auth::User()->company_id)->first();

                    $result  =   company_profile::where('company_profile_id', $company_profile_id->company_profile_id)
                    ->update([
                        'company_id' => Auth::User()->company_id,
                        'tax_type' => $tax_type,
                        'tax_title' => $tax_title,
                        'currency_title' => $currency_title,
                        'decimal_points' => $decimal_points,
                        'billtype' => $billtype,
                        'series_type' => $series_type,
                        'billprint_type' => $billprint_type,
                        'navigation_type' => $navigation_type,
                        'inward_calculation' => $inward_calculation,
                        'inward_type' => $inward_type,
                    ]);
                    
                    ////////////////////// TERMS & CONDITIONS ///////////////////////////////

                    $tc_url     =   TC_URL.'rc_terms_and_conditions.html';
                    $read_    =   @file_get_contents($tc_url);

                    if($read_!='')
                    {
                        $t_and_c_url    =   DEFAULT_COMPANY_URL.'rc_terms_and_conditions.html';

                        $fp_ = fopen($t_and_c_url, "w");
                        fwrite($fp_,$read_);
                        fclose($fp_);
                    }

                    ////////////////////// TERMS & CONDITIONS ///////////////////////////////

                    // $result     =   company_profile::updateOrCreate([
                    //     'company_profile_id' => $company_profile_id->company_profile_id,
                    //     'company_id' => Auth::User()->company_id,
                    //     'tax_type' => $tax_type,
                    //     'tax_title' => $tax_title,
                    //     'currency_title' => $currency_title,
                    //     'decimal_points' => $decimal_points,
                    //     'billtype' => $billtype,
                    //     'series_type' => $series_type,
                    //     'billprint_type' => $billprint_type,
                    //     'navigation_type' => $navigation_type,
                    //     'inward_calculation' => $inward_calculation,
                    //     'inward_type' => $inward_type,
                    //     'state_id' => NULL,
                    //     'country_id' => '102',
                    //     'created_by' => Auth::User()->user_id,
                    //     'modified_by' => NULL,
                    // ]);

                    if($inward_type ==1)
                    {
                        //fmcg
                        $hide[0]['module_name'] = 'inward_stock_show';
                        $hide[0]['active'] = 0;

                        $hide[1]['module_name'] = 'inward_stock';
                        $hide[1]['active'] = 1;

                        $hide[2]['module_name'] = 'batch_no_wise_report';
                        $hide[2]['active'] = 1;

                    }
                    if($inward_type ==2)
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
                            home_navigations_data::where('company_id',Auth::User()->company_id)
                            ->where('nav_url',$item['module_name'])
                            ->update(array(
                               'is_active' => $item['active'],
                                'modified_by' => Auth::User()->user_id
                            ));
                        }
                    }
                }
            }
           
        }

        

        return $this->authenticated($request, $this->guard()->user())
                ?: redirect()->intended($this->redirectPath());
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        //
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'email';
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return $this->loggedOut($request) ?: redirect('/');
    }

    /**
     * The user has logged out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        //
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }
}
