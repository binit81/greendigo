function edit_storerreturnbill(store_return_id)
{


    var  url = "edit_storerreturnbill";
    var type = "POST";
    var dataType = "";
    var data = {
        'store_return_id' : store_return_id,
    };

    callroute(url,type,dataType,data,function (data) {
        var dta = JSON.parse(data);
        if (dta['Success'] == "True")
        {
            var url = '';
            if(dta['url'] != '' && dta['url'] != 'undefined')
            {
                 url = dta['url'];
            }
           localStorage.setItem('edit_storereturn_record',JSON.stringify(dta['Data']));

            window.location.href = url;



        }
        
    });
}

function viewreturndetail(obj){



    var id                        =     $(obj).attr('id');
    var store_return_id           =     $(obj).attr('id').split('viewbill_')[1]; 
    $("#view_bill_type").val(1);
    var data = {'store_return_id':store_return_id};
    var url = 'view_storereturn_popup';
    var type = "POST";
    var dataType =  "";

    callroute(url, type, dataType, data,function (data) 
    {
        $('.popup_values').html('');
        $('.popup_values').html(data);
        $("#addcustomerpopup").modal('show');
    });
}
$('#previousrecord').click(function(e){

   
   

         var store_return_id          =     $('#fetchedbillno').val();   
         var maxid                    =     $('#maxid').val();   
         var minid                    =     $('#minid').val();
            if(Number(store_return_id) == Number(minid))
            {
                    $('#previousrecord').prop('disabled', true);
                    return false;
            }
            else
            {
                $('#nextrecord').prop('disabled', false);
                $('#previousrecord').prop('disabled', false);
                // $('.editdeleteIcons').html('');


             
                var data = {'store_return_id':store_return_id};
                var url = 'previous_storereturn';
                var type = "POST";
                var dataType = "";

                callroute(url, type, dataType, data,function (data) 
                {
                    $('.popup_values').html('');
                    $('.popup_values').html(data);
                });

               
            }
    
    

});

$('#nextrecord').click(function(e){


            var store_return_id          =     $('#fetchedbillno').val();   
            var maxid                    =     $('#maxid').val();   
            var minid                    =     $('#minid').val();   

            if(Number(store_return_id) == Number(maxid))
            {
                     $('#nextrecord').prop('disabled', true);
                    return false;
            }
            else
            {
                $('#nextrecord').prop('disabled', false);
                 $('#previousrecord').prop('disabled', false);
                 // $('.editdeleteIcons').html('');


                var data = {'store_return_id':store_return_id};
                var url = 'next_storereturn';
                var type = "POST";
                var dataType = "";

                callroute(url, type, dataType, data,function (data) 
                {
                    $('.popup_values').html('');
                    $('.popup_values').html(data);
                });
            }
    

});

function storerestockqty(obj)
{
    var id                        =     $(obj).attr('id');
    var returnid                  =     $(obj).attr('id').split('restock_')[1];
    var restock                   =     $('#restock_'+returnid).val();
    var damage                    =     $('#damage_'+returnid).val();
    var totalreturn               =     $('#returnqty_'+returnid).html();

    
    if(Number(damage)=='')
    {
      damage = 0;
    }
    else
    {
      damage   = damage;
    }
    var typereturn  =    Number(restock)  +   Number(damage);


    if(Number(typereturn) > Number(totalreturn))
    {
        var restockqty    =    Number(totalreturn) - Number(damage);
        $('#damage_'+returnid).val(damage);
        $('#restock_'+returnid).val(restockqty);
    }
    else
    {
         $('#damage_'+returnid).val(damage);
    }
    

}
function storedamageqty(obj)
{

    var id                        =     $(obj).attr('id');
    var returnid                  =     $(obj).attr('id').split('damage_')[1];
    var restock                   =     $('#restock_'+returnid).val();
    var damage                    =     $('#damage_'+returnid).val();
    var totalreturn               =     $('#returnqty_'+returnid).html();

    if(Number(restock)=='')
    {
      restock = 0;
    }
    else
    {
      restock   = restock;
    }
    var typereturn  =    Number(restock)  +   Number(damage);


    if(Number(typereturn) > Number(totalreturn))
    {
        var damageqty    =    Number(totalreturn) - Number(restock);
        $('#damage_'+returnid).val(damageqty);
        $('#restock_'+returnid).val(restock);
    }
    else
    {
         $('#restock_'+returnid).val(restock);
    }

   

}

function storesavereturn(obj)
{
 
    var id                        =     $(obj).attr('id');
    var returnid                  =     $(obj).attr('id').split('add_storereturnproducts_')[1];
    var arrayValue                =     [];
    var return_values             =     {};

    return_values['returnbill_product_id']    =   '';
    return_values['storereturn_product_id']   =   returnid;
    return_values['returnqty']                =   $('#returnqty_'+returnid).html();
    return_values['restockqty']               =   $('#restock_'+returnid).val();
    return_values['damageqty']                =   $('#damage_'+returnid).val();
    return_values['price_master_id']          =   $('#pricemasterid_'+returnid).val();
    return_values['product_id']               =   $('#productid_'+returnid).val();
    return_values['inwardids']                =   $('#inwardids_'+returnid).val();
    return_values['remarks']                  =   $('#remarks_'+returnid).val();
    return_values['storecompanyid']           =   $('#storecompanyid_'+returnid).val();
   

    if(return_values['restockqty']=='' && return_values['restockqty']=='')
    {
        toastr.error('Please Enter Qty to ReStock and Damage');
        $('#restock_'+returnid).focus();
        return false;
    }
    else if(return_values['damageqty']>0 && return_values['remarks']=='')
    {  
            toastr.error('Please mention Remarks for Damage Product');
            $('#remarks_'+returnid).focus();
            return false;
       
    }
    else
    {
        if(confirm("Are You Sure to Restock or add products to Damage List ?")) {

                arrayValue.push(return_values);

                var data = arrayValue;

                //console.log(data);
                //return false;

                var  url = "storerestock_products";
                var type = "POST";
                var dataType = "";
                callroute(url,type,dataType,data,function (data)
                {
                     var dta = JSON.parse(data);

                        if (dta['Success'] == "True")
                        {
                            toastr.success(dta['Message']);
                            //$("#viewbillform").trigger('reset');
                            var page = $('#hidden_page').val();
                            $('#hidden_page').val(page);
                            var column_name = $('#hidden_column_name').val();
                            var sort_type = $('#hidden_sort_type').val();

                            var query = {};

                            var fetch_data_url = $('#fetch_data_url').val();
                            var tbodyid = $('html').find('.table-responsive').attr('id');
                            fetch_data(fetch_data_url,page, sort_type, column_name, query,tbodyid);

                            //resettable('viewreturn_data','returnproductrecord');

                        } else {
                            toastr.error(dta['Message']);
                        }
                    
                });
            }
            else
            {
                 return false;
            }

        
    }

}

