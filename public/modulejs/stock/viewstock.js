$("#searchcustomerdata").keyup(function ()
{
    
    jQuery.noConflict();
    if($("#searchcustomerdata").val().length >= 1) {

        $("#searchcustomerdata").autocomplete({
            autoFocus: true,
            minLength: 1,
            source: function (request, response) {
                var url = "customer_search";
                var type = "POST";
                var dataType = "";
                var data = {
                    'search_val': $("#searchcustomerdata").val()
                };
                callroute(url, type,dataType, data, function (data) {


                    var searchdata = JSON.parse(data, true);
                    var html = '';
                    if (searchdata['Success'] == "True") {

                        var result = [];
                        searchdata['Data'].forEach(function (value) {
                            result.push({
                                label: value.customer_name + '_' + value.customer_mobile,
                                value: value.customer_name + '_' + value.customer_mobile,
                                id: value.customer_id
                            });
                        });

                        //push data into result array.and this array used for display suggetion
                        response(result);

                    }
                });
            },
            //this help to call a function when select search suggetion
            select: function (event, ui) {
                var id = ui.item.id;
                //call a getproductdetail function for getting product detail based on selected product from suggetion
                
               
            }
        });
    }
    else
    {
            $("#searchcustomerdata").empty();
    }

});


$('#fromtodate').change(function(e){


   
    var inoutdate         =     $("#fromtodate").val();
    


    var totalnights       =     inoutdate.split(' - ');

    $("#from_date").val(totalnights[0]);
    $("#to_date").val(totalnights[1]); 

});


$("#productsearch").typeahead({

    source: function(request, process) {
     
        var  url = "sproduct_search";
        var type = "post";
        var dataType = "json";
        var data = {
            search_val: $("#productsearch").val(),
            term: request.term
        };
        callroute(url,type,dataType,data,function (data)
        {
            $("#productsearch").val()
                   
                 objects = [];
                 map = {};

                if($("#productsearch").val()!='')
                  {
                    
                     $.each(data, function(i, object)
                    {
                        map[object.label] = object;
                        objects.push(object.label);
                    });
                    process(objects);
                 
                    
                  }
                  else
                  {
                    $(".dropdown-menu").hide(); 
                  }
     });
    },
    
    minLength: 1,
    autoSelect:false
    
     
});

$("#batchno").typeahead({

  source: function(request, process) {
       var url = "batchno_search";
       var type = "post";
       var dataType = "json";
       var data = {
           search_val: $("#batchno").val(),
           term: request.term
       };

        callroute(url, type, dataType, data, function (data) {
           $("#batchno").val()
           objects = [];
           map = {};

           if ($("#batchno").val() != '') {
               $.each(data, function (i, object) {
                   map[object.label] = object;
                   objects.push(object.label);
               });
               
               process(objects);              

           } else {
               $(".dropdown-menu").hide();
           }
     });
    },
    minLength: 1,
    autoselect:false,
 
     
});


function resetbatchdata()
{
    $("#fromtodate").val('');
    $("#productsearch").val('');

    var data = {
        'from_date' : '',
        'to_date' : '',
        'barcode' : ''
    };
    var page = 1;
    var sort_type = $("#hidden_sort_type").val();
    var sort_by = $("#hidden_column_name").val();
    fetch_data('batch_no_wise_record',page,sort_type,sort_by,data,'batch_no_report_record');
}


$(document).on('click', '#exportbatchnodata', function(){

        var filter_date = $('#fromtodate').val();

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

        var query =
        {
            from_date: from_date,
            to_date : to_date,
            productsearch: $('#productsearch').val(),
            batchnosearch: $('#batchno').val(),
            //productsearch: $("#productsearch").val(),
        };
        var url = "export_batchno_details?" + $.param(query)
        window.open(url,'_blank');
    });

$(document).on('click', '#exportstockdata', function()
{
    var query = {};
    var dynamic_query = {};
    $(".common-search").find('input,select,hidden').each(function ()
    {

        if($(this).attr('name-attr') != undefined)
        {
            var name_attr = $(this).attr('name-attr');
            if(name_attr == "from_to_date")
            {
                query['from_date'] = '';
                query['to_date'] = '';
                var separate_date = $(this).val().split(' - ');

                if(separate_date[0] != undefined)
                {
                    query['from_date'] = separate_date[0];
                }

                if(separate_date[1] != undefined)
                {
                    query['to_date'] = separate_date[1];
                }
            }
            else
            {
                if(name_attr.indexOf('dynamic_') >  -1)
                {
                    dynamic_query[name_attr] = $(this).val();
                }
                else {
                    query[name_attr] = $(this).val();
                }
            }

        }
    });

    var querydata = {
        'query' : query,
        'dynamic_query' : dynamic_query
    };

    var url = "export_stockreport_details?" + $.param(querydata)
    window.open(url,'_blank');
});
