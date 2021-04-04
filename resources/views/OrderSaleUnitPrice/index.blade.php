@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">売上発注単価一覧</div>

        <!--検索エリア-->
        <div class='search-area'>
            <form id="index-search-form" method="post" action='{{$search_action}}' enctype="multipart/form-data">
                {{ csrf_field() }}
                <table>
                    <tbody>
                        <tr>
                            <td>
                                <div class="table-th">適用日付</div>
                                <div class="table-td">
                                    <input type="date" class="search-control" id="apply_from" name="data[OrderSaleUnitPrice][apply_from]" value="{{$condition_date_from}}" tabindex="1">
                                </div>
                                <div class="table-td">
                                    <input type="date" class="search-control" id="apply_to" name="data[OrderSaleUnitPrice][apply_to]" value="{{$condition_date_to}}" tabindex="2">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="table-th">取引先企業</div>
                                <div class="table-td table-code-td">
                                    <input type="text" class="search-control sale_company_code_input" id="sale_company_code" name="data[OrderSaleUnitPrice][sale_company_code]" value="{{$condition_company_code}}" tabindex="3">
                                    <input type="hidden" id="sale_company_id" name="data[OrderSaleUnitPrice][sale_company_id]" value="{{$condition_company_id}}">
                                </div>
                                <div class="table-td table-name-td">
                                    <input type="text" class="search-control" id="sale_company_text" name="data[OrderSaleUnitPrice][sale_company_text]" value="{{$condition_company_text}}" readonly>
                                </div>
                                <div class="table-th">取引先店舗</div>
                                <div class="table-td table-code-td">
                                    <input type="text" class="search-control sale_shop_code_input" id="sale_shop_code" name="data[OrderSaleUnitPrice][sale_shop_code]" value="{{$condition_shop_code}}" tabindex="4">
                                    <input type="hidden" id="sale_shop_id" name="data[OrderSaleUnitPrice][sale_shop_id]" value="{{$condition_shop_id}}">
                                </div>
                                <div class="table-td table-name-td">
                                    <input type="text" class="search-control read-only" id="sale_shop_text" name="data[OrderSaleUnitPrice][sale_shop_text]" value="{{$condition_shop_text}}" readonly>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="table-th">売上製品</div>
                                <div class="table-td table-code-td">
                                    <input type="text" class="search-control product_code_input" id="product_code" name="data[OrderSaleUnitPrice][product_code]" value="{{$condition_product_code}}" tabindex="5">
                                    <input type="hidden" id="product_id" name="data[OrderSaleUnitPrice][product_id]" value="{{$condition_product_id}}">
                                </div>
                                <div class="table-td table-name-td">
                                    <input type="text" class="search-control" id="product_text" name="data[OrderSaleUnitPrice][product_text]" value="{{$condition_product_text}}" readonly>
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
                        <th>取引先コード</th>
                        <th class="double-width" colspan="2">取引先名</th>
                        <th>適用開始日</th>
                        <th>更新日</th>
                        <th></th>
                    </tr>
                </tbody>
            </table>

            @foreach ($orderSaleUnitPriceList as $orderSaleUnitPrices)
            <table class='index-table'>
                <tbody>
                    <tr>
                        <td>
                            <!--取引先コード-->{{$orderSaleUnitPrices->sale_company_code}}
                        </td>
                        <td class="double-width bold-tr" colspan="2">
                            <!--取引先名-->{{$orderSaleUnitPrices->sale_company_name}}
                        </td>
                        <td>
                            <!--適用開始日-->{{$orderSaleUnitPrices->apply_from}}
                        </td>
                        <td>
                            <!--更新日-->{{$orderSaleUnitPrices->modified}}
                        </td>
                        @if (Home::authClerkCheck())
                            <td rowspan={{count($order_sale_unit_price_detail_arr[$orderSaleUnitPrices->order_sale_unit_price_id])+1}}>
                                <!--編集ボタン--><a class='edit-btn' href='./OrderSaleUnitPriceEdit/{{$orderSaleUnitPrices->order_sale_unit_price_id}}'>編集</a>
                            </td>
                        @endif
                    </tr>
                    @foreach ($order_sale_unit_price_detail_arr[$orderSaleUnitPrices->order_sale_unit_price_id] as $order_sale_unit_price_detail_key => $order_sale_unit_price_detail_val)
                    <tr>
                        <td>
                            <!--製品コード-->{{$order_sale_unit_price_detail_val['product_code']}}
                        </td>
                        <td class="double-width" colspan="2">
                            <!--製品名-->{{$order_sale_unit_price_detail_val['product_name']}}
                        </td>
                        <td class='text-right'>
                            <!--単価-->{{number_format($order_sale_unit_price_detail_val['order_sale_unit_price_detail_price'])}}円
                        </td>
                        <td>
                            <!--単位-->{{$order_sale_unit_price_detail_val['unit_name']}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endforeach
        </div>

        <div class="d-flex justify-content-center">
            {{ $orderSaleUnitPriceList->links() }}
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
            var sale_submit_type_selected = $("#sale_submit_type_selected").val();
            // 検索条件で設定された企業を設定
            $('#sale_submit_type').val(sale_submit_type_selected);

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

                        $('#search-btn').focus();
                    }

                    return false;

                } else if (event.keyCode === 47) { // スラッシュが押された時

                    var this_id = $(this).attr('id');

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
            // フォーカスアウトしたときの処理
            //-------------------------------------
            $(document).on("blur", "input", function(event) {

                var tabindex = parseInt($(this).attr('tabindex'), 10);
                var set_val = $(this).val();
                // 全角数字を半角に変換
                set_val = set_val.replace( /[０-９]/g, function(s) {
                    return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
                });
                $(this).val(set_val);
                var selector_code = $(this).attr('id');
                var selector_id = selector_code.replace('_code', '_id');
                var selector_text = selector_code.replace('_code', '_text');

                var fd = new FormData();
                fd.append("inputText", set_val);

                if (selector_code.match(/sale_company/)) { // 売上先企業

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

    .double-width {
        width: 20%!important;
    }

    .triple-width {
        width: 30%!important;
    }

    .forth-width {
        width: 40%!important;
    }

    .width-10 {
        width: 10%!important;
    }

    .width-15 {
        width: 15%!important;
    }

    .width-20 {
        width: 20%!important;
    }

    .width-30 {
        width: 30%!important;
    }

    .edit-btn {
        border-radius: 5px;
        color: #fff;
        background-color: #72be92;
        width: 80%;
        margin: auto;
        display: block;
        text-align: center;
        padding: 10px;
    }

    .regis-complete {
        background-color: #D2F0F0;
        font-weight: bold;
        border-left: 3px solid #0099CB!important;
        text-align: center;
    }

    .regis-temp {
        background-color: #f0d2d2;
        font-weight: bold;
        border-left: 3px solid #cb0000!important;
        text-align: center;
    }

    .bold-tr {
        font-weight: bold;
    }

    .text-right {
        text-align: right;
    }
</style>
