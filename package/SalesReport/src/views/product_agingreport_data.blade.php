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
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Product Name</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Barcode</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">HSN</th>
    @foreach($age_range AS $rangekey=>$range_value)

    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5" colspan="2"><center>{{round($range_value['range_from']).'-'.round($range_value['range_to'])}} <small><b>(days)</b></small><br><small><b>Inward&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;InStock</b></center></small></th>

    @endforeach
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3" colspan="2"><center>Total</center><br><small><b>Inward&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;InStock</b></center></small></th>
</tr>
</thead>
<tbody>
<?php

 if(sizeof($products) != 0)
 {


foreach($products as $productkey=>$product_value)
{

		if($product_value['supplier_barcode']!='' || $product_value['supplier_barcode']!=NULL)
		{
		   $barcode = $product_value['supplier_barcode'];
		}
		else
		{
		  $barcode = $product_value['product_system_barcode'];
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
    <td class="leftAlign">{{$product_value['product_name']}}</td>
    <td class="leftAlign">{{$barcode}}</td>
    <td class="leftAlign">{{$product_value['hsn_sac_code']}}</td>    
    <?php
    $totalsinward  = 0;
    $totalsinstock = 0;
    foreach($age_range AS $rangekey=>$range_value) 
    {
    	$html_id   =  round($range_value['range_from']).' - '.round($range_value['range_to']);
    	$html_exp  =  explode(' - ',$product_value[''.$html_id.'']);
        
        $totalsinward    +=  $html_exp[0];
        $totalsinstock   +=  $html_exp[1];
        //echo $html_exp[1];
    	?>
    <td class="centerAlign"><?php echo $html_exp[0]; ?></td>
    <td class="centerAlign"><?php echo $html_exp[1]; ?></td>
    	<?php
    }
    ?>
    <td class="centerAlign"><?php echo $totalsinward; ?></td>
    <td class="centerAlign"><?php echo $totalsinstock; ?></td>
</tr>    

<?php
}
}
?>
<tr>
    <td colspan="16" align="center">
        {!! $products->links() !!}
    </td>
</tr>
</tbody>

</table>
<input type="hidden" name="hidden_page" id="hidden_page" value="1" />
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value="sales_bill_id" />
<input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="DESC" />
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="datewise_product_agereport" />
<script type="text/javascript">
$(document).ready(function(e){


});
</script>