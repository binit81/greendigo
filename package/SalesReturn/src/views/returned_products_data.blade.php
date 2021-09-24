
<table class="table  table-bordered table-hover  mb-0" data-tablesaw-sortable data-tablesaw-sortable-switch data-tablesaw-no-labels>

    
<thead>
<tr class="blue_Head">
<th class="pa-10 leftAlign" width="6.33%">Bill No.</th>
<th width="8.33%">Bill Date</th>
<th width="11.33%">Product Name</th>
<th width="8.33%">Barcode</th>
<th width="8.33%">SKU</th>
<?php
    $show_dynamic_feature = '';
    if (isset($product_features) && $product_features != '' && !empty($product_features))
    {

    foreach ($product_features AS $feature_key => $feature_value)

    {

    if ($feature_value['show_feature_url'] != '' && $feature_value['show_feature_url'] != 'NULL' && $feature_value['show_feature_url'] != null)
    {
    $search ='returned_products';

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
            ?>

                <th width="8.33%"><?php echo $feature_value['product_features_name']?></th>
                <?php 
            } ?>
            <?php
       }
    }
    }
?>

<?php if($nav_type[0]['bill_calculation'] != 2) {  ?>
<th width="6.33%" class="centerAlign">MRP</th>
<!-- <th width="6.33%" class="centerAlign">Selling  Price</th> -->
    <?php } ?>
<th width="3.33%">Qty</th>
<th width="12.33%" class="centerAlign">Restock / Damage</th>
<th width="10.33%">Remarks</th>
<th width="6.33%">Action</th>
</tr>
</thead>
<tbody id="view_bill_record">



@foreach($returnproducts AS $returnkey=>$return_value)

<?php

 if ($returnkey % 2 == 0) {
    $tblclass = 'even';
} else {
    $tblclass = 'odd';
}
$feature_show_val = "";
if($show_dynamic_feature != '')
{
    $feature = explode(',',$show_dynamic_feature);

    foreach($feature AS $fea_key=>$fea_val)
    {
        $feature_show_val .= '<td>'.$return_value['product'][$fea_val].'</td>';
    }
}

 if($return_value['product']['supplier_barcode']!='' && $return_value['product']['supplier_barcode']!=NULL)
  {
    $barcode     =     $return_value['product']['supplier_barcode'];
  }
  else
  {
    $barcode     =    $return_value['product']['product_system_barcode'];
  }

  $restqty    =   $return_value['qty']  - $return_value['totalrdqty'];

  if($return_value['sales_products_detail_id'] != '' && $return_value['sales_products_detail_id'] != NULL)
  {
    $billno    =   $return_value['return_bill']['sales_bill']['bill_no'];
  }
  if($return_value['consign_products_detail_id'] != '' && $return_value['consign_products_detail_id'] != NULL)
  {
    $billno    =   $return_value['return_bill']['consign_bill']['bill_no'];
  }

?>

<tr id="viewbill_{{$return_value['return_product_detail_id']}}" class="<?php echo $tblclass ?>">

    <td class="leftAlign">{{$billno}}</td>
    <td class="leftAlign">{{$return_value['return_bill']['bill_date']}}</td>
    <td class="leftAlign">{{$return_value['product']['product_name']}}</td>
    <td class="leftAlign">{{$barcode}}</td>
    <td class="leftAlign">{{$return_value['product']['sku_code']}}</td>
     <?php
        echo $feature_show_val;
     ?>
    <?php if($nav_type[0]['bill_calculation'] != 2) {  ?>
    <td style="text-align:right !important;">{{round($return_value['mrp'],2)}}</td>
    <!-- <td style="text-align:right !important;">{{round($return_value['sellingprice_before_discount'],2)}}</td> -->
    <?php } ?>
    <td style="text-align:right !important;" id="returnqty_{{$return_value['return_product_detail_id']}}">{{$restqty}}</td>
    <td class="leftAlign" style="text-align:center !important;">
        <input type="hidden" id="inwardids_{{$return_value['return_product_detail_id']}}" value="{{$return_value['inwardids']}}">
        <input type="hidden" id="inwardqtys_{{$return_value['return_product_detail_id']}}" value="{{$return_value['inwardqtys']}}">
        <input type="text" id="restock_{{$return_value['return_product_detail_id']}}" onkeyup="return restockqty(this);" class="form-control mt-15" style="width:48% !important;margin:0 3px 0 0;">
        <input type="text" id="damage_{{$return_value['return_product_detail_id']}}" onkeyup="return damageqty(this);" class="form-control mt-15" style="width:48%  !important;">
        <input type="hidden" id="pricemasterid_{{$return_value['return_product_detail_id']}}" class="form-control mt-15" style="width:49%  !important;" value="{{$return_value['price_master_id']}}">
        <input type="hidden" id="productid_{{$return_value['return_product_detail_id']}}" class="form-control mt-15" style="width:49%  !important;" value="{{$return_value['product_id']}}">
        <input type="hidden" id="salesproductid_{{$return_value['return_product_detail_id']}}" class="form-control mt-15" style="width:49% !important;" value="{{$return_value['sales_products_detail_id']}}"></td>
    <td class="leftAlign" style="text-align:center !important;">
        <textarea id="remarks_{{$return_value['return_product_detail_id']}}" rows="2" style="width:100%;border-radius:5px;border:1px solid #ced4da;"></textarea>
    </td>
    <?php
    if($role_permissions['permission_edit']==1)
    {
    ?>
        <td class="leftAlign" style="text-align:center !important;"><button type="button" class="btn btn-info" name="addbilling" id="addreturnproducts_{{$return_value['return_product_detail_id']}}" onclick="return savereturn(this);" style="padding: 0rem .5rem !important;">Update</button></td>
    <?php
    }
    ?>

</tr>


@endforeach
<tr>
    <td colspan="12" align="center">
        {!! $returnproducts->links() !!}
    </td>
</tr>

</tbody>
</table>

<script type="text/javascript">
    $(".PagecountResult").html('{{$returnproducts->total()}}');
    $(".PagecountResult").addClass("itemfocus");
</script>