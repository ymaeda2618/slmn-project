@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">日別売上一覧</div>

        <!--検索エリア-->
        <div class='search-area'>
            <form id="index-search-form" method="post" action='./DailyPerformanceIndex' enctype="multipart/form-data">
                {{ csrf_field() }}
                <table>
                    <tbody>
                        <tr>
                            <td>
                                <div class="table-th">対象日付</div>
                                <div class="table-td radio_box">
                                    <label class="radio-label"><input type="radio" name="data[DailyPerformance][date_type]" value="1" {{$dp_check_str_slip_date}}> 伝票日付</label>
                                    <label class="radio-label"><input type="radio" name="data[DailyPerformance][date_type]" value="2" {{$dp_check_str_deliver_date}}> 納品日付</label>
                                </div>
                                <div class="table-td">
                                    <select class="search-control " id="daily_performance_target_year" name="data[DailyPerformance][target_year]">
                                        @foreach ($year_arr as $year_key => $year_val)
                                        @if ($year_key == $dp_daily_performance_target_year)
                                        <option value="{{$year_key}}" selected>{{$year_val}}</option>
                                        @else
                                        <option value="{{$year_key}}">{{$year_val}}</option>
                                        @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="table-td">
                                    <select class="search-control " id="daily_performance_target_month " name="data[DailyPerformance][target_month]">
                                        @foreach ($month_arr as $month_key => $month_val)
                                        @if ($month_key == $dp_daily_performance_target_month)
                                        <option value="{{$month_key}}" selected>{{$month_val}}</option>
                                        @else
                                        <option value="{{$month_key}}">{{$month_val}}</option>
                                        @endif
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="table-th">仕入先企業</div>
                                <div class="table-td table-code-td">
                                    <input type="text" class="search-control supply_company_code_input" id="supply_company_code" name="data[DailyPerformance][supply_company_code]" value="{{$dp_supply_company_code}}" tabindex="3">
                                    <input type="hidden" id="supply_company_id" name="data[DailyPerformance][supply_company_id]" value="{{$dp_supply_company_id}}">
                                </div>
                                <div class="table-td table-name-td">
                                    <input type="text" class="search-control" id="supply_company_text" name="data[DailyPerformance][supply_company_text]" value="{{$dp_supply_company_text}}" readonly>
                                </div>
                                <div class="table-th">仕入先店舗</div>
                                <div class="table-td table-code-td">
                                    <input type="text" class="search-control supply_shop_code_input" id="supply_shop_code" name="data[DailyPerformance][supply_shop_code]" value="{{$dp_supply_shop_code}}" tabindex="4">
                                    <input type="hidden" id="supply_shop_id" name="data[DailyPerformance][supply_shop_id]" value="{{$dp_supply_shop_id}}">
                                </div>
                                <div class="table-td table-name-td">
                                    <input type="text" class="search-control read-only" id="supply_shop_text" name="data[DailyPerformance][supply_shop_text]" value="{{$dp_supply_shop_text}}" readonly>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="table-th">売上先企業</div>
                                <div class="table-td table-code-td">
                                    <input type="text" class="search-control sale_company_code_input" id="sale_company_code" name="data[DailyPerformance][sale_company_code]" value="{{$dp_sale_company_code}}" tabindex="3">
                                    <input type="hidden" id="sale_company_id" name="data[DailyPerformance][sale_company_id]" value="{{$dp_sale_company_id}}">
                                </div>
                                <div class="table-td table-name-td">
                                    <input type="text" class="search-control" id="sale_company_text" name="data[DailyPerformance][sale_company_text]" value="{{$dp_sale_company_text}}" readonly>
                                </div>
                                <div class="table-th">売上先店舗</div>
                                <div class="table-td table-code-td">
                                    <input type="text" class="search-control sale_shop_code_input" id="sale_shop_code" name="data[DailyPerformance][sale_shop_code]" value="{{$dp_sale_shop_code}}" tabindex="4">
                                    <input type="hidden" id="sale_shop_id" name="data[DailyPerformance][sale_shop_id]" value="{{$dp_sale_shop_id}}">
                                </div>
                                <div class="table-td table-name-td">
                                    <input type="text" class="search-control read-only" id="sale_shop_text" name="data[DailyPerformance][sale_shop_text]" value="{{$dp_sale_shop_text}}" readonly>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="table-th">製品</div>
                                <div class="table-td table-code-td">
                                    <input type="text" class="search-control product_code_input" id="product_code" name="data[DailyPerformance][product_code]" value="{{$dp_product_code}}" tabindex="5">
                                    <input type="hidden" id="product_id" name="data[DailyPerformance][product_id]" value="{{$dp_product_id}}">
                                </div>
                                <div class="table-td table-name-td">
                                    <input type="text" class="search-control" id="product_text" name="data[DailyPerformance][product_text]" value="{{$dp_product_text}}" readonly>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="btn-area ">
                    <div class='search-btn-area'>
                        <input type='submit' class='search-btn btn-primary' name='search-btn' id="search-btn" value='検索'>
                        <input type='submit' class='initial-btn' name='reset-btn' id="reset-btn" value='検索条件リセット'>
                    </div>
                </div>
            </form>
        </div>

        <!--総計表示エリア-->
        <div class='sum-display-area'>
            <div class='sum-display-div'>仕入総額:{{number_format($supply_total_amount)}}円</div>
            <div class='sum-display-div'>売上総額:{{number_format($sale_total_amount)}}円</div>
        </div>

        <!--一覧表示エリア-->
        <div class='list-area'>
            <table class='index-table'>
                <tbody>
                    <tr>
                        <th>日付</th>
                        <th colspan="2">仕入金額</th>
                        <th colspan="2">売上金額</th>
                    </tr>
                </tbody>
            </table>

            @foreach ($daily_performance_arr as $key_date => $daily_performance_val)
            <table class='index-table'>
                <tbody>
                    <tr>
                        <td>
                            <!--日付-->{{$key_date}}
                        </td>
                        <td>
                            <!--仕入金額-->{{number_format($daily_performance_val['supply_daily_amount'])}}
                        </td>
                        <td>
                            <!--売上金額-->{{number_format($daily_performance_val['sale_daily_amount'])}}
                        </td>
                    </tr>
                </tbody>
            </table>
            @endforeach
        </div>

    </div>
