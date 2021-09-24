function reset_debit_note_filterdata(){
    $("#filer_from_to").val('');
    $("#debit_no_filter").val('');
    $("#pcode_filter").val('');
    var data = {};
    var page = 1;
    var sort_type = $("#hidden_sort_type").val();
    var sort_by = $("#hidden_column_name").val();
    fetch_data('debit_no_wise_search_record',page,sort_type,sort_by,data,'debit_note_record');
}

$(document).on('click', '#debit_note_report_export', function(){

    var filter_date = $('#filer_from_to').val();

    var from_date = '';
    var to_date = '';

    var separate_date = filter_date.split(' - ');
    if(separate_date[0] != undefined)
    {
        from_date = separate_date[0];
    }

    if(separate_date[1] != undefined)
    {
        to_date = separate_date[1];
    }
    var query = {
        from_date: from_date,
        to_date : to_date,
        debit_no : $("#debit_no_filter").val(),
        product_code : $("#pcode_filter").val()
    };
    var url = "debitnote_report_export?" + $.param(query)
    window.open(url,'_blank');


});

$("#debit_no_filter").keyup(function ()
{
    jQuery.noConflict();

    $(this).autocomplete({
        autoFocus: true,
        minLength: 1,
        source: function (request, response)
        {
            var url = "debit_number_search";
            var type = "POST";
            var dataType = "";
            var data = {
                'search_val' : $("#debit_no_filter").val()
            };
            callroute(url,type,dataType,data,function (data)
            {
                var searchdata = JSON.parse(data,true);

                if(searchdata['Success'] == "True")
                {
                    var result = [];
                    searchdata['Data'].forEach(function (value)
                    {
                        result.push({
                            label: value.debit_no,
                            value: value.debit_no
                        });
                    });
                    response(result);
                }
            });
        },
        //this help to call a function when select search suggetion
        select: function(event,ui)
        {
            $(".ui-helper-hidden-accessible").css('display','none');
        }
    });
});

