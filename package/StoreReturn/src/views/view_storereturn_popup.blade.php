<?php
/**
 * Created by PhpStorm.
 * User: Hemaxi
 * Date: 26/3/19
 * Time: 10:18 AM
 */

if(sizeof($store_return) != 0)
{
  

  
?>
<style type="text/css">
 

/* if the browser window is at least 800px-s wide: */
@media screen and (min-width: 800px) {
  table {
  width: 90%;}
}

/* if the browser window is at least 1000px-s wide: */
@media screen and (min-width: 1000px) {
  table {
  width: 80%;}
}

  
</style>
<?php
if($nav_type[0]['bill_calculation']==1)
{
  $billing_calculation_case  = "";
}
else
{
  $billing_calculation_case  = "display:none;";
}

?>

<form id="popupbills">
      <!-- <div class="billdata"> -->
      <div class="invoice-to-wrap pb-20">
      <div class="row">
      <div class="col-xl-12">

      <div class="invoice-from-wrap">

      <div class="row" style="margin:20px 0 0 0;">


      </div>
      </div>

      <div class="invoice-to-wrap pb-20">
      <div class="row">
      <div class="col-md-7 mb-30" style="width:50% !important;border:0px solid !important;font-size:16px;">
      <input type="hidden" id="maxid" value="{{$maxsales_id}}">
      <input type="hidden" id="minid" value="{{$minsales_id}}">
      <input type="hidden" id="fetchedbillno" value="{{$store_return[0]['store_return_id']}}">
      <textarea id="encryptbillid" style="display:none;">{{encrypt($store_return[0]['store_return_id'])}}</textarea>
      <input type="hidden" id="returnid" value="">
      <input type="hidden" name="view_bill_type" id="view_bill_type" value=""> 

      <table style="float:left;width:auto !important;">
      <tr>
        <th colspan="3">To</th>
      </tr>
      <tr>
      <td class="d-block text-dark font-14">Warehouse</td>
      <td class="font-weight-600">&nbsp;:&nbsp;</td>
      <td class="text-dark font-14 font-weight-600"><span class="customer_name">{{$store_return[0]['company']['company_name']}}</span></td>
      </tr>
      
      <tr>
      <td class="d-block text-dark font-14">Mobile</td>
      <td class="font-weight-600">&nbsp;:&nbsp;</td>
      <td class="text-dark font-14 font-weight-600"><span class="customer_mobile">{{$store_return[0]['company']['personal_mobile_no']}}</span></td>
      <td>
      </td>
      </tr>
      
      <tr>
      <td class="d-block text-dark font-14">Address</td>
      <td class="font-weight-600">&nbsp;:&nbsp;</td>
      <td class="text-dark font-14 font-weight-600"><span class="customer_address">{{strip_tags($store_return[0]['company']['company_address'])}}</span></td>
      </tr>
     
      

      </table>

<?php  ?>

      </div>
      <div class="col-md-5 mb-30" style="width:50% !important;border:0px solid !important;font-size:16px;">

      <table style="float:right;width:auto !important;">
      <tr>
      <td class="d-block text-dark font-14">Return No.</td>
      <td class="font-weight-600">&nbsp;:&nbsp;</td>
      <td class="text-dark font-14 font-weight-600 text-right"><span class="tinvoiceno">{{$store_return[0]['return_no']}}</span></td>
      </tr>
      <tr>
      <td class="d-block text-dark font-14">Return Date</td>
      <td class="font-weight-600">&nbsp;:&nbsp;</td>
      <td class="text-dark font-14 font-weight-600 text-right"><span class="invoicedate">{{$store_return[0]['return_date']}}</span></td>
      </tr>
      
      <?php
     
      $username  = $store_return[0]['user']['employee_firstname'].' '.$store_return[0]['user']['employee_lastname'].' '.$store_return[0]['user']['employee_middlename'];
     ?>
      <tr>
      <td class="d-block text-dark font-14">Cashier Name</td>
      <td class="font-weight-600">&nbsp;:&nbsp;</td>
      <td class="text-dark font-14 font-weight-600 text-right"><span class="invoicedate">{{$username}}</span></td>
      </tr>
      <tr>

      </tr>

      </table>

      </div>
      </div>
      </div>
          <h5 class="hk-sec-title">
              <small class="badge badge-soft-danger mt-15 mr-10"><b>No.of Items:</b>
                  <span class="totcount">0</span>
              </small>
          </h5>
     <section class="hk-sec-wrapper">
    <div class="table-wrap">
     <div class="table-responsive" >
      <!--  <tr style="background:#88c241;border-bottom:1px #999 solid;border:1px #f3f3f3 solid;"> -->
      <table  class="table tablesaw table-bordered table-hover table-striped mb-0"  data-tablesaw-sortable data-tablesaw-minimap data-tablesaw-mode-switch cellpadding="6" border="0" frame="box"  >
      <thead>
      <tr class="blue_Head">
      <th class="text-dark font-14 font-weight-600" style="">Sr.No.</th>
      <th class="text-dark font-14 font-weight-600" style="">Item Description</th>
      <?php
      $show_dynamic_feature = '';
       $dynamic_cnt = 0;
    if (isset($product_features) && $product_features != '' && !empty($product_features))
    {
    foreach ($product_features AS $feature_key => $feature_value)
    {
    if ($feature_value['show_feature_url'] != '' && $feature_value['show_feature_url'] != 'NULL' && $feature_value['show_feature_url'] != null)
    {
    $search = 'view_bill';

    if (strstr($feature_value['show_feature_url'],$search) )
    {
    if($show_dynamic_feature == '')
    {
        $show_dynamic_feature =$feature_value['html_id'];
    }
    else
    {
        $show_dynamic_feature = $show_dynamic_feature.','.$feature_value['html_id'];
    }
     $dynamic_cnt++;
    ?>

    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist"><?php echo $feature_value['product_features_name']?></th>
    <?php } ?>
    <?php

    }
    }
    }
    ?>
      <th class="text-dark font-14 font-weight-600" style="">PCode</th>
      <th class="text-dark font-14 font-weight-600" style="">UQC</th>
      <th class="text-dark font-14 font-weight-600" style="">Barcode</th>
      <th class="text-right text-dark font-14 font-weight-600">Qty</th>
      
      </tr>
      </thead>
      <tbody id="productdetails">

      <?php
      $totsellingafterdiscount = 0;
      $totcgstamount = 0;
      $totsgstamount=0;
      $totigstamount=0;
      if($store_return[0]['storereturn_product']!='')
      {

      foreach($store_return[0]['storereturn_product'] as $saleskey=>$sales_value)
      {
      if ($saleskey % 2 == 0) {
      $tblclass = 'even';
      } else {
      $tblclass = 'odd';
      }
      $sr   =  $saleskey + 1;
     
        if($sales_value['product']['supplier_barcode']!='' || $sales_value['product']['supplier_barcode']!=NULL)
        {
        $barcode = $sales_value['product']['supplier_barcode'];
        }
        else
        {
        $barcode = $sales_value['product']['product_system_barcode'];
        }

     
     
      $uqc_name = '';
      if($sales_value['product']['uqc_id'] != '' && $sales_value['product']['uqc_id'] != null && $sales_value['product']['uqc_id'] != 0)
      {
          $uqc_name = $sales_value['product']['uqc']['uqc_shortname'];
      }

      $feature_show_val = "";
      if($show_dynamic_feature != '')
      {
          $feature = explode(',',$show_dynamic_feature);

          foreach($feature AS $fea_key=>$fea_val)
          {
              $feature_show_val .= '<td class="leftAlign">'.$sales_value['product'][$fea_val].'</td>';
          }
      }

      ?>
      <tr class="<?php echo $tblclass ?>" style="border-bottom:1px solid #C0C0C0 !important;">

      <td style="font-size:14px !important;">{{$sr}}</td>
      <td style="font-size:14px !important;text-align:left !important;">{{$sales_value['product']['product_name']}}</td>
      <?php

      echo $feature_show_val;

      ?>
      <td style="font-size:14px !important;text-align:left !important;">{{$sales_value['product']['product_code']}}</td>
      
      <td style="font-size:14px !important;text-align:left !important;"><?php echo $uqc_name?></td>
      <td style="font-size:14px !important;text-align:left !important;">{{$barcode}}</td>
      <td style="text-align:right !important;font-size:14px !important;">{{$sales_value['qty']}}</td>
      
      </tr>


      <?php
      }
      }
      ?>

      </tbody>
      <tfoot style="border-bottom:1px solid #C0C0C0 !important;border-top:1px solid #C0C0C0 !important;">
      <tr>
     <?php $colspan = 5 + $dynamic_cnt?>
      <th colspan="{{$colspan}}" class="text-right text-dark font-14 font-weight-600">Total</th>
      <th class="text-right text-dark font-14 font-weight-600">{{round($store_return[0]['total_qty'],2)}}</th>
      


      </tr>
      </tfoot>
      </table>
</div>
</div><!--table-wrap-->
</section>

      </div>
      </div>
      </div>

<script type="text/javascript">

$(document).ready(function(e){


    $('.invoiceno').html(<?php echo json_encode($store_return[0]['return_no']);?>);
    var bill_no                 =     $('#fetchedbillno').val();
    var maxid                   =     $('#maxid').val();
    var minid                   =     $('#minid').val();
    var encryptbillid           =     $('#encryptbillid').val();
    encryptbillid               =     "'"+encryptbillid+"'";



    if(Number(bill_no) == Number(minid))
    {
            $('#previousrecord').prop('disabled', true);
            $('#nextrecord').prop('disabled', false);
            // return false;
    }
    if(Number(bill_no) == Number(maxid))
    {
            $('#previousrecord').prop('disabled', false);
             $('#nextrecord').prop('disabled', true);
            // return false;
    }

    var printhtml = '';
    if(billprint_type==1)
    {
          printhtml = '<a href="{{URL::to('print_bill')}}?id={{encrypt($store_return[0]['store_return_id'])}}" style="text-decoration:none !important;" target="_blank" title="A4/A5 Print"><i class="fa fa-print" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;"></i></a>';
    }
    else
    { 
        printhtml = '<a href="{{URL::to("thermalprint_bill")}}?id={{encrypt($store_return[0]['store_return_id'])}}" style="text-decoration:none !important;" target="_blank" title="Thermal Print"><img src="{{URL::to('/')}}/public/images/thermalprint_icon" title="Thermal Print" width="30" class="" /></a>';

    }
    
   $('.editdeleteIcons').html('<a class="edit_bill" title="Edit"><i class="fa fa-edit" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;"></i></a>'+printhtml+'<a id="deletebill_{{$store_return[0]['store_return_id']}}" onclick="return deletebill(this);" style="text-decoration:none !important;" target="_blank" title="Delete"><i class="fa fa-trash" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;"></i></a>');

   $('.edit_bill').click(function(e){
  
      var encryptbillid           =     $('#encryptbillid').val();
        encryptbillid               =     "'"+encryptbillid+"'";
   
        edit_storerreturnbill(encryptbillid);

});
$(".totcount").html('<?php echo count($store_return[0]['storereturn_product'])?>');

});
</script>
</form>
<?php
}
?>