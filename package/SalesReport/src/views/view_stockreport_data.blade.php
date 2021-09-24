<?php
$show_dynamic_feature = '';
?>
<table  id="view_stock_recorddata" class="table tablesaw table-bordered table-hover table-striped mb-0"  data-tablesaw-sortable data-tablesaw-minimap data-tablesaw-mode-switch>

<thead>
<tr class="blue_Head">
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Action</th>
    <?php
     if(sizeof($get_store)!=0)
     {
     ?>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Location</th>
    <?php
    }
    ?>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Barcode</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Product Name</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">HSN</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">PCode</th>
    <?php
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
    ?>

    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist"><?php echo $feature_value['product_features_name']?></th>
    <?php } ?>
    <?php

    }
    }
    }
    ?>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">UQC</th>
<?php
if($nav_type[0]['bill_calculation']==1)
{

?>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">MRP</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Cost Price</th>
<?php
}
?>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Opening</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6">Inward(+)</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">Sold(-)</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">Franchise Qty(-)</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">Stock Transfer(-)</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">Return</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="9">Restock(+)</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="10">Damage(-)</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="11">Used/Other(-)</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="12">Supp. Return(-)</th>    
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">Pending Consignment(-)</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="13">InStock</th>
<?php
if($nav_type[0]['bill_calculation']==1)
{

?>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="14">Total MRP Value</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="14">Total Cost Value</th>
<?php
}
?>


</tr>
</thead>
<tbody>


@foreach($product AS $productkey=>$product_value)
<?php
if ($productkey % 2 == 0)
{
    $tblclass = 'even';
}
else
{
    $tblclass = 'odd';
}
    $sr   = $productkey + 1;

    if(isset($product_value->category_id) && $product_value->category_id!=null || $product_value->category_id!='')
    {
        $category_name   =   $product_value->category->category_name;
    }
    else
    {
        $category_name  = '- - - -';
    }
    if(isset($product_value->brand_id) && $product_value->brand_id!=null || $product_value->brand_id!='')
    {
        $brand_name   =   $product_value->brand->brand_type;
    }
    else
    {
        $brand_name  = '- - - -';
    }

    if(isset($product_value->sku_code) && $product_value->sku_code!=null || $product_value->sku_code!='')
    {
        $sku_code   =   $product_value->sku_code;
    }
    else
    {
        $sku_code  = '- - - -';
    }


    
    $opening   =   $product_value->totalinwardqty - $product_value->totalsoldqty + $product_value['totalrestock'] - $product_value['totalused'] - $product_value['totalddamage'] - $product_value['totalsuppreturn'] - $product_value['totalfranchiseqty']-$product_value['totalstransfer'] - $product_value['totalconsignsold'];

    

    $todayconsign = $product_value->currentconsign != '' ?$product_value->currentconsign : 0;
    $ppendingconsignqty  =  $todayconsign - $product_value['currentconsignsold'];

    $stock     =   $opening +$product_value->currentinward -$product_value->currentsold + $product_value['currentrestock']-$product_value['currentused'] - $product_value['currentddamage']  - $product_value['currentsuppreturn'] - $product_value['currentfranchiseqty'] - $product_value['currentstransfer'] - $ppendingconsignqty;
    $todayinward  = $product_value->currentinward != '' ?$product_value->currentinward : 0;
    $todaysold    = $product_value->currentsold != '' ?$product_value->currentsold : 0;
    
    $todayfranchiseqty = $product_value->currentfranchiseqty != '' ?$product_value->currentfranchiseqty : 0;
    $todaystransfer = $product_value->currentstransfer != '' ?$product_value->currentstransfer : 0;
    $totaldamage  =  $product_value['currentdamage'] +  $product_value['currentddamage'];

    if($product_value->averagemrp !='')
    {
        $averagemrp   = $product_value->averagemrp;
    }
    else
    {
        $averagemrp    =  $product_value->offer_price != '' ?$product_value->offer_price : 0;
    }

    $totalmrpvalue  =  $averagemrp * $stock;

    if($product_value->supplier_barcode!='' && $product_value->supplier_barcode!=NULL)
    {
        $barcode  =   $product_value->supplier_barcode;

    }
    else
    {
         $barcode  =   $product_value->product_system_barcode;
    }

$uqc_name = '';

