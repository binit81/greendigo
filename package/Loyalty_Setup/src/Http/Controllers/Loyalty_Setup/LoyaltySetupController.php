<?php

namespace Retailcore\Loyalty_Setup\Http\Controllers\Loyalty_Setup;

use App\Http\Controllers\Controller;
use Retailcore\Loyalty_Setup\Models\Loyalty_Setup\loyalty_setup;

use Illuminate\Http\Request;
use Auth;
use DB;

use Log;

class LoyaltySetupController extends Controller
{

    public function index()
    {
        $loyalty_setup_data = loyalty_setup::where('company_id',Auth::User()->company_id)->where('is_active',1)->first();


        return view('loyalty_setup::loyalty_setup',compact('loyalty_setup_data'));
    }

    public function add_loyalty_setup(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $data = $request->all();

        $loyalty_setup_data =  array();
        parse_str($data['formdata'], $loyalty_setup_data);
        $loyalty_setup_data = preg_replace('/\s+/', ' ', $loyalty_setup_data);
        $company_id = Auth::User()->company_id;
        $created_by = Auth::User()->user_id;


        try{
            DB::beginTransaction();

            $loyalty_setup = loyalty_setup::updateOrCreate(
                ['loyalty_setup_id' => $loyalty_setup_data['loyalty_setup_id'],
                    'company_id'=>$company_id,],
                [
                    'created_by' =>$created_by,
                    'company_id'=>$company_id,
                    'schedule_date' => (isset($loyalty_setup_data['schedule_date'])?$loyalty_setup_data['schedule_date'] : ''),
                    'expiry_date' => (isset($loyalty_setup_data['expiry_date'])?$loyalty_setup_data['expiry_date'] : ''),
                    'purchase_amount' => (isset($loyalty_setup_data['purchase_amount'])?$loyalty_setup_data['purchase_amount'] : '0'),
                    'points' => (isset($loyalty_setup_data['points'])?$loyalty_setup_data['points'] : '0'),
                    'points_amount' => (isset($loyalty_setup_data['points_amount'])?$loyalty_setup_data['points_amount'] : '0'),
                    'redeem_point' => (isset($loyalty_setup_data['redeem_point'])?$loyalty_setup_data['redeem_point'] : '0'),
                    'is_active' => "1"
                ]
            );

         DB::commit();
        }catch (\Illuminate\Database\QueryException $e)
        {
            DB::rollback();
            return json_encode(array("Success"=>"False","Message"=>$e->getMessage()));
        }

        if($loyalty_setup)
        {
            if ($loyalty_setup_data['loyalty_setup_id'] != '')
            {
                return json_encode(array("Success"=>"True","Message"=>"Loyalty Setup has been successfully updated."));
            }
            else
            {
                return json_encode(array("Success"=>"True","Message"=>"Loyalty Setup has been successfully added."));
            }
        }
        else
        {
            return json_encode(array("Success"=>"False","Message"=>"Something Went Wrong"));
        }
    }


}
