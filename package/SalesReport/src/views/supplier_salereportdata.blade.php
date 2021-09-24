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
<table id="view_suppliersale_recordtable" class="table tablesaw table-bordered table-hover table-striped mb-0"  data-tablesaw-sortabledata-tablesaw-mode-switch>

<thead>
<tr class="blue_Head">
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Supplier</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Invoice No.</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="persist">Bill No.</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="2">Bill Date</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Product Name</th>
    
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Barcode</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">HSN</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="3">Size / UQC</th>
    <?php
    if($nav_type[0]['billtype']==3)
    {
        ?><th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="5">Batch No.</th><?php
    }
    ?>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="4">Qty</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="7" style="{{$billing_calculation_case}}">Taxable Amount</th>
    <?php
    if($nav_type[0]['tax_type']==1)
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
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="8" style="{{$billing_calculation_case}}">Cost Price</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="10" style="{{$billing_calculation_case}}">Profit/Loss</th>
    <th scope="col" data-tablesaw-sortable-col data-tablesaw-priority="11" style="{{$billing_calculation_case}}">Profit/Loss%</th>
</tr>
</thead>
<tbody>



<?php

if(sizeof($productdetails) != 0)
{

?>
@foreach($productdetails AS $saleskey=>$sales_value)
<?php

    $total_price = 0;

    if($sales_value['inwardids'] !='' || $sales_value['inwardids'] !=null)
    {

       $inwardids  = explode(',' ,substr($sales_value['inwardids'],0,-1));
       $inwardqtys = explode(',' ,substr($sales_value['inwardqtys'],0,-1));


        foreach($inwardids as $inidkey=>$inids)
        {
              $cost_price = Retailcore\Inward_Stock\Models\inward\inward_product_detail::select('cost_rate','supplier_gst_id','inward_stock_id')->find($inids);
              $invoice_no = Retailcore\Inward_Stock\Models\inward\inward_stock::select('invoice_no')->find($cost_price['inward_stock_id']);
              $supplier_id = Retailcore\Supplier\Models\supplier\supplier_gst::select('supplier_company_info_id')->find($cost_price['supplier_gst_id']);

              $supplier_name = Retailcore\Supplier\Models\supplier\supplier_company_info::select('supplier_company_name')->find($supplier_id['supplier_company_info_id']);

              $total_price      =    $cost_price['cost_rate'] * $inwardqtys[$inidkey];


              $taxable          =   ($sales_value->sellingprice_afteroverall_discount / $sales_value['qty']) * $inwardqtys[$inidkey];
              $totalable        =   ($sales_value->total_amount / $sales_value['qty']) * $inwardqtys[$inidkey];

              $averagecost      =   ($total_price / $inwardqtys[$inidkey]) * $inwardqtys[$inidkey];
              $profitamt        =   $taxable  - $averagecost;

              $profitper        =   ($profitamt * 100)/$averagecost;
              if($sales_value['product']['supplier_barcode']!='' || $sales_value['product']['supplier_barcode']!=NULL)
              {
                 $barcode = $sales_value['product']['supplier_barcode'];
              }
              else
              {
                $barcode = $sales_value['product']['product_system_barcode'];
              }

$uqc_name = '';
$size_name = '';
if($sales_value['product']['size_id'] != '' && $sales_value['product']['size_id'] != null && $sales_value['product']['size_id'] != 0)
{
    $size_name = $sales_value['product']['size']['size_name'];
}
$uqc_name = '';
if($sales_value['product']['uqc_id'] != '' && $sales_value['product']['uqc_id'] != null && $sales_value['product']['uqc_id'] != 0)
{
    $uqc_name = $sales_value['product']['uqc']['uqc_shortname'];
}
              ?>
              <tr id="">
                  <td class="leftAlign">{{$supplier_name['supplier_company_name']}}</td>
                  <td class="leftAlign">{{$invoice_no['invoice_no']}}</td>
                  <td class="leftAlign">{{$sales_value['sales_bill']['bill_no']}}</td>
                  <td class="leftAlign">{{$sales_value['sales_bill']['bill_date']}}</td>
                  <td class="leftAlign">{{$sales_value['product']['product_name']}}</td>
                  <td class="leftAlign">{{$barcode}}</td>
                  <td class="leftAlign">{{$sales_value['product']['hsn_sac_code']!=0?$sales_value['product']['hsn_sac_code']:''}}</td>
                  <td class="leftAlign"><?php echo $size_name .' '.$uqc_name?></td>
                   <?php
                  if($nav_type[0]['billtype']==3)
                  {
                      ?><td class="leftAlign">{{$sales_value['batchprice_master']['batch_no']}}</td><?php
                  }
                  ?>
                  <td class="rightAlign">{{$inwardqtys[$inidkey]}}</td>
                  <td class="rightAlign" style="{{$billing_calculation_case}}">{{number_format($taxable,$nav_type[0]['decimalpoints_forview'])}}</td>
                  <?php
                  if($nav_type[0]['tax_type']==1)
                  {
                      $igst_amount   =   ($sales_value->igst_amount / $sales_value['qty']) * $inwardqtys[$inidkey];
                          ?>
                           <td class="rightAlign" style="{{$billing_calculation_case}}">{{$sales_value->igst_percent}}</td>
                           <td class="rightAlign" style="{{$billing_calculation_case}}">{{number_format($igst_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
                          <?php
                  }
                  else
                  {
                          if($sales_value['sales_bill']['state_id'] == $company_state)
                          {
                            $cgst_amount   =   ($sales_value->cgst_amount / $sales_value['qty']) * $inwardqtys[$inidkey];
                            $sgst_amount   =   ($sales_value->sgst_amount / $sales_value['qty']) * $inwardqtys[$inidkey];
                              ?>
                                      <td class="rightAlign" style="{{$billing_calculation_case}}">{{$sales_value->cgst_percent}}</td>
                                      <td class="rightAlign" style="{{$billing_calculation_case}}">{{number_format($cgst_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
                                      <td class="rightAlign" style="{{$billing_calculation_case}}">{{$sales_value->sgst_percent}}</td>
                                      <td class="rightAlign" style="{{$billing_calculation_case}}">{{number_format($sgst_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
                                      <td class="rightAlign" style="{{$billing_calculation_case}}">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
                                      <td class="rightAlign" style="{{$billing_calculation_case}}">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
                              <?php
                          }
                          else
                          {
                             $igst_amount   =   ($sales_value->igst_amount / $sales_value['qty']) * $inwardqtys[$inidkey];
                              ?>
                                      <td class="rightAlign" style="{{$billing_calculation_case}}">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
                                      <td class="rightAlign" style="{{$billing_calculation_case}}">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
                                      <td class="rightAlign" style="{{$billing_calculation_case}}" style="{{$billing_calculation_case}}">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
                                      <td class="rightAlign" style="{{$billing_calculation_case}}">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
                                      <td class="rightAlign" style="{{$billing_calculation_case}}">{{$sales_value->igst_percent}}</td>
                                      <td class="rightAlign" style="{{$billing_calculation_case}}">{{number_format($igst_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
                              <?php
                          }
                  }

                  ?>


                  <td class="rightAlign bold" style="{{$billing_calculation_case}}">{{number_format($totalable,$nav_type[0]['decimal_points'])}}</td>
                  <td class="rightAlign" style="{{$billing_calculation_case}}">{{number_format($averagecost,$nav_type[0]['decimalpoints_forview'])}}</td>
                  <td class="rightAlign" style="{{$billing_calculation_case}}">{{number_format($profitamt,$nav_type[0]['decimalpoints_forview'])}}</td>
                  <td class="rightAlign" style="{{$billing_calculation_case}}">{{number_format($profitper,$nav_type[0]['decimalpoints_forview'])}}</td>
              </tr>
              <?php


        }

    }



?>






@endforeach
<?php
if(sizeof($rproductdetails) != 0)
{

?>
@foreach($rproductdetails AS $returnkey=>$return_value)
<?php
    $totalsellingprice   =   $return_value['qty'] * $return_value['sellingprice_before_discount'];
    $totaldiscount       =   $return_value['discount_amount']  + $return_value['overalldiscount_amount'];

     $inwardids  = explode(',' ,substr($return_value['inwardids'],0,-1));
     $inwardqtys = explode(',' ,substr($return_value['inwardqtys'],0,-1));
     $total_price = 0;
    if($return_value['inwardids'] !='' || $return_value['inwardids'] !=null)
    {

       foreach($inwardids as $inidkey=>$inids)
        {
              $cost_price = Retailcore\Inward_Stock\Models\inward\inward_product_detail::select('cost_rate','supplier_gst_id','inward_stock_id')->find($inids);
              $invoice_no = Retailcore\Inward_Stock\Models\inward\inward_stock::select('invoice_no')->find($cost_price['inward_stock_id']);
              $supplier_id = Retailcore\Supplier\Models\supplier\supplier_gst::select('supplier_company_info_id')->find($cost_price['supplier_gst_id']);

              $supplier_name = Retailcore\Supplier\Models\supplier\supplier_company_info::select('supplier_company_name')->find($supplier_id['supplier_company_info_id']);

              $total_price      =    $cost_price['cost_rate'] * $inwardqtys[$inidkey];


              $taxable          =   ($return_value->sellingprice_afteroverall_discount / $return_value['qty']) * $inwardqtys[$inidkey];
              $totalable        =   ($return_value->total_amount / $return_value['qty']) * $inwardqtys[$inidkey];

              $averagecost      =   ($total_price / $inwardqtys[$inidkey]) * $inwardqtys[$inidkey];
              $profitamt        =   $taxable  - $averagecost;

              $profitper        =   ($profitamt * 100)/$averagecost;
              if($return_value['product']['supplier_barcode']!='' || $return_value['product']['supplier_barcode']!=NULL)
              {
                 $barcode = $return_value['product']['supplier_barcode'];
              }
              else
              {
                $barcode = $return_value['product']['product_system_barcode'];
              }
              $uqc_name = '';
$size_name = '';
if($return_value['product']['size_id'] != '' && $return_value['product']['size_id'] != null && $return_value['product']['size_id'] != 0)
{
    $size_name = $return_value['product']['size']['size_name'];
}
$uqc_name = '';
if($return_value['product']['uqc_id'] != '' && $return_value['product']['uqc_id'] != null && $return_value['product']['uqc_id'] != 0)
{
    $uqc_name = $return_value['product']['uqc']['uqc_shortname'];
}
              ?>
              <tr id="" style="background:#ffcfbe !important;">
                  <td class="leftAlign ">{{$supplier_name['supplier_company_name']}}</td>
                  <td class="leftAlign ">{{$invoice_no['invoice_no']}}</td>
                  <td class="leftAlign ">{{$return_value['return_bill']['sales_bill']['bill_no']}}</td>
                  <td class="leftAlign ">{{$return_value['return_bill']['bill_date']}}</td>
                  <td class="leftAlign ">{{$return_value['product']['product_name']}}</td>
                  <td class="leftAlign ">{{$barcode}}</td>
                  <td class="leftAlign">{{$return_value['product']['hsn_sac_code']!=0?$return_value['product']['hsn_sac_code']:''}}</td>
                  <td class="leftAlign"><?php echo $size_name .' '.$uqc_name?></td>
                   <?php
                  if($nav_type[0]['billtype']==3)
                  {
                      ?><td class="leftAlign">{{$return_value['rbatchprice_master']['batch_no']}}</td><?php
                  }
                  ?>
                  <td class="rightAlign ">{{$inwardqtys[$inidkey]}}</td>
                  <td class="rightAlign " style="{{$billing_calculation_case}}">{{number_format($taxable,$nav_type[0]['decimalpoints_forview'])}}</td>
                  <?php
                  if($nav_type[0]['tax_type']==1)
                  {
                      $igst_amount   =   ($return_value->igst_amount / $return_value['qty']) * $inwardqtys[$inidkey];
                          ?>
                           <td class="rightAlign " style="{{$billing_calculation_case}}">{{$return_value->igst_percent}}</td>
                           <td class="rightAlign " style="{{$billing_calculation_case}}">{{number_format($igst_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
                          <?php
                  }
                  else
                  {
                          if($return_value['return_bill']['state_id'] == $company_state)
                          {
                            $cgst_amount   =   ($return_value->cgst_amount / $return_value['qty']) * $inwardqtys[$inidkey];
                            $sgst_amount   =   ($return_value->sgst_amount / $return_value['qty']) * $inwardqtys[$inidkey];
                              ?>
                                      <td class="rightAlign " style="{{$billing_calculation_case}}">{{$return_value->cgst_percent}}</td>
                                      <td class="rightAlign " style="{{$billing_calculation_case}}">{{number_format($cgst_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
                                      <td class="rightAlign " style="{{$billing_calculation_case}}">{{$return_value->sgst_percent}}</td>
                                      <td class="rightAlign " style="{{$billing_calculation_case}}">{{number_format($sgst_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
                                      <td class="rightAlign " style="{{$billing_calculation_case}}">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
                                      <td class="rightAlign " style="{{$billing_calculation_case}}">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
                              <?php
                          }
                          else
                          {
                             $igst_amount   =   ($return_value->igst_amount / $return_value['qty']) * $inwardqtys[$inidkey];
                              ?>
                                      <td class="rightAlign " style="{{$billing_calculation_case}}">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
                                      <td class="rightAlign " style="{{$billing_calculation_case}}">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
                                      <td class="rightAlign " style="{{$billing_calculation_case}}">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
                                      <td class="rightAlign " style="{{$billing_calculation_case}}">{{number_format(0,$nav_type[0]['decimalpoints_forview'])}}</td>
                                      <td class="rightAlign " style="{{$billing_calculation_case}}">{{$return_value->igst_percent}}</td>
                                      <td class="rightAlign " style="{{$billing_calculation_case}}">{{number_format($igst_amount,$nav_type[0]['decimalpoints_forview'])}}</td>
                              <?php
                          }
                  }
                  
                  ?>

                 
                  <td class="rightAlign bold  " style="{{$billing_calculation_case}}">{{number_format($totalable,$nav_type[0]['decimal_points'])}}</td>
                  <td class="rightAlign " style="{{$billing_calculation_case}}" style="{{$billing_calculation_case}}">{{number_format($averagecost,$nav_type[0]['decimalpoints_forview'])}}</td>
                  <td class="rightAlign " style="{{$billing_calculation_case}}">{{number_format($profitamt,$nav_type[0]['decimalpoints_forview'])}}</td>
                  <td class="rightAlign " style="{{$billing_calculation_case}}">{{number_format($profitper,$nav_type[0]['decimalpoints_forview'])}}</td>
              </tr>
              <?php


        }
      }
    ?>



@endforeach

<?php
}
?>

<tr>

 <td colspan="18" align="center">
       {!! $productdetails->links() !!}
    </td>
</tr>



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
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="datewise_suppliersale_detail" />
<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>