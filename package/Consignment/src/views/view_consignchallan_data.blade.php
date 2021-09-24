<table id="view_bill_recordtable" class="table tablesaw table-bordered table-hover table-striped mb-0"  data-tablesaw-sortable data-tablesaw-minimap data-tablesaw-mode-switch>


<thead>
<tr class="blue_Head">
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Action</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Status</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Consign. No.</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Consign. Date</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Customer</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Total Qty</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Pending Qty</th>
<?php
if($nav_type[0]['bill_calculation']==1)
{


?>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6">Selling Price</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">Disc. Amt.</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">Taxable Amt.</th>
    <?php
    if($tax_type==1)
    {
        ?>
           <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="11">{{$taxname}} Amt.</th> 
    <?php
    }
    else
    {
        ?>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="9">CGST Amt.</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="10">SGST Amt.</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="11">IGST Amt.</th> 
        <?php
    }
    ?>
                                             
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="12">Total Bill Amt.</th>

    @foreach($payment_methods AS $payment_methods_key=>$payment_methods_value)
        <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="13">{{$payment_methods_value->payment_method_name}}</th>
    @endforeach

<?php
}
?>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="12">Reference</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="12">Note for Internal USE</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="12">Note for Customer</th>
</tr>
</thead>
<tbody>
           


