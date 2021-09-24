

$('#transfer_date').datepicker({
    autoclose: true,
    format: "dd-mm-yyyy",
    immediateUpdates: true,
    todayBtn: true,
    orientation: "bottom",
    todayHighlight: true
}).datepicker("setDate", "0").on('keypress paste', function (e)
{
    e.preventDefault();
    return false;
});
(function ($) {
    $.fn.focusTextToEnd = function () {
        var $thisVal = this.val();
        this.val('').val($thisVal);
        this.focus();
        return this;
    }
}(jQuery));



$("#sinvoice_no_search").typeahead({
source: function(request, process)
    {
        var  url = "searchproduct";
        var type = "post";
        var dataType = "json";
        var data = {
            search_val: $("#stockproductsearch").val(),
            term: request.term
        };
        callroute(url,type,dataType,data,function (data)
        {
          // console.log(data);return false;

            objects = [];
            map = {};

            if($("#stockproductsearch").val()!='')
            {
                $.each(data['Data'], function(i, object)
                {
                    map[object.label] = object;
                    objects.push(object.label);
                });
                process(objects);
                if(objects!='')
                {
                  // return false;
                    if(objects.length === 1) {
                      $(".dropdown-menu .active").trigger("click");
                      $("#stockproductsearch").val('');
                    }
                }
            }
        });
    },
    minLength: 1,
    autoselect:true,
      afterSelect: function (item) {
        var value = item;
        var price_master_id = map[item]['price_master_id'];
        getproductdetail(price_master_id);
    }
});







