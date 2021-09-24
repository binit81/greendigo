@include('pagetitle')
@extends('master')

@section('main-hk-pg-wrapper')

<?php
if($role_permissions['userType']==0)
{
    ?><script>window.location='my_profile'</script><?php
}
    $url   =   URL::to('/').'/'.DASHBOARD_SORT_URL.DASHBOARD_SORT_FILE;

    $myfile = @fopen($url, "r");
     $sortDivs   =   @fgets($myfile);
    $sortDiv    =   explode(',',$sortDivs,-1);


    $array_srt_0  =   array_keys($sortDiv,0);
    $array_srt_1  =   array_keys($sortDiv,1);
    $array_srt_2  =   array_keys($sortDiv,2);
    $array_srt_3  =   array_keys($sortDiv,3);
    $array_srt_4  =   array_keys($sortDiv,4);
    $array_srt_5  =   array_keys($sortDiv,5);
    $array_srt_6  =   array_keys($sortDiv,6);

    // print_r($array_srt_2[0]);
?>

<div class="container ml-20">
    

    <div id="sortable1" class="row ma-0 mb-30 pr-30 pa-0 connectedSortable">
            
            <div id="0" data-id="{{$array_srt_0['0']}}" class="col-md-12 pr-10 mb-30 ui-state-default ord">
                <div class="row pa-0 ma-0">
                    
                    <div class="col-md-5 pa-0 mb-10 bold centerAlign">Today's</div>
                    <div class="col-md-2 pa-0 bold">&nbsp;</div>
                    <div class="col-md-5 pa-0 bold centerAlign">This Month</div>
               
                    <div class="card card-sm pa-0">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="card-body pa-0 pt-10 ml-20">
                                    <div class="d-flex">
                                        <div class="col-md-4">
                                            <h4 class="borderRight centerAlign">
                                                <b class="greencolor">{{number_format($finalTodaySales,2)}}</b><br>
                                                <small class="bold centerAlign font-14">SALE ({!!html_entity_decode($nav_type[0]['currency_title'])!!})</small>
                                            </h4>

                                        </div>
                                        <div class="col-md-4">
                                            <h4 class="borderRight centerAlign">
                                                <b class="greencolor">{{number_format($todayprofit,2)}}</b><br>
                                                <small class="bold centerAlign font-14">NET PROFIT ({!!html_entity_decode($nav_type[0]['currency_title'])!!})</small>
                                            </h4>
                                        </div>
                                        <div class="col-md-4">
                                            <h4 class="centerAlign">
                                                <b class="redcolor">{{number_format($todayReturn,2)}}</b><br>
                                                <small class="bold centerAlign font-14">SALE RETURN ({!!html_entity_decode($nav_type[0]['currency_title'])!!})</small>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2 centerAlign pt-15">&nbsp;</div> <!-- graph comes here-->

                            <div class="col-md-5">
                                <div class="card-body pa-0 pt-10 ml-20">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="col-md-4">
                                            <h4 class="borderRight centerAlign">
                                                <b class="greencolor">{{number_format($finalMonthSales,2)}}</b><br>
                                                <small class="bold centerAlign font-14">SALE ({!!html_entity_decode($nav_type[0]['currency_title'])!!})</small>
                                            </h4>
                                        </div>
                                        <div class="col-md-4">
                                            <h4 class="borderRight centerAlign">
                                                <b class="greencolor">{{number_format($todayprofit_month,2)}}</b><br>
                                                <small class="bold centerAlign font-14">NET PROFIT ({!!html_entity_decode($nav_type[0]['currency_title'])!!})</small>
                                            </h4>
                                        </div>
                                        <div class="col-md-4">
                                            <h4 class="centerAlign">
                                                <b class="redcolor">{{number_format($monthReturn,2)}}</b><br>
                                                <small class="bold centerAlign font-14">SALE RETURN ({!!html_entity_decode($nav_type[0]['currency_title'])!!})</small>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-5 centerAlign">
                        <div class="todaysCollection pt-5 ml-10 row" style="display:none;">
                            @foreach($payments_day as $key_day=>$value_day)
                            <div class="col-md-4 mb-5 leftAlign">
                                <small>
                                    <span>&nbsp;&nbsp;&nbsp;{{$value_day['mode']}}</span>
                                    <span class="bold modeVal">{!!html_entity_decode($nav_type[0]['currency_title'])!!} {{$value_day['amounts']}}</span>
                                </small>
                            </div>
                            @endforeach
                            <input type="hidden" id="todaysCollection" value="0" />
                        </div>
                        <center>
                            <div class="viewButton col-md-6 cursor todaysCollectionVal" onclick="todaysCollectionBtn()">View Payment Mode Collections</div>
                        </center>
                    </div>

                    <div class="col-md-2">
                        <!-- <center>
                            <div class="viewButton col-md-6 cursor">Sale Summary</div>
                        </center> -->
                    </div>

                    <div class="col-md-5 centerAlign ">
                        <div class="monthlyCollection pt-5 mr-10 row" style="display:none;">
                            @foreach($payments as $key=>$value)
                            <div class="col-md-4 mb-5 leftAlign">
                                <small>
                                    <span>&nbsp;&nbsp;&nbsp;{{$value['mode']}}</span>
                                    <span class="bold modeVal">{!!html_entity_decode($nav_type[0]['currency_title'])!!} {{$value['amounts']}}</span>
                                </small>
                            </div>
                            @endforeach
                            <input type="hidden" id="monthlyCollection" value="0" />
                        </div>
                        <center>
                            <div class="viewButton col-md-6 cursor monthlyCollectionVal" onclick="monthlyCollectionBtn()">View Payment Mode Collections</div>
                        </center>
                    </div>

                </div>
            </div>


            <!-- LATEST BILLS BLOCK -->
            <div id="1" data-id="{{$array_srt_1['0']}}" class="ui-state-default col-md-4 ord">
                <small class="bold">LATEST BILLS (showing <span class="countbills"></span> out of {{$salescount}} records)</small>
                <div class="card card-sm pa-0 mt-10">
                    <div class="table-responsive dashboardTbl">
                        <table class="table table-primary table-bordered mb-0 sorttable">
                            <thead class="thead-primary_default">
                            <tr>
                                <th><small class="bold">Bill No.</small></th>
                                <th><small class="bold">Customer</small></th>
                                <th class="centerAlign"><small class="bold">Qty.</small></th>
                                <th class="rightAlign"><small class="bold">Disc. ({!!html_entity_decode($nav_type[0]['currency_title'])!!})</small></th>
                                <th class="rightAlign"><small class="bold">Amount ({!!html_entity_decode($nav_type[0]['currency_title'])!!})</small></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            if(sizeof($sales)!=0)
                            {
                                ?>
                                @foreach($sales as $billkey=>$billvalue)
                                <tr>
                                    <td class="leftAlign"><small>{{$billvalue->bill_no}}</small></td>
                                    <td class="leftAlign"><small>{{$billvalue['customer']['customer_name']}}</small></td>
                                    <td class="centerAlign"><small>{{$billvalue->total_qty}}</small></td>
                                    <td class="rightAlign"><small>{{number_format($billvalue->totaldiscount,2)}}</small></td>
                                    <td class="rightAlign"><small>{{number_format($billvalue->total_bill_amount,2)}}</small></td>
                                </tr>
                                @endforeach
                                <?php
                                $countbills =  ($billkey+1);
                            }
                            else
                            {
                                ?>
                                <tr>
                                    <td colspan="5"><small>no result found...</small></td>
                                </tr>
                                <?php
                                $countbills  =   0;
                            }
                            ?>
                            </tbody>
                        </table>
                        
                    </div>
                </div>
                
                <?php if($countbills>0){?>
                <div class="row">
                    <div class="col-md-9">&nbsp;</div>
                    <div class="col-md-3"><center><a href="{{URL::to('/')}}/view_bill"><div class="viewButton col-md-12 cursor">View All</div></a></center></div>
                </div>
                <?php }?>

            </div>
            <!-- LATEST BILLS BLOCK -->

            <!-- LATEST RETURN BILLS BLOCK -->
            <div id="2" data-id="{{$array_srt_2['0']}}" class="ui-state-default col-md-4 ord">
                <small class="bold">LATEST RETURN BILLS (showing <span class="countreturn"></span> out of {{$returncount}} records)</small>
                <div class="card card-sm pa-0 mt-10">
                    <div class="table-responsive dashboardTbl">
                        <table class="table table-primary table-bordered mb-0 sorttable">
                            <thead class="thead-primary_default">
                            <tr>
                                <th><small class="bold">Bill No.</small></th>
                                <th><small class="bold">Customer</small></th>
                                <th class="centerAlign"><small class="bold">Qty.</small></th>
                                <th class="rightAlign"><small class="bold">Disc. ({!!html_entity_decode($nav_type[0]['currency_title'])!!})</small></th>
                                <th class="rightAlign"><small class="bold">Amount ({!!html_entity_decode($nav_type[0]['currency_title'])!!})</small></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            if(sizeof($returnbill)!=0)
                            {
                                ?>
                                @foreach($returnbill as $returnkey=>$returnvalue)
                                <tr>
                                    <td class="leftAlign"><small>{{$returnvalue['sales_bill']['bill_no']}}</small></td>
                                    <td class="leftAlign"><small>{{$returnvalue['customer']['customer_name']}}</small></td>
                                    <td class="centerAlign"><small>{{$returnvalue->total_qty}}</small></td>
                                    <td class="rightAlign"><small>{{number_format($returnvalue->totaldiscount,2)}}</small></td>
                                    <td class="rightAlign"><small>{{number_format($returnvalue->total_bill_amount,2)}}</small></td>
                                </tr>
                                @endforeach
                            <?php
                                $countreturn =  ($returnkey+1);
                            }
                            else
                            {
                                ?>
                                <tr>
                                    <td colspan="5"><small>no result found...</small></td>
                                </tr>
                                <?php
                                $countreturn  =   0;
                            }
                            ?>
                            </tbody>
                        </table>
                        
                    </div>
                </div>

                <?php if($countreturn>0){?>
                <div class="row">
                    <div class="col-md-9">&nbsp;</div>
                    <div class="col-md-3"><center><a href="{{URL::to('/')}}/view_bill"><div class="viewButton col-md-12 cursor">View All</div></a></center></div>
                </div>
                <?php }?>

            </div>
            <!-- LATEST RETURN BILLS BLOCK -->

            <!-- TOP SELLING PRODUCTS BLOCK -->
            <div id="3" data-id="{{$array_srt_3['0']}}" class="ui-state-default col-md-4 ord">
                <small class="bold">TOP SELLING PRODUCTS <span class="counttopselling"></span></small>
                <div class="card card-sm pa-0 mt-10">
                    <div class="table-responsive dashboardTbl">
                        <table class="table table-primary table-bordered mb-0 sorttable">
                            <thead class="thead-primary_default">
                            <tr>
                                <th><small class="bold">Product</small></th>
                                <th><small class="bold">Barcode</small></th>
                                <th class="rightAlign"><small class="bold">Total Qty.</small></th>
                                <th class="rightAlign"><small class="bold">Amount ({!!html_entity_decode($nav_type[0]['currency_title'])!!})</small></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            if(sizeof($topSelling)!=0)
                            {
                                ?>
                                @foreach($topSelling as $topsellingkey=>$topsellingvalue)
                                <tr>
                                    <td class="leftAlign"><small>{{$topsellingvalue['product']['product_name']}}</small></td>
                                    <td class="leftAlign"><small>{{$topsellingvalue['product']['product_system_barcode']}}</small></td>
                                    <td class="rightAlign"><small>{{$topsellingvalue['sum(qty)']}}</small></td>
                                    <td class="rightAlign"><small>{{number_format(($topsellingvalue->total_amount*$topsellingvalue['sum(qty)']),2)}}</small></td>
                                </tr>
                                @endforeach
                            <?php
                                $counttopselling =  ($topsellingkey+1);
                            }
                            else
                            {
                                ?>
                                <tr>
                                    <td colspan="5"><small>no result found...</small></td>
                                </tr>
                                <?php
                                $counttopselling  =   0;
                            }
                            ?>
                            </tbody>
                        </table>
                        
                    </div>
                </div>
                
                <?php if($counttopselling>0){?>
                <div class="row">
                    <div class="col-md-9">&nbsp;</div>
                    <div class="col-md-3"><center><a href="{{URL::to('/')}}/view_productwise_bill"><div class="viewButton col-md-12 cursor">View All</div></a></center></div>
                </div>
                <?php }?>

            </div>
            <!-- TOP SELLING PRODUCTS BLOCK -->

            <!-- LOW / OUT OF STOCK PRODUCTS BLOCK -->
            <div id="4" data-id="{{$array_srt_4['0']}}" class="ui-state-default col-md-4 ord">
                <small class="bold">LOW / OUT OF STOCK PRODUCTS (showing <span id="totalCount"></span> out of {{$lowStockcount}} records)</small>
                <div class="card card-sm pa-0 mt-10">
                    <div class="table-responsive dashboardTbl">
                        <table class="table table-primary table-bordered mb-0 sorttable">
                            <thead class="thead-primary_default">
                            <tr>
                                <th><small class="bold">Barcode</small></th>
                                <th><small class="bold">Product Name</small></th>
                                <th><small class="bold centerAlign">pCode</small></th>
                                <th><small class="bold rightAlign">In Stock</small></th>
                                <th><small class="bold rightAlign">Stock Alert</small></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            if(sizeof($lowStock)!=0)
                            {
                                ?>
                                @foreach($lowStock as $key=>$value)
                                <?php
                                $barcode    =   $value->supplier_barcode==''?$value->product_system_barcode:$value->supplier_barcode;
                                ?>
                                <tr>
                                    <td class="leftAlign pa-5"><small>{{$barcode}}</small></td>
                                    <td class="leftAlign pa-5"><small>{{$value->product_name}}</small></td>
                                    <td class="leftAlign pa-5"><small>{{$value->product_code==''?'-':$value->product_code}}</small></td>
                                    <td class=" pa-5"><small>{{$value->totalstock==''?0:$value->totalstock}}</small></td>
                                    <td class=" pa-5"><small>{{$value->alert_product_qty}}</small></td>
                                </tr>
                                @endforeach
                            <?php
                                    $countlowstock =  ($key+1);
                                }
                                else
                                {
                                    ?>
                                    <tr>
                                        <td colspan="5"><small>no result found...</small></td>
                                    </tr>
                                    <?php
                                    $countlowstock  =   0;
                                }
                            ?>   
                            </tbody>
                        </table>
                        
                    </div>
                </div>

                <?php if($countlowstock!=0){?>
                <div class="row">
                    <div class="col-md-9">&nbsp;</div>
                    <div class="col-md-3"><center><a href="{{URL::to('/')}}/lowstock_report"><div class="viewButton col-md-12 cursor">View All</div></a></center></div>
                </div>
                <?php }?>

            </div>
            <!-- LOW / OUT OF STOCK PRODUCTS BLOCK -->

            <!-- CUSTOMER OUTSTANDING PAYMENTS BLOCK -->
            <div id="5" data-id="{{$array_srt_5['0']}}" class="ui-state-default col-md-4 ord">
                <small class="bold">CUSTOMER OUTSTANDING PAYMENTS <span class="countblnc"></span></small>
                <div class="card card-sm pa-0 mt-10">
                    <div class="table-responsive dashboardTbl">
                        <table class="table table-primary table-bordered mb-0 sorttable">
                            <thead class="thead-primary_default">
                            <tr>
                                <th><small class="bold">Customer</small></th>
                                <th><small class="bold">Mobile</small></th>
                                <th><small class="bold">Email Address</small></th>
                                <th class="rightAlign"><small class="bold">Amount ({!!html_entity_decode($nav_type[0]['currency_title'])!!})</small></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            if(sizeof($customerbaldata)!=0)
                            {
                                ?>
                                @foreach($customerbaldata as $key1=>$value1)
                                    <tr>
                                        <td class="leftAlign pa-5"><small>{{$value1->customer['customer_name']}}</small></td>
                                        <td class="leftAlign pa-5"><small>{{$value1->customer['customer_mobile']}}</small></td>
                                        <td class="leftAlign pa-5"><small>{{$value1->customer['customer_email']}}</small></td>
                                        <td class="rightAlign pa-5"><small>{{number_format($value1->totalbalance,2)}}</small></td>
                                    </tr>
                                @endforeach
                                <?php
                                    $countblnc =  ($key1+1);
                                }
                                else
                                {
                                    ?>
                                    <tr>
                                        <td colspan="4"><small>no result found...</small></td>
                                    </tr>
                                    <?php
                                    $countblnc  =   0;
                                }
                            ?>   
                            </tbody>
                        </table>
                        
                    </div>
                </div>

                <?php if($countblnc!=0){?>
                <div class="row">
                    <div class="col-md-9">&nbsp;</div>
                    <div class="col-md-3"><center><a href="{{URL::to('/')}}/customer_credit_summary"><div class="viewButton col-md-12 cursor">View All</div></a></center></div>
                </div>
                <?php }?>
            
            </div>
            <!-- CUSTOMER OUTSTANDING PAYMENTS BLOCK -->

            <div id="6" data-id="{{$array_srt_6['0']}}" class="ui-state-default col-md-4 ord">
                <small class="bold">TOP RETURNING PRODUCTS <span class="countreturnProduct"></span></small>
                <div class="card card-sm pa-0 mt-10">
                    <div class="table-responsive dashboardTbl">
                        <table class="table table-primary table-bordered mb-0 sorttable">
                            <thead class="thead-primary_default">
                            <tr>
                                <th><small class="bold">Product</small></th>
                                <th><small class="bold">Barcode</small></th>
                                <th class="rightAlign"><small class="bold">Total Qty.</small></th>
                                <th class="rightAlign"><small class="bold">Amount ({!!html_entity_decode($nav_type[0]['currency_title'])!!})</small></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            if(sizeof($topReturn)!=0)
                            {
                                ?>
                                @foreach($topReturn as $topReturnkey=>$topReturnvalue)
                                <tr>
                                    <td class="leftAlign"><small>{{$topReturnvalue['product']['product_name']}}</small></td>
                                    <td class="leftAlign"><small>{{$topReturnvalue['product']['product_system_barcode']}}</small></td>
                                    <td class="rightAlign"><small>{{$topReturnvalue['sum(qty)']}}</small></td>
                                    <td class="rightAlign"><small>{{number_format(($topReturnvalue->total_amount*$topReturnvalue['sum(qty)']),2)}}</small></td>
                                </tr>
                                @endforeach
                            <?php
                                $countreturnProduct =  ($topReturnkey+1);
                            }
                            else
                            {
                                ?>
                                <tr>
                                    <td colspan="5"><small>no result found...</small></td>
                                </tr>
                                <?php
                                $countreturnProduct  =   0;
                            }
                            ?>
                            </tbody>
                        </table>
                        
                    </div>
                </div>
                
                <?php if($countreturnProduct>0){?>
                <div class="row">
                    <div class="col-md-9">&nbsp;</div>
                    <div class="col-md-3"><center><a href="{{URL::to('/')}}/view_productwise_bill"><div class="viewButton col-md-12 cursor">View All</div></a></center></div>
                </div>
                <?php }?>
            </div>

      
      <!-- <div class="sortable2 list"></div> -->

    </div>

    <div class="sortable1 list"></div>

    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery/js/jquery.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/public/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
    <script src="{{URL::to('/')}}/public/modulejs/common.js"></script>

    <!-- EChartJS JavaScript -->
    <script src="{{URL::to('/')}}/vendor/echarts/dist/echarts-en.min.js"></script>

    <script type="text/javascript">
    // Sort Div Code
        jQuery("#sortable1").sortable({
            connectWith: ".connectedSortable",
            stop: function(event, ui) {
                $('.connectedSortable').each(function()
                {
                    result = "";
                    $(this).find(".ui-state-default").each(function(){
                        result += $(this).attr('id') + ",";
                    });

                    // $(".list").html(result);

                    $.ajax({
                        url:'dashboard_sort',

                        data: {
                            sorting:result,
                        },
                        success:function(data)
                        {
                            // console.log(data);
                        }
                    })

                });
            }
        });
    // Sort Div Code

    $(document).ready(function(e)
    {

        var list_val = '<?php echo json_encode($sortDiv) ?>';

        var dta = JSON.parse(list_val,true);

        $.each(dta,function (k,v)
        {
            $("#sortable1").find('.ord').each(function ()
            {
                if($(this).attr('id') != ''){
                        if($(this).attr('id') == v)
                        {
                            $(this).data('id',k);
                        }
                }
            });

        });


        $("#sortable1 div.ord").sort(function(a, b)
        {
          return $(a).data('id') - $(b).data('id');
        }).each(function() {
          var elem = $(this);
          elem.remove();
          $(elem).appendTo("#sortable1");
        });





        $('.countbills').html('<?php echo $countbills?>');
        $('.countreturn').html('<?php echo $countreturn?>');
        $('.counttopselling').html('(<?php echo $counttopselling?>)');
        $('.countblnc').html('(<?php echo $countblnc?>)');
        $('.countreturnProduct').html('(<?php echo $countreturnProduct?>)');
    });


    </script>

    <script type="text/javascript">
    var echartsConfig = function() {

    if( $('#e_chart_11').length > 0 ){
        var eChart_11 = echarts.init(document.getElementById('e_chart_11'));
        var option10 = {
            color: ['#88c241'],
            tooltip: {
                show: true,
                trigger: 'axis',
                backgroundColor: '#fff',
                borderRadius:6,
                padding:0,
                axisPointer:{
                    lineStyle:{
                        width:0,
                    }
                },
                textStyle: {
                    color: '#324148',
                    fontFamily: '"Nunito", sans-serif',
                    fontSize: 12
                }
            },

            xAxis: [{
                type: 'category',
                data: [<?php echo $days_?>],
                axisLine: {
                    show:false
                },
                axisTick: {
                    show:false
                },
                axisLabel: {
                    textStyle: {
                        color: '#5e7d8a'
                    }
                }
            }],
            yAxis: {
                type: 'value',
                axisLine: {
                    show:false
                },
                axisTick: {
                    show:false
                },
                axisLabel: {
                    textStyle: {
                        color: '#5e7d8a'
                    }
                },
                splitLine: {
                    lineStyle: {
                        color: 'transparent',
                    }
                }
            },
            grid: {
                top: '3%',
                left: '3%',
                right: '3%',
                bottom: '3%',
                containLabel: true
            },
            series: [{
                data: [<?php echo $daySales__?>],
                type: 'bar',
                barMaxWidth: 30,
                itemStyle: {
                    normal: {
                        barBorderRadius: [6, 6, 0, 0] ,
                    }
                },
                label: {
                    normal: {
                        show: false,
                        position: 'outside'
                    }
                },
            }]
        };
        eChart_11.setOption(option10);
        eChart_11.resize();
    }
}
/*****E-Charts function end*****/

/*****Resize function start*****/
var echartResize;
$(window).on("resize", function () {
    /*E-Chart Resize*/
    clearTimeout(echartResize);
    echartResize = setTimeout(echartsConfig, 200);
}).resize();
/*****Resize function end*****/

/*****Function Call start*****/
echartsConfig();
/*****Function Call end*****/
    </script>




@endsection
