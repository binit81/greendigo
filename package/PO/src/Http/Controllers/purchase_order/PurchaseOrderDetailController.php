<?php

namespace Retailcore\PO\Http\Controllers\Purchase_order;
use App\Http\Controllers\Controller;


use Retailcore\Products\Models\product\product;


use Retailcore\PO\Models\purchase_order\purchase_order;
use Retailcore\PO\Models\purchase_order\purchase_order_detail;
use Illuminate\Http\Request;
use Auth;
use Retailcore\Products\Models\product\ProductFeatures;
use Log;
class PurchaseOrderDetailController extends Controller
{

    public function view_po_detail(Request $request)
    {
        Log::info(Auth::User()->user_id.'::'.Auth::User()->employee_firstname.'::'.$_SERVER['REMOTE_ADDR'].'===>'.__METHOD__. ' Line No '.__LINE__.''.PHP_EOL);
        $purchase_order_id = decrypt($request->purchase_order_id);

        $purchase_order_detail = purchase_order::where('purchase_order_id','=',$purchase_order_id)
         ->where('company_id',Auth::user()->company_id)
         ->where('deleted_at','=',NULL)
         ->with('purchase_order_detail.product.uqc')
         ->with('purchase_order_detail.product.product_features_relationship')
         ->with('supplier_gstdetail')
         ->get();
        $product_features =  ProductFeatures::getproduct_feature('');



        foreach ($purchase_order_detail[0]['purchase_order_detail'] AS $key=>$v) {


            if (isset($v['product']['product_features_relationship']) && $v['product']['product_features_relationship'] != '') {
                foreach ($product_features AS $kk => $vv) {
                    $html_id = $vv['html_id'];

                    if ($v['product']['product_features_relationship'][$html_id] != '' && $v['product']['product_features_relationship'][$html_id] != NULL) {

                        $nm = product::feature_value($vv['product_features_id'], $v['product']['product_features_relationship'][$html_id]);

                        $purchase_order_detail[0]['purchase_order_detail'][$key]['product'][$html_id] = $nm;
                    }
                }
            }
        }




        $data = json_encode($purchase_order_detail);


          //TO GET NEXT ID OF SELECTED ID IN POPUP
        $next_id = purchase_order::where('purchase_order_id', '>', $purchase_order_id)->min('purchase_order_id');

         $next = '';
         if(isset($next_id) && $next_id != '')
         {
            $next = encrypt($next_id);
         }

         //TO GET PREVIOUS ID OF SELECTED ID IN POPUP
        $previous = purchase_order::where('purchase_order_id', '<', $purchase_order_id)->max('purchase_order_id');


         $prev = '';
            if(isset($previous) && $previous != '')
            {
                $prev = encrypt($previous);
            }


        return json_encode(array("Success"=>"True","Data"=>$data,"next"=>$next,"previous"=>$prev));

    }



}
