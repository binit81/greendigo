$('#searchCollapse').click(function(e){

    $('#searchBox').slideToggle();
});

function reset_stock_transfer_detail()
{
    $("#filer_from_to").val('');
    $("#stock_transfer_no_warehouse_filter").val('');

    resettable('stock_transfer_detail_fetch_data','stock_transfer_detail_table');
}
