<?php
$show_dynamic_feature = '';
?>

<table id="view_stock_recorddata" class="table tablesaw table-bordered table-hover table-striped mb-0"  data-tablesaw-sortable data-tablesaw-minimap data-tablesaw-mode-switch>
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
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">UQC</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Batch No.</th>
        <?php
        if($nav_type[0]['bill_calculation']==1)
        {
        ?>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Cost Rate</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">MRP</th>
        <?php
        }
        ?>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6">Opening</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">Inward[+]</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">Sold[-]</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">Franchise Qty(-)</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">Stock Transfer(-)</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">Consignment(-)</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="9">Return</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="10">Restock[+]</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="11">Damage[-]</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="12">Used[-]</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="13">Supp. Return[-]</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="14">InStock</th>
        <?php
        if($nav_type[0]['bill_calculation']==1)
        {
        ?>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="15">Total Cost Rate</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="16">Total MRP</th>
        <?php
        }
        ?>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="17">Expiry Date</th>
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="18">Expiry Days</th>
    </tr>
    </thead>
    <tbody>
    @foreach($product AS $key => $value)
        <?php

        if ($key % 2 == 0)
        {
            $tblclass = 'even';
        }
        else
        {
            $tblclass = 'odd';
        }
        if($value->product->supplier_barcode != '')
        {
            $barcode =  $value->product->supplier_barcode;
        }
        else
        {
            $barcode = $value->product->product_system_barcode;
        }
        $diff = '';
        if($value->expiry_date != null)
        {
            $now = strtotime(date('d-m-Y')); //CURRENT DATE
            $expiry_date = strtotime($value->expiry_date);
            $datediff = $expiry_date-$now;
            $diff =  round($datediff / (60 * 60 * 24));
        }
        $datediff = '';
        if($diff != '' && $diff >= 0)
            {
                $datediff = $diff;
            }
        $near_expiry_product = '';
        if($value->product->days_before_product_expiry != '')
        {
            if($diff != '' && $diff > 0)
                {
                    if($diff <= $value->product->days_before_product_expiry)
                    {
                        $near_expiry_product = "style=background-color:#ffaf93";
                    }
                }
        }

        $uqc_name = '';

        if($value['product']['uqc_id'] != '' && $value['product']['uqc_id'] != null && $value['product']['uqc_id'] != 0)
        {
            $uqc_name = $value['product']['uqc']['uqc_shortname'];
        }
        $opening   =   $value->totalinwardqty - $value->totalsoldqty + $value['totalrestock'] - $value['totalused'] - $value['totalddamage'] - $value['totalsuppreturn']-$value['totalconsign'] - $value['totalfranchiseqty']-$value['totalstransfer'];
            $stock     =   $opening +$value->currentinward -$value->currentsold + $value['currentrestock']-$value['currentused'] - $value['currentddamage']  - $value['currentsuppreturn'] - $value['currentconsign'] - $value['currentfranchiseqty'] - $value['currentstransfer'];
            $todayinward  = $value->currentinward != '' ?$value->currentinward : 0;
            $todaysold    = $value->currentsold != '' ?$value->currentsold : 0;
            $todayconsign = $value->currentconsign != '' ?$value->currentconsign : 0;
            $todayfranchiseqty = $value->currentfranchiseqty != '' ?$value->currentfranchiseqty : 0;
            $todaystransfer = $value->currentstransfer != '' ?$value->currentstransfer : 0;

            $totaldamage  =  $value['currentdamage'] +  $value['currentddamage'];

           if($value->averagemrp !='')
            {
                $averagemrp   = $value->averagemrp;
            }
            else
            {
                $averagemrp    =  $value->offer_price != '' ?$value->offer_price : 0;
            }

            $totalmrpvalue  =  $averagemrp * $stock;

            if($value->averagecost !='')
            {
                $averagecost   = $value->averagecost;
            }
            else
            {
                $averagecost    =  $value->cost_rate != '' ?$value->cost_rate : 0;
            }

            $totalcostvalue  =  $averagecost * $stock;

            $feature_show_val = "";
            if($show_dynamic_feature != '')
            {
                $feature = explode(',',$show_dynamic_feature);

                foreach($feature AS $fea_key=>$fea_val)
                {

                    $feature_show_val .= '<td>'.$value['product'][$fea_val].'</td>';
                }
            }

        ?>
        <tr {{$near_expiry_product}}>
            <td>
                <a href="{{URL::to('product_summary')}}?product_id={{encrypt($value->product->product_id)}}&batch_no={{encrypt($value->batch_no)}}" style="text-decoration:none !important;" target="_blank" title="Print">
                    <i class="fa fa-eye" title="View Product Summary"> </i></a>
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
            <td>{{$value->product->product_name}}</td>
            <?php
                echo $feature_show_val;
            ?>
            <td><?php echo $uqc_name?></td>
            <td>{{$value->batch_no}}</td>
            <?php
            if($nav_type[0]['bill_calculation']==1)
            {
             ?>
            <td>{{$value->averagecost}}</td>
            <td>{{$value->averagemrp}}</td>
            <?php
            }
            ?>
            <td class="rightAlign bold">{{$opening}}</td>
            <td class="rightAlign">{{$todayinward}}</td>
            <td class="rightAlign">{{$todaysold}}</td>
            <td class="rightAlign">{{$todayfranchiseqty}}</td>
            <td class="rightAlign">{{$todaystransfer}}</td>
            <td class="rightAlign">{{$todayconsign}}</td>
            <td class="rightAlign">{{$value['currentreturn']!=''?$value['currentreturn']:0}}</td>
            <td class="rightAlign">{{$value['currentrestock']!=''?$value['currentrestock']:0}}</td>
            <td class="rightAlign">{{$totaldamage}}</td>
            <td class="rightAlign">{{$value['currentused']!=''?$value['currentused']:0}}</td>
            <td class="rightAlign">{{$value['currentsuppreturn']!=''?$value['currentsuppreturn']:0}}</td>
            <td class="rightAlign bold">{{$stock}}</td>
            <?php
            if($nav_type[0]['bill_calculation']==1)
            {
             ?>
            <td class="rightAlign bold">{{$totalcostvalue}}</td>
            <td class="rightAlign bold">{{$totalmrpvalue}}</td>
            <?php
            }
            ?>
            <td class="rightAlign bold">{{$value->expiry_date}}</td>
            <td class="rightAlign bold">{{$datediff}}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="19" align="center" class="paginateui">
            {!! $product->links() !!}
        </td>
    </tr>
    </tbody>
</table>

<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>
<input type="hidden" name="hidden_page" id="hidden_page" value="1" />
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value="inward_product_detail_id" />
<input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="DESC" />
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="batch_no_wise_record" />
<script type="text/javascript">
    $(document).ready(function(e)
    {
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
        $('.totalconsign').html({{$currconsignqty}});
    });
    $(".PagecountResult").html('{{$product->total()}}');
    $(".PagecountResult").addClass("itemfocus");
</script>
