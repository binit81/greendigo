<?php

$show_dynamic_feature = '';

?>

<table id="view_billproduct_recordtable" class="table tablesaw table-bordered table-hover table-striped mb-0"  data-tablesaw-sortable data-tablesaw-minimap data-tablesaw-mode-switch>

<thead>

<tr class="blue_Head">
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Action</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Product Name</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Barcode</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">HSN</th>

    <?php
    $dynamic_cnt = 0;


    if (isset($product_features) && $product_features != '' && !empty($product_features))
    {
    foreach ($product_features AS $feature_key => $feature_value)
    {
    if ($feature_value['show_feature_url'] != '' && $feature_value['show_feature_url'] != 'NULL' && $feature_value['show_feature_url'] != null)
    {
        $search = 'view_flatproducts';

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
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Offer Price</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6">Discount Percent</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">Valid from Date</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">Valid to Date</th>
</tr>
</thead>
<tbody>




<?php

 if(sizeof($discount_master) != 0)
 {

?>
@foreach($discount_master AS $discountkey=>$discount_value)
<?php if ($discountkey % 2 == 0) {
    $tblclass = 'even';
} else {
    $tblclass = 'odd';
}
if($discount_value['product']['supplier_barcode']!='' || $discount_value['product']['supplier_barcode']!=NULL)
{
   $barcode = $discount_value['product']['supplier_barcode'];
}
else
{
  $barcode = $discount_value['product']['product_system_barcode'];
}


$uqc_name = '';
if($discount_value['product']['uqc_id'] != '' && $discount_value['product']['uqc_id'] != null && $discount_value['product']['uqc_id'] != 0)
{
    $uqc_name = $discount_value['product']['uqc']['uqc_shortname'];
}

$feature_show_val = "";
if($show_dynamic_feature != '')
{
    $feature = explode(',',$show_dynamic_feature);

    foreach($feature AS $fea_key=>$fea_val)
    {
        $feature_show_val .= '<td class="leftAlign">'.$discount_value['product'][$fea_val].'</td>';
    }
}



?>

<tr id="">
    <td>
    
            <a id="deleteflatdiscount_{{$discount_value->discount_master_id}}" onclick="return deleteflatdiscount(this);" style="text-decoration:none !important;" target="_blank" title="Delete"><i class="fa fa-trash" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;"></i></a>
           
    </td>
    <td class="leftAlign">{{$discount_value['product']['product_name']}}</td>
    <td class="leftAlign">{{$barcode}}</td>
    <td class="leftAlign">{{$discount_value['product']['hsn_sac_code']!=0?$discount_value['product']['hsn_sac_code']:''}}</td>
    <?php

    echo $feature_show_val;

    ?>
    <td class="leftAlign"><?php echo $uqc_name?></td>
    <td class="rightAlign">{{$discount_value['mrp']}}</td>
    <td class="rightAlign">{{$discount_value['discount_percent']}}</td>
    <td class="centerAlign">{{$discount_value['from_date']}}</td>
    <td class="centerAlign">{{$discount_value['to_date']}}</td>
</tr>




@endforeach



<tr>

 <td colspan="18" align="center">
       {!! $discount_master->links() !!}
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
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="datewise_flatdiscount_detail" />
<script type="text/javascript">
$(document).ready(function(e){



});
</script>