<?php

 if(sizeof($sales) != 0) 
 {
    
 
foreach($sales AS $saleskey=>$sales_value)
{
if ($saleskey % 2 == 0) {
    $tblclass = 'even';
} else {
    $tblclass = 'odd';
}

$totalconsignqty = 0;
$totalconsignreturnqty = 0;
foreach($sales_value['consign_products_detail'] as $skey=>$svalue)
{
    $totalconsignqty          +=   $svalue['totalconsignqty'];
    $totalconsignreturnqty    +=   $svalue['totalconsignreturnqty'];
}


 
$pendingqty            =  $sales_value['total_qty'] - $totalconsignqty - $totalconsignreturnqty;

$sellingbeforediscount = $sales_value->sellingprice_after_discount + $sales_value->totaldiscount + $sales_value->totalcharges;

   

    $totalsellingprice_after_discount  =   $sales_value->sellingprice_after_discount + $sales_value->totalcharges;

    $halfchargesgst   =   $sales_value->chargesgst / 2;

  if($tax_type==1)
  {
     $totaligstamount   =   $sales_value->total_igst_amount + $sales_value->chargesgst;
  }
  else
  {
        if($sales_value['state_id']==$company_state)
        {
                    $totalcgstamount   =   $sales_value->total_cgst_amount + $halfchargesgst;
                    $totalsgstamount   =   $sales_value->total_sgst_amount + $halfchargesgst;
                    $totaligstamount   =   0;
        }
        else
        {
                    $totalcgstamount   =   0;
                    $totalsgstamount   =   0;
                    $totaligstamount   =   $sales_value->total_igst_amount + $sales_value->chargesgst;
        }
   }

?>

<tr id="viewbill_{{$sales_value->consign_bill_id}}" class="<?php echo $tblclass ?>" ondblclick="return viewConsignment(this);">
    <td class="leftAlign"><i class="fa fa-eye" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;font-weight:bold;"  id="viewbill_{{$sales_value->consign_bill_id}}" onclick="return viewConsignment(this);" title="View Bill Details"></i>
    <?php
      
            if($role_permissions['permission_edit']==1)
            {
            ?>
                <a id="edit_bill" onclick="return edit_consignbill('{{encrypt($sales_value->consign_bill_id)}}');" title="Edit Bill"><i class="fa fa-edit" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;"></i></a>
            <?php
            }
            
            if($role_permissions['permission_print']==1)
            {
            ?>
                <a href="{{URL::to('printconsign_challan')}}?id={{encrypt($sales_value->consign_bill_id)}}" style="text-decoration:none !important;" target="_blank" title="A4/A5 Print"><i class="fa fa-print" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;"></i></a>
                <a href="{{URL::to('thermalconsign_challan')}}?id={{encrypt($sales_value->consign_bill_id)}}" style="text-decoration:none !important;" target="_blank" title="Thermal Print"><img src="{{URL::to('/')}}/public/images/thermalprint_icon" title="Thermal Print" width="25" class="" /></a>
            <?php
            }
            if($role_permissions['permission_delete']==1)
            {
            ?>
            <a id="deletebill_{{$sales_value->consign_bill_id}}" onclick="return deleteconsignbill(this);" style="text-decoration:none !important;" target="_blank" title="Delete"><i class="fa fa-trash" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;"></i></a>
            <?php
            }
       
    ?>
    </td>   
    <td class="leftAlign">
        <?php
        if($pendingqty == 0)
        {
            if($sales_value['total_qty'] == $totalconsignreturnqty)
            {
                ?>
                <a id="consign_bill_id" title="Convert to Bill" style="font-size:12px !important;color:#f00;font-weight:bold;">Returned</a>
                <?php
            }
            else
            {
            ?>
             <a id="consign_bill_id" title="Convert to Bill" style="font-size:12px !important;color:#f00;font-weight:bold;">Bill Created</a>
            <?php
            }
        }
        else
        {
            if($sales_value['total_qty'] == $totalconsignreturnqty)
            {
                ?>
                <a id="consign_bill_id" title="Convert to Bill" style="font-size:12px !important;color:#f00;font-weight:bold;">Returned</a>
                <?php
            }
            else
            {
         
            ?>
             <a id="consign_bill_id" onclick="return makebill('{{encrypt($sales_value->consign_bill_id)}}');" title="Convert to Bill" style="font-size:12px !important;color:#f00;font-weight:bold;cursor:pointer;">Convert to Bill</a>
            <?php
            }
        }
        ?>
       

    </td>
    <td class="leftAlign">{{$sales_value->bill_no}}</td>
    <td class="leftAlign">{{date("d-m-Y",strtotime($sales_value->bill_date))}}</td>
    <td class="leftAlign">{{$customername = $sales_value['customer']['customer_name']}}</td>
    <td class="rightAlign">{{$sales_value->total_qty}}</td>
    <td class="rightAlign">{{$pendingqty}}</td><?php
if($nav_type[0]['bill_calculation']==1)
{


?>


    <td class="rightAlign">{{number_format($sellingbeforediscount,2)}}</td>
    <td class="rightAlign">{{number_format($sales_value->totaldiscount,2)}}</td>
    <td class="rightAlign">{{number_format($totalsellingprice_after_discount,2)}}</td>
    <?php
    if($tax_type==1)
    {
        ?>

        <td class="rightAlign">{{number_format($totaligstamount,2)}}</td>
        <?php
    }
    else
    {
        ?>
         <td class="rightAlign">{{number_format($totalcgstamount,2)}}</td>
        <td class="rightAlign">{{number_format($totalsgstamount,2)}}</td>
        <td class="rightAlign">{{number_format($totaligstamount,2)}}</td>
        <?php
    }
    ?>
   
    <td class="rightAlign bold">{{number_format($sales_value->total_bill_amount,$nav_type[0]['decimal_points'])}}</td>
    @foreach($payment_methods AS $payment_methods_key=>$payment_methods_value)
        <td class="rightAlign" id="bill{{$sales_value->consign_bill_id}}{{$payment_methods_value->payment_method_id}}">0.00</td>
    @endforeach
<?php
}
?>    
    <td class="leftAlign">{{$sales_value['reference']['reference_name']}}</td>
    <td class="leftAlign">{{$sales_value->official_note}}</td>
    <td class="leftAlign">{{$sales_value->print_note}}</td>

</tr>


@foreach($sales_value->consign_payment_detail AS $salespayment_key=>$salespayment_value)
    <?php
    $billid = $sales_value->consign_bill_id . $salespayment_value->payment_method_id;
    ?>
    <script type="text/javascript">
        $('#bill<?php echo $billid?>').html(Number(<?php echo $salespayment_value->total_bill_amount ?>).toFixed({{$nav_type[0]['decimal_points']}}));
    </script>

@endforeach
<?php
}
?>
@foreach($returnbill AS $returnkey=>$return_value)

<?php

$rsellingbeforediscount = $return_value->sellingprice_after_discount + $return_value->totaldiscount + $return_value->totalcharges;


    $rtotalsellingprice_after_discount  =   $return_value->sellingprice_after_discount + $return_value->totalcharges;

    $rhalfchargesgst   =   $return_value->chargesgst / 2;

  if($tax_type==1)
  {
    $rtotaligstamount   =   $return_value->total_igst_amount + $return_value->chargesgst;
  }
  else
  {
        if($return_value['state_id']==$company_state)
        {
                    $rtotalcgstamount   =   $return_value->total_cgst_amount + $rhalfchargesgst;
                    $rtotalsgstamount   =   $return_value->total_sgst_amount + $rhalfchargesgst;
                    $rtotaligstamount   =   0;
        }
        else
        {
                    $rtotalcgstamount   =   0;
                    $rtotalsgstamount   =   0;
                    $rtotaligstamount   =   $return_value->total_igst_amount + $return_value->chargesgst;
        }
  }
    ?>
<tr style="background-color:#ffcfbe !important;">
    <td class=" leftAlign"><i class="fa fa-eye" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;font-weight:bold;"  id="viewreturnbill_{{$return_value->return_bill_id}}" onclick="return viewreturnBill(this);"></i>
     <a href="{{URL::to('print_creditnote')}}?id={{encrypt($return_value->return_bill_id)}}" style="text-decoration:none !important;" target="_blank" title="Print"><i class="fa fa-print" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;"></i></a>
    <a id="deletereturnbill_{{$return_value->return_bill_id}}" onclick="return deletereturnbill(this);" style="text-decoration:none !important;" target="_blank" title="Delete"><i class="fa fa-trash" aria-hidden="true" style="margin:0 2px !important;cursor:pointer;"></i></a></td>
    <td class="leftAlign"></td>
    <td class="leftAlign">{{$return_value['consign_bill']['bill_no']}}</td>
    <td class="leftAlign">{{$return_value['bill_date']}}</td>
    <td class="leftAlign">{{$customername = $return_value['customer']['customer_name']}}</td>
    <td class="rightAlign">{{$return_value->total_qty}}</td>
    <td class="rightAlign"></td>
<?php
if($nav_type[0]['bill_calculation']==1)
{


?>
    <td class="rightAlign">{{number_format($rsellingbeforediscount,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign">{{number_format($return_value->totaldiscount,$nav_type[0]['decimalpoints_forview'])}}</td>
    <td class="rightAlign">{{number_format($rtotalsellingprice_after_discount,$nav_type[0]['decimalpoints_forview'])}}</td>
    <?php
    if($tax_type==1)
    {
        ?>
         <td class=" rightAlign">{{number_format($rtotaligstamount,$nav_type[0]['decimalpoints_forview'])}}</td>
        <?php
    }
    else
    {
        ?>
         <td class=" rightAlign">{{number_format($rtotalcgstamount,$nav_type[0]['decimalpoints_forview'])}}</td>
        <td class=" rightAlign">{{number_format($rtotalsgstamount,$nav_type[0]['decimalpoints_forview'])}}</td>
        <td class=" rightAlign">{{number_format($rtotaligstamount,$nav_type[0]['decimalpoints_forview'])}}</td>
        <?php
    }
    ?>

    <td class=" rightAlign bold" >{{number_format($return_value->total_bill_amount,$nav_type[0]['decimal_points'])}}</td>
    @foreach($payment_methods AS $payment_methods_key=>$payment_methods_value)
        <td class=" rightAlign" id="rbill{{$return_value->return_bill_id}}{{$payment_methods_value->payment_method_id}}">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
    @endforeach

<?php
}
?>
    <td class="leftAlign ">{{$return_value['reference']['reference_name']}}</td>
    <td class="leftAlign ">{{$return_value->official_note}}</td>
    <td class="leftAlign ">{{$return_value->print_note}}</td>
    <td class="leftAlign">{{$return_value['user']['employee_firstname']}}</td>

</tr>
@foreach($return_value->return_bill_payment AS $returnpayment_key=>$returnrpayment_value)
    <?php
    $rbillid = $return_value->return_bill_id . $returnrpayment_value->payment_method_id;
    $creditamt =  number_format($returnrpayment_value->total_bill_amount,$nav_type[0]['decimalpoints_forview']);
    $creditno =  $returnrpayment_value['customer_creditnote']['creditnote_no'];
    $creditamtno   =  $creditamt.'<br>( '.$creditno.' )';
    ?>
    <script type="text/javascript">
        $('#rbill<?php echo $rbillid?>').html('<?php echo $creditamtno; ?>');
    </script>

@endforeach

@endforeach

<tr>
    <td colspan="22" align="center">
        {!! $sales->links() !!}
    </td>
</tr>

<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>

<?php
}

else
{
        ?>
            <tr>
            <td colspan="22" class="leftAlign">
            <b style="font-size:16px;">No Records Found!</b>
            </td>
            </tr>
        <?php
}


?>
</tbody>
</table>
<input type="hidden" name="hidden_page" id="hidden_page" value="1" />
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value="consign_bill_id" />
<input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="DESC" />
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="view_datewise_consigndata" />
<script type="text/javascript">
$(document).ready(function(e){
   

    $('.totalinvoice').html({{$count}});
    $('.taxabletariff').html({{round($todaytaxable,$nav_type[0]['decimal_points'])}});
    $('.overallcgst').html({{round($todaycgst,$nav_type[0]['decimal_points'])}});
    $('.overallsgst').html({{round($todaysgst,$nav_type[0]['decimal_points'])}});
    $('.overalligst').html({{round($todayigst,$nav_type[0]['decimal_points'])}});
    $('.overallgrand').html({{round($todaygrand,$nav_type[0]['decimal_points'])}});
    $('.cash').html({{round($cash,$nav_type[0]['decimal_points'])}});
    $('.showcard').html({{round($card,$nav_type[0]['decimal_points'])}});
    $('.cheque').html({{round($cheque,$nav_type[0]['decimal_points'])}});
    $('.wallet').html({{round($wallet,$nav_type[0]['decimal_points'])}});
    $('.unpaidamt').html({{round($unpaidamt,$nav_type[0]['decimal_points'])}});
    $('.netbanking').html({{round($netbanking,$nav_type[0]['decimal_points'])}});
    $('.creditnote').html({{round($creditnote,$nav_type[0]['decimal_points'])}});
   
    $('.fromdate').html("{{$max_date}}");
    $('.todate').html("{{$min_date}}");

    if($('.totalinvoice').html()==0  || $('.totalinvoice').html()=='')
    {
        $('.invoiceLabel').html('');
    }
    else if($('.totalinvoice').html()==1)
    {
       $('.invoiceLabel').html('Challan');
    }
    else
    {
        $('.invoiceLabel').html('Challans');
    }

   

});
</script>