<?php
if($nav_type[0]['bill_calculation']==1)
{
  $billing_calculation_case  = "";
}
else
{
  $billing_calculation_case  = "display:none;";
}
$show_dynamic_feature = '';

?>

<table id="view_billproduct_recordtable" class="table tablesaw table-bordered table-hover table-striped mb-0"  data-tablesaw-sortable data-tablesaw-minimap data-tablesaw-mode-switch>

<thead>

<tr class="blue_Head">
     <?php
     if(sizeof($get_store)!=0)
     {
     ?>  
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Location</th>
    <?php
    }
    ?>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Bill No.</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Bill Date</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Customer</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Product Name</th>

    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Barcode</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">HSN</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">PCode</th>
    <?php
    $dynamic_cnt = 0;
    if (isset($product_features) && $product_features != '' && !empty($product_features))
    {
    foreach ($product_features AS $feature_key => $feature_value)
    {
    if ($feature_value['show_feature_url'] != '' && $feature_value['show_feature_url'] != 'NULL' && $feature_value['show_feature_url'] != null)
    {
    $search =$urlData['breadcrumb'][0]['nav_url'];

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

    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">UQC</th>

    <?php
    if($nav_type[0]['billtype']==3)
    {
        ?><th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Batch No.</th><?php
    }
    ?>

    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6" style="{{$billing_calculation_case}}">SellingPrice</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">Qty</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8" style="{{$billing_calculation_case}}">Disc.%</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="9" style="{{$billing_calculation_case}}">Disc. Amount</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="10" style="{{$billing_calculation_case}}">Overall Discount</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="11" style="{{$billing_calculation_case}}">Taxable Amount</th>
    <?php
    if($tax_type==1)
    {
        ?>

        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="16" style="{{$billing_calculation_case}}">{{$taxname}}%</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="17" style="{{$billing_calculation_case}}">{{$taxname}} Amount</th>
        <?php
    }
    else
    {
        ?>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="12" style="{{$billing_calculation_case}}">CGST%</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="13" style="{{$billing_calculation_case}}">CGST Amount</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="14" style="{{$billing_calculation_case}}">SGST%</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="15" style="{{$billing_calculation_case}}">SGST Amount</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="16" style="{{$billing_calculation_case}}">IGST%</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="17" style="{{$billing_calculation_case}}">IGST Amount</th>
        <?php
    }
    ?>

    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="18" style="{{$billing_calculation_case}}">Total Amount</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="18">Reference</th>

</tr>
</thead>
<tbody>




<?php

 if(sizeof($sales_room_details) != 0)
 {

?>
@foreach($sales_room_details AS $saleskey=>$sales_value)
<?php if ($saleskey % 2 == 0) {
    $tblclass = 'even';
} else {
    $tblclass = 'odd';
}
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

<tr id="">
    <?php
     if(sizeof($get_store)!=0)
     {
     ?> 
     <td>{{$companyname}}</td>
     <?php
     }
     ?>
    <td class="leftAlign">{{$sales_value->sales_bill->bill_no}}</td>
    <td class="leftAlign">{{$sales_value->sales_bill->bill_date}}</td>
    <td class="leftAlign">{{$sales_value->sales_bill['customer']['customer_name']}}</td>
    <td class="leftAlign">{{$sales_value['product']['product_name']}}</td>
    <td class="leftAlign">{{$barcode}}</td>
    <td class="leftAlign">{{$sales_value['product']['hsn_sac_code']!=0?$sales_value['product']['hsn_sac_code']:''}}</td>
    <td class="leftAlign">{{$sales_value['product']['product_code']}}</td>
    <?php

    echo $feature_show_val;

    ?>
    <td class="leftAlign"><?php echo $uqc_name?></td>
    <?php
    if($nav_type[0]['billtype']==3)
    {
        ?><td class="leftAlign">{{$sales_value['batchprice_master']['batch_no']}}</td><?php
    }
    ?>
    <td class="rightAlign" style="{{$billing_calculation_case}}">{{number_format($sales_value->sellingprice_before_discount,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign">{{$sales_value->qty}}</td>
    <td class="rightAlign" style="{{$billing_calculation_case}}">{{$sales_value->discount_percent}}</td>
    <td class="rightAlign" style="{{$billing_calculation_case}}">{{number_format($sales_value->discount_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign" style="{{$billing_calculation_case}}">{{number_format($sales_value->overalldiscount_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign" style="{{$billing_calculation_case}}">{{number_format($sales_value->sellingprice_afteroverall_discount,$nav_type[0]['decimalpoints_forview'])}}</td>
    <?php
    if($tax_type==1)
    {
            ?>
             <td class="rightAlign" style="{{$billing_calculation_case}}">{{$sales_value->igst_percent}}</td>
             <td class="rightAlign" style="{{$billing_calculation_case}}">{{number_format($sales_value->igst_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
            <?php
    }
    else
    {
            if($sales_value['sales_bill']['state_id'] == $company_state)
            {
                ?>
                        <td class="rightAlign" style="{{$billing_calculation_case}}">{{$sales_value->cgst_percent}}</td>
                        <td class="rightAlign" style="{{$billing_calculation_case}}">{{number_format($sales_value->cgst_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
                        <td class="rightAlign" style="{{$billing_calculation_case}}">{{$sales_value->sgst_percent}}</td>
                        <td class="rightAlign" style="{{$billing_calculation_case}}">{{number_format($sales_value->sgst_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
                        <td class="rightAlign" style="{{$billing_calculation_case}}">0.00</td>
                        <td class="rightAlign" style="{{$billing_calculation_case}}">0.00</td>
                <?php
            }
            else
            {
                ?>
                        <td class="rightAlign" style="{{$billing_calculation_case}}">0.00</td>
                        <td class="rightAlign" style="{{$billing_calculation_case}}">0.00</td>
                        <td class="rightAlign" style="{{$billing_calculation_case}}">0.00</td>
                        <td class="rightAlign" style="{{$billing_calculation_case}}">0.00</td>
                        <td class="rightAlign" style="{{$billing_calculation_case}}">{{$sales_value->igst_percent}}</td>
                        <td class="rightAlign" style="{{$billing_calculation_case}}">{{number_format($sales_value->igst_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
                <?php
            }
    }

    ?>


    <td class="rightAlign bold" style="{{$billing_calculation_case}}">{{number_format($sales_value->total_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign">{{$sales_value['sales_bill']['reference']['reference_name']}}</td>
</tr>




@endforeach

@foreach($return_room_details AS $returnkey=>$return_value)
<?php
if($return_value['product']['supplier_barcode']!='' || $return_value['product']['supplier_barcode']!=NULL)
{
   $barcode = $return_value['product']['supplier_barcode'];
}
else
{
  $barcode = $return_value['product']['product_system_barcode'];
}

$uqc_name = '';
if($return_value['product']['uqc_id'] != '' && $return_value['product']['uqc_id'] != null && $return_value['product']['uqc_id'] != 0)
{
    $uqc_name = $return_value['product']['uqc']['uqc_shortname'];
}
$feature_show_val = "";
if($show_dynamic_feature != '')
{
    $feature = explode(',',$show_dynamic_feature);

    foreach($feature AS $fea_key=>$fea_val)
    {
        $feature_show_val .= '<td class="leftAlign">'.$return_value['product'][$fea_val].'</td>';
    }
}

?>
<tr id="" style="background:#ffcfbe !important;">
    <?php
     if(sizeof($get_store)!=0)
     {
     ?> 
     <td>{{$companyname}}</td>
     <?php
     }
     ?>
    <td class="leftAlign">{{$return_value['return_bill']['sales_bill']['bill_no']}}</td>
    <td class="leftAlign">{{$return_value['return_bill']['bill_date']}}</td>
    <td class="leftAlign">{{$return_value['customer']['customer_name']}}</td>
    <td class="leftAlign">{{$return_value['product']['product_name']}}</td>
    <td class="leftAlign">{{$barcode}}</td>
    <td class="leftAlign">{{$return_value['product']['hsn_sac_code']}}</td>
    <td class="leftAlign">{{$return_value['product']['product_code']}}</td>
    <?php

    echo $feature_show_val;

    ?>

    <td class=" leftAlign"><?php echo $uqc_name?></td>
    <?php
    if($nav_type[0]['billtype']==3)
    {
        ?><td class="leftAlign">{{$return_value['rbatchprice_master']['batch_no']}}</td><?php
    }
    ?>
    <td class="rightAlign " style="{{$billing_calculation_case}}">{{number_format($return_value->sellingprice_before_discount,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign ">{{$return_value->qty}}</td>
    <td class="rightAlign " style="{{$billing_calculation_case}}">{{$return_value->discount_percent}}</td>
    <td class="rightAlign " style="{{$billing_calculation_case}}">{{number_format($return_value->discount_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign " style="{{$billing_calculation_case}}">{{number_format($return_value->overalldiscount_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign " style="{{$billing_calculation_case}}">{{number_format($return_value->sellingprice_afteroverall_discount,$nav_type[0]['decimalpoints_forview'])}}</td>
    <?php
    if($tax_type==1)
    {
            ?>
            <td class="rightAlign " style="{{$billing_calculation_case}}">{{$return_value->igst_percent}}</td>
            <td class="rightAlign " style="{{$billing_calculation_case}}">{{number_format($return_value->igst_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
            <?php
    }
    else
    {
            if($return_value['return_bill']['state_id'] == $company_state)
            {
                ?>
                        <td class="rightAlign " style="{{$billing_calculation_case}}">{{$return_value->cgst_percent}}</td>
                        <td class="rightAlign " style="{{$billing_calculation_case}}">{{number_format($return_value->cgst_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
                        <td class="rightAlign " style="{{$billing_calculation_case}}">{{$return_value->sgst_percent}}</td>
                        <td class="rightAlign " style="{{$billing_calculation_case}}">{{number_format($return_value->sgst_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
                        <td class="rightAlign " style="{{$billing_calculation_case}}">0.00</td>
                        <td class="rightAlign " style="{{$billing_calculation_case}}">0.00</td>
                <?php
            }
            else
            {
                ?>
                        <td class="rightAlign " style="{{$billing_calculation_case}}">0.00</td>
                        <td class="rightAlign " style="{{$billing_calculation_case}}">0.00</td>
                        <td class="rightAlign " style="{{$billing_calculation_case}}">0.00</td>
                        <td class="rightAlign " style="{{$billing_calculation_case}}">0.00</td>
                        <td class="rightAlign " style="{{$billing_calculation_case}}">{{$return_value->igst_percent}}</td>
                        <td class="rightAlign " style="{{$billing_calculation_case}}">{{number_format($return_value->igst_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
                <?php
            }
    }

    ?>


    <td class="rightAlign bold" style="{{$billing_calculation_case}}">{{number_format($return_value->total_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign ">{{$return_value['return_bill']['reference']['reference_name']}}</td>

</tr>




@endforeach

<tr>

 <td colspan="18" align="center">
       {!! $sales_room_details->links() !!}
    </td>
</tr>

<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>
<?php
}

else
{
        ?>
            <tr>
            <td colspan="18" class="leftAlign">
            <b style="font-size:16px;">No Records Found!</b>
            </td>
            </tr>
        <?php
}
?>
</tbody>
</table>
<input type="hidden" name="hidden_page" id="hidden_page" value="1" />
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value="sales_bill_id" />
<input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="DESC" />
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="datewise_product_billdetail" />
<script type="text/javascript">
$(document).ready(function(e){


    $('.taxabletariff').html({{round($todaytaxable,$nav_type[0]['decimal_points'])}});
    $('.totalcgst').html({{round($todaycgst,$nav_type[0]['decimal_points'])}});
    $('.totalsgst').html({{round($todaysgst,$nav_type[0]['decimal_points'])}});
    $('.totaligst').html({{round($todayigst,$nav_type[0]['decimal_points'])}});
    $('.grandtotal').html({{round($todaygrand,$nav_type[0]['decimal_points'])}});


});
</script>