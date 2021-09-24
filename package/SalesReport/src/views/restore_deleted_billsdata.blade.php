<table id="view_deletedbill_recordtable" class="table tablesaw table-bordered table-hover table-striped mb-0" data-tablesaw-mode="swipe" data-tablesaw-sortable data-tablesaw-minimap data-tablesaw-mode-switch>


<thead>
<tr class="header">
    <th scope="col" data-tablesaw-priority="persist">Action</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Bill No.</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Bill Date</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Customer</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Total Qty</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="6">Selling Price</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7">Disc. Amount</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8">Taxable Amount</th>
    <?php
    if($tax_type==1)
    {
        ?>
           <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="11">{{$taxname}} Amount</th> 
    <?php
    }
    else
    {
        ?>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="9">CGST Amount</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="10">SGST Amount</th>
            <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="11">IGST Amount</th> 
        <?php
    }
    ?>
                                             
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="12">Total Amount</th>

</tr>
</thead>
<tbody>


<?php

 if(sizeof($sales) != 0) 
 {
    ?>
@foreach($sales AS $saleskey=>$sales_value)

<?php

 if ($saleskey % 2 == 0) {
    $tblclass = 'even';
} else {
    $tblclass = 'odd';
}
$sellingbeforediscount = $sales_value->sellingprice_after_discount + $sales_value->totaldiscount + $sales_value->totalcharges;

    if($sales_value['return_bill']!= null && $sales_value['return_bill']!= '')
    {
       
       $returnid  =   $sales_value['return_bill']['sales_bill_id'];
        
    }
    else
    {
        $returnid = '';
    }

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

<tr id="deletedbill_{{$sales_value['sales_bill_id']}}" class="<?php echo $tblclass ?>">
    <td><a id="restoredeletedbill_{{$sales_value->sales_bill_id}}" onclick="return restoredeletedbill(this);" style="text-decoration:none !important;" target="_blank" title="Restore Deleted Bill"><i class='fas fa-trash-restore-alt'></i></a>
        <i class="fa fa-eye" aria-hidden="true"  style='font-size:18px;cursor:pointer;' id="viewDeletedBill_{{$sales_value->sales_bill_id}}" onclick="return viewDeletedBill(this);" title="View Deleted Bill"></i></td>
    <td class="leftAlign">{{$sales_value->bill_no}}</td>
    <td class="leftAlign">{{date("d-m-Y",strtotime($sales_value->bill_date))}}</td>
    <td class="leftAlign">{{$customername = $sales_value['customer']['customer_name']}}</td>
    <td class="rightAlign">{{$sales_value->total_qty}}</td>
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
   

</tr>


@endforeach
<tr>
    <td colspan="12" align="center">
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
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value="sales_bill_id" />
<input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="DESC" />
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="pagewise_deletedbill_popup" />


