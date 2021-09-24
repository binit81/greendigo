<?php

namespace Retailcore\Referral_Points\Http\Controllers\referral_points;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Maatwebsite\Excel\Exceptions\ConcernConflictException;
use Retailcore\Referral_Points\Models\referral_points\referral_point;
use Illuminate\Http\Request;
use Auth;
use DB;
use Log;


class ReferralPointController extends Controller
{
    public function index()
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $referral_points = referral_point::where('company_id',Auth::user()->company_id)->get();
        return view('referral_points::referral_points',compact('referral_points'));
    }

    public function add_referral_point(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);

        $data = $request->all();


        if(isset($data) && !empty($data))
        {
            try {
                DB::beginTransaction();
                foreach ($data as $key => $value) {


                    $referral_insert = referral_point::updateOrCreate(
                      [
                          'referral_point_id' => $value['referral_point_id'],
                          'company_id' => Auth::User()->company_id,
                      ],
                        $value
                    );
                }
                DB::commit();
                return json_encode(array("Success"=>"True","Message"=>"Referral Points Successfully Updated."));
            }catch (\Illuminate\Database\QueryException $e)
            {
                DB::rollback();
                return json_encode(array("Success" => "False", "Message" => $e->getMessage()));
                exit;
            }
        }
    }
}