</div>
@endsection
<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript">
    (function($) {
        jQuery(window).load(function() {

            //-------------------------------------
            // Enterと-を押したときにタブ移動する処理
            //-------------------------------------
            $(document).on("keyup", "input", function(event) {

                if (event.keyCode === 13) { // Enterが押された時

                    var this_id = $(this).attr('id');

                    var tabindex = parseInt($(this).attr('tabindex'), 10);
                    if (isNaN(tabindex) && this_id == "search-btn") {
                        $('#index-search-form').submit();
                        return;
                    } else if (isNaN(tabindex)) return false;

                    tabindex += 1;

                    if ($('input[tabindex="' + tabindex + '"]').length) {

                        var this_val = $('input[tabindex="' + tabindex + '"]').val();
                        $('input[tabindex="' + tabindex + '"]').val("");
                        $('input[tabindex="' + tabindex + '"]').focus();
                        $('input[tabindex="' + tabindex + '"]').val(this_val);

                    } else {

                        var this_val = $('#search-btn').val();
                        $('#search-btn').val("");
                        $('#search-btn').focus();
                        $('#search-btn').val(this_val);
                    }

                    return false;

                } else if (event.keyCode === 111) { // スラッシュが押された時

                    var this_id = $(this).attr('id');

                    // 文字列の最後の文字を削除
                    $(this).val($(this).val().slice(0, -1));

                    if (this_id == "search-btn") { // 検索ボタンの場合

                        var this_val = $('input[tabindex="3"]').val();
                        $('input[tabindex="3"]').val("");
                        $('input[tabindex="3"]').focus();
                        $('input[tabindex="3"]').val(this_val);

                    } else {

                        var tabindex = parseInt($(this).attr('tabindex'), 10);
                        if (isNaN(tabindex)) return false;

                        tabindex -= 1;

                        if ($('input[tabindex="' + tabindex + '"]').length) {
                            var this_val = $('input[tabindex="' + tabindex + '"]').val();
                            $('input[tabindex="' + tabindex + '"]').val("");
                            $('input[tabindex="' + tabindex + '"]').focus();
                            $('input[tabindex="' + tabindex + '"]').val(this_val);
                        }

                    }

                    return false;
                }

            });


            //-------------------------------------
            // autocomplete処理 仕入企業ID
            //-------------------------------------
            $(".supply_company_code_input").autocomplete({
                source: function(req, resp) {
                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./AjaxAutoCompleteSupplyCompany",
                        type: "POST",
                        cache: false,
                        dataType: "json",
                        data: {
                            inputText: req.term
                        },
                        success: function(o) {
                            resp(o);
                        },
                        error: function(xhr, ts, err) {
                            resp(['']);
                        }
                    });
                }
            });

            //-------------------------------------
            // autocomplete処理 仕入店舗ID
            //-------------------------------------
            $(".supply_shop_code_input").autocomplete({
                source: function(req, resp) {
                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./AjaxAutoCompleteSupplyShop",
                        type: "POST",
                        cache: false,
                        dataType: "json",
                        data: {
                            inputText: req.term
                        },
                        success: function(o) {
                            resp(o);
                        },
                        error: function(xhr, ts, err) {
                            resp(['']);
                        }
                    });
                }
            });

            //-------------------------------------
            // autocomplete処理 売上企業ID
            //-------------------------------------
            $(".sale_company_code_input").autocomplete({
                source: function(req, resp) {
                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./AjaxAutoCompleteSaleCompany",
                        type: "POST",
                        cache: false,
                        dataType: "json",
                        data: {
                            inputText: req.term
                        },
                        success: function(o) {
                            resp(o);
                        },
                        error: function(xhr, ts, err) {
                            resp(['']);
                        }
                    });
                }
            });

            //-------------------------------------
            // autocomplete処理 売上店舗ID
            //-------------------------------------
            $(".sale_shop_code_input").autocomplete({
                source: function(req, resp) {
                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./AjaxAutoCompleteSaleShop",
                        type: "POST",
                        cache: false,
                        dataType: "json",
                        data: {
                            inputText: req.term
                        },
                        success: function(o) {
                            resp(o);
                        },
                        error: function(xhr, ts, err) {
                            resp(['']);
                        }
                    });
                }
            });

            //-------------------------------------
            // autocomplete処理 製品ID
            //-------------------------------------
            $(".product_code_input").autocomplete({
                source: function(req, resp) {
                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./AjaxAutoCompleteProduct",
                        type: "POST",
                        cache: false,
                        dataType: "json",
                        data: {
                            inputText: req.term
                        },
                        success: function(o) {
                            resp(o);
                        },
                        error: function(xhr, ts, err) {
                            resp(['']);
                        }
                    });
                }
            });

            //-------------------------------------
            // autocomplete処理 製品ID
            //-------------------------------------
            $(".product_code_input").autocomplete({
                source: function(req, resp) {
                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./AjaxAutoCompleteProduct",
                        type: "POST",
                        cache: false,
                        dataType: "json",
                        data: {
                            inputText: req.term
                        },
                        success: function(o) {
                            resp(o);
                        },
                        error: function(xhr, ts, err) {
                            resp(['']);
                        }
                    });
                }
            });

            //-------------------------------------
            // フォーカスアウトしたときの処理
            //-------------------------------------
            $(document).on("blur", "input", function(event) {

                var tabindex = parseInt($(this).attr('tabindex'), 10);
                var set_val = $(this).val();
                // 全角数字を半角に変換
                set_val = set_val.replace(/[０-９]/g, function(s) {
                    return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
                });
                $(this).val(set_val);
                var selector_code = $(this).attr('id');
                var selector_id = selector_code.replace('_code', '_id');
                var selector_text = selector_code.replace('_code', '_text');

                var fd = new FormData();
                fd.append("inputText", set_val);

                if (selector_code.match(/supply_company/)) { // 仕入先企業

                    $.ajax({
                            headers: {
                                "X-CSRF-TOKEN": $("[name='_token']").val()
                            },
                            url: "./AjaxSetSupplyCompany",
                            type: "POST",
                            dataType: "JSON",
                            data: fd,
                            processData: false,
                            contentType: false
                        })
                        .done(function(data) {

                            $("#" + selector_code).val(data[0]);
                            $("#" + selector_id).val(data[1]);
                            $("#" + selector_text).val(data[2]);
                        });

                } else if (selector_code.match(/supply_shop/)) { // 仕入先店舗

                    $.ajax({
                            headers: {
                                "X-CSRF-TOKEN": $("[name='_token']").val()
                            },
                            url: "./AjaxSetSupplyShop",
                            type: "POST",
                            dataType: "JSON",
                            data: fd,
                            processData: false,
                            contentType: false
                        })
                        .done(function(data) {

                            $("#" + selector_code).val(data[0]);
                            $("#" + selector_id).val(data[1]);
                            $("#" + selector_text).val(data[2]);
                        });

                } else if (selector_code.match(/sale_company/)) { // 売上先企業

                    $.ajax({
                            headers: {
                                "X-CSRF-TOKEN": $("[name='_token']").val()
                            },
                            url: "./AjaxSetSaleCompany",
                            type: "POST",
                            dataType: "JSON",
                            data: fd,
                            processData: false,
                            contentType: false
                        })
                        .done(function(data) {

                            $("#" + selector_code).val(data[0]);
                            $("#" + selector_id).val(data[1]);
                            $("#" + selector_text).val(data[2]);
                        });

                } else if (selector_code.match(/sale_shop/)) { // 売上先店舗

                    $.ajax({
                            headers: {
                                "X-CSRF-TOKEN": $("[name='_token']").val()
                            },
                            url: "./AjaxSetSaleShop",
                            type: "POST",
                            dataType: "JSON",
                            data: fd,
                            processData: false,
                            contentType: false
                        })
                        .done(function(data) {

                            $("#" + selector_code).val(data[0]);
                            $("#" + selector_id).val(data[1]);
                            $("#" + selector_text).val(data[2]);
                        });

                } else if (selector_code.match(/product/)) { // 製品IDの部分

                    $.ajax({
                            headers: {
                                "X-CSRF-TOKEN": $("[name='_token']").val()
                            },
                            url: "./AjaxSetProduct",
                            type: "POST",
                            dataType: "JSON",
                            data: fd,
                            processData: false,
                            contentType: false
                        })
                        .done(function(data) {

                            var before_product_id = $("#" + selector_id).val();

                            $("#" + selector_code).val(data[0]);
                            $("#" + selector_id).val(data[1]);
                            $("#" + selector_text).val(data[2]);

                        });
                }
            });

        });
    })(jQuery);
