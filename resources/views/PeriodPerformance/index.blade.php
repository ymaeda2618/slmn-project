@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">期間実績一覧</div>

        <!--検索エリア-->
        <div class='search-area'>
            <form id="index-search-form" method="post" action='./PeriodPerformanceIndex' enctype="multipart/form-data">
                {{ csrf_field() }}
                <table>
                    <tbody>
                        <tr>
                            <td><div class="table-attention-th">最大表示件数は300件</div></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="table-th">対象日付</div>
                                <div class="table-td radio_box">
                                    <label class="radio-label"><input type="radio" name="data[PeriodPerformance][date_type]" value="1" {{$pp_check_str_slip_date}}> 伝票日付</label>
                                    <label class="radio-label"><input type="radio" name="data[PeriodPerformance][date_type]" value="2" {{$pp_check_str_deliver_date}}> 納品日付</label>
                                </div>
                                <div class="table-td">
                                    <input type="date" class="search-control" id="sale_date_from" name="data[PeriodPerformance][date_from]" value="{{$pp_date_from}}" tabindex="1">
                                </div>
                                <div class="table-td">
                                    <input type="date" class="search-control" id="sale_date_to" name="data[PeriodPerformance][date_to]" value="{{$pp_date_to}}" tabindex="2">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="table-th">仕入先店舗</div>
                                <div class="table-td table-code-td">
                                    <input type="text" class="search-control supply_company_code_input" id="supply_company_code" name="data[PeriodPerformance][supply_company_code]" value="{{$pp_supply_company_code}}" tabindex="3">
                                    <input type="hidden" id="supply_company_id" name="data[PeriodPerformance][supply_company_id]" value="{{$pp_supply_company_id}}">
                                </div>
                                <div class="table-td table-name-td">
                                    <input type="text" class="search-control" id="supply_company_text" name="data[PeriodPerformance][supply_company_text]" value="{{$pp_supply_company_text}}" readonly>
                                </div>
                                <div class="table-th">売上先店舗</div>
                                <div class="table-td table-code-td">
                                    <input type="text" class="search-control sale_company_code_input" id="sale_company_code" name="data[PeriodPerformance][sale_company_code]" value="{{$pp_sale_company_code}}" tabindex="5">
                                    <input type="hidden" id="sale_company_id" name="data[PeriodPerformance][sale_company_id]" value="{{$pp_sale_company_id}}">
                                </div>
                                <div class="table-td table-name-td">
                                    <input type="text" class="search-control" id="sale_company_text" name="data[PeriodPerformance][sale_company_text]" value="{{$pp_sale_company_text}}" readonly>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="table-th">製品</div>
                                <div class="table-td table-code-td">
                                    <input type="text" class="search-control product_code_input" id="product_code" name="data[PeriodPerformance][product_code]" value="{{$pp_product_code}}" tabindex="7">
                                    <input type="hidden" id="product_id" name="data[PeriodPerformance][product_id]" value="{{$pp_product_id}}">
                                </div>
                                <div class="table-td table-name-td">
                                    <input type="text" class="search-control" id="product_text" name="data[PeriodPerformance][product_text]" value="{{$pp_product_text}}" readonly>
                                </div>
                                <div class="table-th">担当者</div>
                                <div class="table-td table-code-td">
                                    <input type="text" class="search-control staff_code_input" id="staff_code" name="data[PeriodPerformance][staff_code]" value="{{$pp_staff_code}}" tabindex="7">
                                    <input type="hidden" id="staff_id" name="data[PeriodPerformance][staff_id]" value="{{$pp_staff_id}}">
                                </div>
                                <div class="table-td table-name-td">
                                    <input type="text" class="search-control" id="staff_text" name="data[PeriodPerformance][staff_text]" value="{{$pp_staff_text}}" readonly>
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

        <!--一覧表示エリア-->
        <div class='list-area'>
            <table class='index-table'>
                <tbody>
                    <tr>
                        <th>No.</th>
                        <th>コード</th>
                        <th>商品名</th>
                        <th colspan="3">仕入</th>
                        <th colspan="3">売上</th>
                        <th>利益金額</th>
                    </tr>
                </tbody>
            </table>

            <?php $no = 1; ?>

            @foreach ($productlList as $productVal)
            <table class='index-table'>
                <tbody>
                    <tr>
                        <td>
                            <!--No.-->{{$no}}
                        </td>
                        <td>
                            <!--製品コード-->{{$productVal->product_code}}
                        </td>
                        <td>
                            <!--商品名-->{{$productVal->product_name}}
                        </td>
                        <td>
                            <!--仕入数量-->{{preg_replace("/\.?0+$/","",number_format($productVal->supply_sum_unit_num, 2))}}
                        </td>
                        <td>
                            <!--単位-->{{$productVal->unit_name}}
                        </td>
                        <td>
                            <!--仕入金額-->{{preg_replace("/\.?0+$/","",number_format($productVal->supply_product_amount, 2))}}円
                        </td>
                        <td>
                            <!--売上数量-->{{preg_replace("/\.?0+$/","",number_format($productVal->sale_sum_unit_num, 2))}}
                        </td>
                        <td>
                            <!--単位-->{{$productVal->unit_name}}
                        </td>
                        <td>
                            <!--売上金額-->{{preg_replace("/\.?0+$/","",number_format($productVal->sale_product_amount, 2))}}円
                        </td>
                        <td>
                            @if (0 > $productVal->profit)
                            <!--利益金額--><font color="red">{{preg_replace("/\.?0+$/","",number_format($productVal->profit, 2))}}円</font>
                            @else
                            <!--利益金額-->{{preg_replace("/\.?0+$/","",number_format($productVal->profit, 2))}}円
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php $no += 1;?>
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
            $(document).on("keypress", "input", function(event) {

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

                } else if (event.keyCode === 47) { // スラッシュが押された時

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
            // autocomplete処理 仕入店舗ID
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
            // autocomplete処理 売上店舗ID
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
            // autocomplete処理 担当者ID
            //-------------------------------------
            $(".staff_code_input").autocomplete({
                source: function(req, resp) {
                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./AjaxAutoCompleteStaff",
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

                if (selector_code.match(/supply_company/)) { // 仕入先店舗

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

                } else if (selector_code.match(/sale_company/)) { // 売上先店舗

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
                } else if (selector_code.match(/staff/)) { // 担当者IDの部分

                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./AjaxSetStaff",
                        type: "POST",
                        dataType: "JSON",
                        data: fd,
                        processData: false,
                        contentType: false
                    })
                    .done(function (data) {

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

    .table-attention-th{
        width: 100%;
        font-size: 12px;
        color: red;
        text-align: right;
        padding: 15px 0px 0px 10px;
        padding-right: 5%;
        float: left;
        font-weight: bolder;
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
        padding: 10px;
        padding-left: 10px;
        background-color: #57595b;
        font-weight: bold;
        color: white;
        font-size: 10px;
        letter-spacing: 1px;
        border: 1px solid #bcbcbc;
        width:14%;
    }

    .index-table th:first-of-type {
        width: 5%;
    }

    .index-table th:nth-of-type(2) {
        width: 10%;
    }

    .index-table th:nth-of-type(3) {
        width: 25%;
    }

    .index-table th:nth-of-type(4),
    .index-table th:nth-of-type(5) {
        width: 23%;
    }

    .index-table td {
        font-size: 10px;
        padding: 8px;
        border: 1px solid #bcbcbc;
        border-top: none;
        font-weight: bold;
        padding-right: 1%;
        text-align: right;
        width:14%;
    }

    .index-table td:first-of-type {
        padding: 0px;
        text-align: center;
        width: 5%;
        background-color: #efefef;
        font-weight: unset;
    }

    .index-table td:nth-of-type(2) {
        padding-left: 20px;
        text-align: left;
        width: 10%;
    }
    .index-table td:nth-of-type(3) {
        padding-left: 20px;
        text-align: left;
        width: 25%;
    }
    .index-table td:nth-of-type(4),
    .index-table td:nth-of-type(7) {
        text-align: right;
        width: 7%;
        padding: 0px;
        padding-right: 1%;
    }
    .index-table td:nth-of-type(5),
    .index-table td:nth-of-type(8) {
        padding-right: 20px;
        text-align: center;
        width: 5%;
        padding: 0px;
    }
    .index-table td:nth-of-type(6),
    .index-table td:nth-of-type(9) {
        padding-right: 1%;
        text-align: right;
        width: 11%;
    }
</style>