if($product_value['uqc_id'] != '' && $product_value['uqc_id'] != null && $product_value['uqc_id'] != 0)
{
    $uqc_name = $product_value['uqc']['uqc_shortname'];
}


$feature_show_val = "";
if($show_dynamic_feature != '')
{
    $feature = explode(',',$show_dynamic_feature);

    foreach($feature AS $fea_key=>$fea_val)
    {
        $feature_show_val .= '<td>'.$product_value[$fea_val].'</td>';
    }
}


 ?>
 <tr>
     <td>
         <a href="{{URL::to('product_summary')}}?product_id={{encrypt($product_value->product_id)}}" style="text-decoration:none !important;" target="_blank" title="Print">
              <i class="fa fa-eye" title="View Product Summery"> </i></a>
         </a>
     </td>
    <?php
     if(sizeof($get_store)!=0)
     {
     ?>
     <td>{{$companyname}}</td>
     <?php
     }
     ?>

    <td>{{$barcode}}</td>
    <td>{{$product_value->product_name}}</td>
    <td>{{$product_value->hsn_sac_code!=0?$product_value->hsn_sac_code:''}}</td>
    <td>{{$product_value->product_code}}</td>
     <?php
        echo $feature_show_val;
     ?>
    <td><?php echo $uqc_name?></td>
<?php
if($nav_type[0]['bill_calculation']==1)
{
    ?>
    <td>{{number_format($averagemrp,$nav_type[0]['decimal_points'])}}</td>
    <td>{{number_format($product_value['averageproductcost'],$nav_type[0]['decimalpoints_forview'])}}</td>
<?php
}
?>

    <td class="rightAlign bold">{{$opening}}</td>
    <td class="rightAlign">{{$todayinward}}</td>
    <td class="rightAlign">{{$todaysold}}</td>
    <td class="rightAlign">{{$todayfranchiseqty}}</td>
    <td class="rightAlign">{{$todaystransfer}}</td>
    <td class="rightAlign">{{$product_value['currentreturn']!=''?$product_value['currentreturn']:0}}</td>
    <td class="rightAlign">{{$product_value['currentrestock']!=''?$product_value['currentrestock']:0}}</td>
    <td class="rightAlign">{{$totaldamage}}</td>
    <td class="rightAlign">{{$product_value['currentused']!=''?$product_value['currentused']:0}}</td>
    <td class="rightAlign">{{$product_value['currentsuppreturn']!=''?$product_value['currentsuppreturn']:0}}</td>
    <td class="rightAlign">{{$ppendingconsignqty!=''?$ppendingconsignqty:0}}</td>
    <td class="rightAlign bold">{{$stock}}</td>
<?php
if($nav_type[0]['bill_calculation']==1)
{
    ?>
    <td class="rightAlign bold">{{number_format($totalmrpvalue,$nav_type[0]['decimal_points'])}}</td>
    <td class="rightAlign bold">{{number_format($product_value['totalaverageproductcost'],$nav_type[0]['decimalpoints_forview'])}}</td>
<?php
}
?>

</tr>
@endforeach
<tr>
 <td colspan="16" align="center">
       {!! $product->links() !!}
    </td>
</tr>

</tbody>
</table>


<script type="text/javascript">
 //console.log({{$product->sum('totalaverageproductcost')}});
$(document).ready(function(e){


    $('.totalproducts').html({{$count}});
    $('.opening').html({{$totopening}});
    $('.totalinwardqty').html({{$currinward}});
    $('.totalsoldqty').html({{$currsold}});
    $('.totalinstock').html({{$totstock}});
    $('.totalrestockqty').html({{$currrestock}});
    $('.totaldamageqty').html({{$ttotaldamage}});
    $('.totalusedqty').html({{$currusedqty}});
    $('.totalsupprqty').html({{$currsupprqty}});
    $('.totalfranqty').html({{$currfranqty}});
    $('.totaltransfer').html({{$currstransfer}});
    $('.totalconsign').html({{$pendingcurrconsignqty}});
   
});
</script>

<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>
<input type="hidden" name="hidden_page" id="hidden_page" value="1" />
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value="product_id" />
<input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="DESC" />
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="datewise_stock_detail" />
<script type="text/javascript">
    $(".PagecountResult").html('{{$product->total()}}');
    $(".PagecountResult").addClass("itemfocus");
</script>