</script>

<style>
    /* 共通 */
    
    .search-control {
        display: block;
        width: 100%;
        height: 30px;
        padding: 5px;
        font-size: 10px;
        line-height: 1.6;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    }
    
    .search-control[readonly] {
        background-color: #e9ecef;
        opacity: 1;
    }
    
    .top-title {
        max-width: 1300px;
        font-size: 1.4em;
        font-weight: bold;
        width: 90%;
        padding: 25px 0px 25px 20px;
    }
    
    .radio-label {
        margin-bottom: initial!important;
        font-weight: bolder;
        margin-right: 10px;
    }
    
    .search-area {
        max-width: 1300px;
        width: 90%;
        padding: 10px 0px 0px;
        border: 1px solid #bcbcbc;
        border-radius: 5px;
    }
    
    .search-area table {
        margin: auto;
        width: 100%;
    }
    
    .table-th {
        width: 10%;
        padding: 15px 0px 0px 10px;
        font-size: 10px;
        float: left;
        font-weight: bolder;
    }
    
    .table-td {
        width: 20%;
        padding: 10px;
        font-size: 10px;
        float: left;
    }
    
    .table-code-td {
        padding-right: 0px;
    }
    
    .table-name-td {
        padding-left: 0px;
    }
    
    .table-double-td {
        width: 40%;
        padding: 10px;
        font-size: 10px;
        float: left;
    }
    
    .radio_box {
        padding-top: 15px;
    }
    
    .search-btn-area {
        text-align: center;
        margin: 10px auto 10px;
        width: 100%;
        display: inline-block;
    }
    
    .search-btn {
        width: 80%;
        font-size: 10px;
        max-width: 150px;
        height: 30px;
        border-radius: 10px;
        margin-right: 2%;
    }
    
    .initial-btn {
        width: 80%;
        font-size: 10px;
        max-width: 150px;
        height: 30px;
        border-radius: 10px;
        margin-left: 2%;
    }
    /*総額エリア*/
    
    .sum-display-area {
        max-width: 1300px;
        width: 90%;
        padding-top: 20px;
        padding-left: 20px;
    }
    
    .sum-display-div {
        float: left;
        margin-right: 1rem;
        font-weight: bold;
        font-size: 14px;
    }
    /*伝票表示エリア*/
    
    .list-area {
        max-width: 1300px;
        width: 90%;
        margin: 20px auto 50px;
    }
    
    .index-table {
        width: 100%;
        letter-spacing: 2px;
    }
    
    .index-table th {
        width: 10%;
        padding: 10px;
        padding-left: 10px;
        background-color: #57595b;
        font-weight: bold;
        color: white;
        font-size: 10px;
        letter-spacing: 1px;
        border: 1px solid #bcbcbc;
    }
    
    .index-table td {
        font-size: 10px;
        padding-left: 20px;
        padding: 8px;
        border: 1px solid #bcbcbc;
        border-top: none;
        width: 10%;
    }
</style>