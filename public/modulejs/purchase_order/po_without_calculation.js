var total_amount = 0;

function addqty(obj)
{
    var tbl_row = $(obj).closest('tr').data('id');

    $("#po_product_detail_record").find("tr[data-id='" + tbl_row + "']").each(function ()
    {
        var product_id = $(obj).attr('id').split('qty_')[1];

        var qty = Number($(this).find('#qty_' + product_id).html());
        if (qty == '' || isNaN(qty))
        {
            qty = 0;
        }

        var costrate = total_amount;
        var gst_percent = total_amount;
        var gstamt = total_amount;
        $(this).find("#cost_gst_amount_" +product_id).html(gstamt.toFixed(4));
        var total_cost_without_gst = total_amount;
        $(this).find("#total_cost_without_gst_" + product_id).html(total_cost_without_gst.toFixed(4));
        var gst_amount = total_amount;
        var total_gst = total_amount;
        $(this).find("#total_gst_" + product_id).html(total_gst.toFixed(4));
        var total_cost_with_gst = total_amount;
        $(this).find("#total_cost_with_gst_" + product_id).html(total_cost_with_gst.toFixed(4));
        gettotalqty();
    });
}

function costrate(obj)
{
    var product_id = $(obj).attr('id').split('cost_rate_')[1];

    var cost_rate = $("#cost_rate_"+product_id).html();

    var cost_gst_percent = $("#cost_gst_percent_"+product_id).html();

    var cost_gst_amount = ((Number(cost_rate)) * (Number(cost_gst_percent)) /(Number(100)));

    $("#cost_gst_amount_"+product_id).html(cost_gst_amount.toFixed(4));

    var qty  = $("#qty_"+product_id).html();

    var total_cost_without_gst = ((Number(cost_rate)) * (Number(qty)));

    $("#total_cost_without_gst_"+product_id).html(total_cost_without_gst.toFixed(4));

    var gst_amount = $("#cost_gst_amount_"+product_id).html();

    var total_gst = ((Number(gst_amount)) * (Number(qty)));

    $("#total_gst_"+product_id).html(total_gst.toFixed(4));

    var total_cost_with_gst = (((Number(cost_rate)) + (Number(gst_amount)))*(Number(qty)));

    $("#total_cost_with_gst_"+product_id).html(total_cost_with_gst.toFixed(4));

    gettotalqty();
}

function gettotalqty()
{
    var total_qty = 0;

    $("#po_product_detail_record tr").each(function (index,e)
    {
        var product_id = $(this).attr('id').split('product_')[1];

        $(this).find('td').each(function ()
        {
            if($(this).attr('id') == "qty_"+product_id)
            {
                var totalqty  = $(this).html();
                if(totalqty == '')
                {
                    totalqty = 0;
                }
                total_qty += (Number(totalqty));
            }
        });
    });
    $("#total_qty").val(total_qty);
    $("#total_cost_rate").val(total_amount.toFixed(decimal_points));
    $("#total_gst").val(total_amount.toFixed(decimal_points));
    $("#total_cost_price").val(total_amount.toFixed(decimal_points));
}
