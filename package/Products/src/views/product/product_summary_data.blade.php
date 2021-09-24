<?php
?>
<table class="table tablesaw table-bordered table-hover table-striped mb-0"  cellpadding="6" border="0" frame="box" style="border:1px solid #C0C0C0 !important;">

    <thead>
    <tr class="blue_Head">
        <th>Sr No.</th>
        <th>Date / Module Name</th>
        <th>Bill / Invoice No.</th>
        <th>Details</th>
        <th>In / Pending Receive</th>
        <th>Out /Pending Out Receive</th>
        <th>In Stock</th>

    </tr>
    </thead>
    <tbody id="view_summary_record" style="" >

    @foreach($summary_arr AS $productkey=>$product_value)

        <?php if($productkey % 2 == 0)
        {
            $tblclass = 'even';
        }
        else
        {
            $tblclass = 'odd';
        }

        $value_detail = '';

        $invoice_no  = '';
        foreach ($product_value as $key => $value) {
            if (strpos($key, 'inv_') === 0)
            {
                $key = str_replace('inv_','',$key);
                $key = str_replace('_',' ',$key);
                $key = ucwords($key);

                if($invoice_no == '')
                {
                    $invoice_no = $key .'::'.$value.'<br>';
                }
                else{
                    $invoice_no = $invoice_no .$key .'::'.$value;
                }
            }
            else{
                if($key != 'module_name' && $key != 'date')
                {
                    $key = str_replace('_',' ',$key);
                    $value_detail .=  ucwords($key) .'::'.$value.'<br/>';
                }
            }
        }

        ?>

        <tr id="" class="<?php echo $tblclass ?>">
            <td><?php  echo $productkey + 1?></td>
            <td style="text-align:left;"><?php echo $product_value['date'].'/'.$product_value['module_name']?></td>
            <td style="text-align:left;"><?php echo $invoice_no ?></td>
            <td style="text-align:left;"><?php echo $value_detail ?></td>
            <td><?php echo isset($product_value['in_qty'])? $product_value['in_qty'] : ''  ?>
                <?php echo  isset($product_value['pending_receive_qty']) ? '/'.$product_value['pending_receive_qty'] :''?></td>
            <td><?php echo isset($product_value['out_qty']) ? $product_value['out_qty'] : '' ?>
                <?php echo isset($product_value['pending_out_qty']) ? '/'.$product_value['pending_out_qty'] : '' ?>
            </td>
            <td><?php echo isset($product_value['opening']) ? $product_value['opening'] : ''   ?></td>


        </tr>


    @endforeach

    </tbody>
</table>

<input type="hidden" name="hidden_page" id="hidden_page" value="1"/>
<input type="hidden" name="hidden_column_name" id="hidden_column_name" value=""/>
<input type="hidden" name="hidden_sort_type" id="hidden_sort_type" value="DESC"/>
<input type="hidden" name="fetch_data_url" id="fetch_data_url" value="product_summary_search"/>
<script src="{{URL::to('/')}}/public/dist/js/tablesaw-data.js"></script>

<script>
    $(".instock").html('<?php echo $instock  ?>');
    $("#product_id").val('<?php echo encrypt($product_detail['product_id']) ?>');
    <?php if(isset($product_detail['product_name'])) { ?>
    $("#product_name_summary").html('<?php echo $product_detail['product_name'].'_'.$product_detail['product_system_barcode'] ?>');
    $(".summary_title").html('<?php echo $product_detail['product_name'].'_'.$product_detail['product_system_barcode'] ?>');
    <?php } else { ?>
    $("#product_name_summary").html('Product Summary');
    $(".summary_title").html('Product Summary');
    <?php } ?>


</script>
