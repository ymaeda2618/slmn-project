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
                                    <input type="text" class="search-control product_code_input" id="product_code" name="data[DailyPerformanceDetail][product_code]" value="{{$dp_product_code}}" tabindex="5">
                                    <input type="hidden" id="product_id" name="data[DailyPerformanceDetail][product_id]" value="{{$dp_product_id}}">
                                </div>
                                <div class="table-td table-name-td">
                                    <input type="text" class="search-control" id="product_text" name="data[DailyPerformanceDetail][product_text]" value="{{$dp_product_text}}" readonly>
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

            @foreach ($daily_performance_arr as $daily_performance_val)
            <table class='index-table'>
                <tbody>
                    <tr>
                        <td>
                            <!--日付-->{{$daily_performance_val->daily_performance_date}}
                        </td>
                        <td>
                            <!--仕入金額-->{{number_format($daily_performance_val->supply_daily_amount)}}
                        </td>
                        <td>
                            <!--売上金額-->{{number_format($daily_performance_val->sale_daily_amount)}}
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

            // 検索されて選択状態の企業を取得
            var supply_submit_type_selected = $("#supply_submit_type_selected").val();
            // 検索条件で設定された企業を設定
            $('#supply_submit_type').val(supply_submit_type_selected);


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
        margin: 25px auto 50px;
    }
    
    .index-table {
        width: 100%;
        letter-spacing: 2px;
        border-top: solid 1px #ccc;
        border-bottom: solid 2px #ccc;
        margin: 5px 0px;
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
        width: 10%;
    }
</style>