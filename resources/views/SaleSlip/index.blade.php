@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">売上一覧</div>

        <form id="index-search-form" method="post" action='{{$search_action}}' enctype="multipart/form-data">
            <!--検索エリア-->
            <div class='search-area'>

                {{ csrf_field() }}
                <table>
                    <tbody>
                        <tr>
                            <td>
                                <div class="table-th">対象日付</div>
                                <div class="table-td radio_box">
                                    <label class="radio-label"><input type="radio" name="data[SaleSlip][date_type]" value="1" {{$check_str_slip_date}}> 伝票日付</label>
                                    <label class="radio-label"><input type="radio" name="data[SaleSlip][date_type]" value="2" {{$check_str_deliver_date}}> 納品日付</label>
                                </div>
                                <div class="table-td">
                                    <input type="date" class="search-control" id="sale_date_from" name="data[SaleSlip][sale_date_from]" value="{{$condition_date_from}}" tabindex="1">
                                </div>
                                <div class="table-td">
                                    <input type="date" class="search-control" id="sale_date_to" name="data[SaleSlip][sale_date_to]" value="{{$condition_date_to}}" tabindex="2">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="table-th">取引先企業</div>
                                <div class="table-td table-code-td">
                                    <input type="text" class="search-control sale_company_code_input" id="sale_company_code" name="data[SaleSlip][sale_company_code]" value="{{$condition_company_code}}" tabindex="3">
                                    <input type="hidden" id="sale_company_id" name="data[SaleSlip][sale_company_id]" value="{{$condition_company_id}}">
                                </div>
                                <div class="table-td table-name-td">
                                    <input type="text" class="search-control" id="sale_company_text" name="data[SaleSlip][sale_company_text]" value="{{$condition_company_text}}" readonly>
                                </div>
                                <div class="table-th">支払方法</div>
                                <div class="table-td table-code-td">
                                    <input type="text" class="search-control" id="payment_method_type" name="data[SaleSlip][payment_method_type]" value="{{$condition_payment_method_type}}" tabindex="4">
                                </div>
                                <div class="table-td table-name-td">
                                    <input type="text" class="search-control read-only" id="payment_method_type_text" name="data[SaleSlip][payment_method_type_text]" value="{{$condition_payment_method_type_text}}" readonly>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="table-th">仕入製品</div>
                                <div class="table-td table-code-td">
                                    <input type="text" class="search-control product_code_input" id="product_code" name="data[SaleSlipDetail][product_code]" value="{{$condition_product_code}}" tabindex="5">
                                    <input type="hidden" id="product_id" name="data[SaleSlipDetail][product_id]" value="{{$condition_product_id}}">
                                </div>
                                <div class="table-td table-name-td">
                                    <input type="text" class="search-control" id="product_text" name="data[SaleSlipDetail][product_text]" value="{{$condition_product_text}}" readonly>
                                </div>
                                <div class="table-th">担当者</div>
                                <div class="table-td table-code-td">
                                    <input type="text" class="search-control staff_code_input" id="staff_code" name="data[SaleSlipDetail][staff_code]" value="{{$condition_staff_code}}" tabindex="5">
                                    <input type="hidden" id="staff_id" name="data[SaleSlipDetail][staff_id]" value="{{$condition_staff_id}}">
                                </div>
                                <div class="table-td table-name-td">
                                    <input type="text" class="search-control" id="staff_text" name="data[SaleSlipDetail][staff_text]" value="{{$condition_staff_text}}" readonly>
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
            </div>

            <!--総計表示エリア-->
            <div class='sum-display-area'>
                @if(true)
                <div class='sum-display-div'>伝票件数:{{number_format($sale_slip_num)}}件</div>
                <div class='sum-display-div'>総配送額:{{number_format($delivery_price_amount)}}円</div>
                <div class='sum-display-div'>総調整額:{{number_format($adjust_price_amount)}}円</div>
                <div class='sum-display-div'>総税抜額:{{number_format($notax_sub_total_amount)}}円</div>
                <div class='sum-display-div'>税込総額:{{number_format($sale_slip_tax_amount)}}円</div>
                @else
                <div class='sum-display-div'>伝票件数:{{number_format($sale_slip_condition_num)}}件</div>
                <div class='sum-display-div'>総税抜額:{{number_format($sale_slip_condition_notax_sub_total)}}円</div>
                @endif
                <div class='display-condition-div'>並び順
                    <select id='display-sort' name="display_sort" class='display-condition-select'>
                    <option value="0">伝票日付:降順</option>
                    <option value="1">伝票日付:昇順</option>
                    <option value="2">納品日付:降順</option>
                    <option value="3">納品日付:昇順</option>
                </select>
                </div>
                <div class='display-condition-div'>表示件数
                    <select id='display-num' name="display_num" class='display-condition-select'>
                    <option value="20">20件</option>
                    <option value="40">40件</option>
                    <option value="60">60件</option>
                    <option value="80">80件</option>
                    <option value="100">100件</option>
                </select>
                </div>
                <input type='hidden' id='display_sort_selected' value='{{$condition_display_sort}}'>
                <input type='hidden' id='display_num_selected' value='{{$condition_display_num}}'>
                <div class="float-clear"></div>
            </div>
            <div class='no-display-area'>
                <input type="checkbox" id="no_display" name="no_display" value="1">
                <label for="no_display">infomartのデータを表示しない</label>
                <input type='hidden' id='no_display_selected' value='{{$condition_no_display}}'>
            </div>
        </form>

        <!--一覧表示エリア-->
        <div class='list-area'>
            <table class='index-table'>
                <tbody>
                    <tr>
                        <th rowspan="2 ">種別.</th>
                        <th>伝票No.</th>
                        <th>伝票日付</th>
                        <th>取引先コード</th>
                        <th class="forth-width " colspan="4 ">取引先名</th>
                        <th class="double-width " colspan="2 " rowspan="2 "></th>
                    </tr>
                    <tr>
                        <th>納品日</th>
                        <th class="double-width " colspan="2 ">登録日</th>
                        <th class="double-width " colspan="2 ">配送費+調整額</th>
                        <th class="double-width " colspan="2 ">税抜商品合計</th>
                    </tr>
                </tbody>
            </table>

            @foreach ($saleSlipList as $saleSlips)
            <table class='index-table'>
                <tbody>
                    <tr>
                        <!--種別-->
                        @if ($saleSlips->sale_submit_type == 1 || $saleSlips->sale_submit_type == 4)
                        <td class="regis-complete" rowspan="<?php echo ($sale_slip_detail_count_arr[$saleSlips->sale_slip_id] + 2); ?>">登録済</td>
                        @else
                        <td class="regis-temp" rowspan="<?php echo ($sale_slip_detail_count_arr[$saleSlips->sale_slip_id] + 2); ?>">一時保存</td>
                        @endif
                        <td>
                            <!--伝票NO-->{{$saleSlips->sale_slip_id}}
                        </td>
                        <td>
                            <!--伝票日付-->{{$saleSlips->sale_slip_date}}
                        </td>
                        <td>
                            <!--取引先コード-->{{$saleSlips->sale_company_code}}
                        </td>
                        <td class="forth-width bold-tr" colspan="4">
                            <!--取引先名-->{{$saleSlips->sale_company_name}}
                        </td>
                        @if (Home::authClerkCheck())
                        <td class="double-width" colspan="2">
                            <!--編集ボタン--><a class='edit-btn' target="_blank" href='./SaleSlipEdit/{{$saleSlips->sale_slip_id}}'>編集</a>
                        </td>
                        @endif
                    </tr>
                    <tr>
                        <td class="bold-tr">
                            <!--納品日-->{{$saleSlips->sale_slip_delivery_date}}
                        </td>
                        <td class="double-width" colspan="2">
                            <!--登録日付-->{{$saleSlips->sale_slip_modified}}
                        </td>
                        <td class="double-width" colspan="2">
                            <!--調整額-->{{number_format($saleSlips->delivery_price + $saleSlips->adjust_price)}}
                        </td>
                        <td class="double-width" colspan="2">
                            <!--総合計-->{{number_format($saleSlips->notax_sub_total)}}
                        </td>
                        @if (Home::authClerkCheck())
                        <td class="double-width" colspan="2">
                            <!--納品書ボタン--><a class='delivery-slip-btn' target="_blank" href='./deliverySlipOutput/{{$saleSlips->sale_slip_id}}'>納品書</a>
                        </td>
                        @endif
                    </tr>
                    @foreach ($sale_slip_detail_arr[$saleSlips->sale_slip_id] as $sale_slip_detail_key => $sale_slip_detail_val)
                    <tr>
                        <td>
                            <!--製品コード-->{{$sale_slip_detail_val['product_code']}}
                        </td>
                        <td class="double-width" colspan="2">
                            <!--製品名-->{{$sale_slip_detail_val['product_name']}}
                        </td>
                        <td class="double-width" colspan="2">
                            <!--摘要-->{{$sale_slip_detail_val['memo']}}
                        </td>
                        <td>
                            <!--担当者名-->{{$sale_slip_detail_val['staff_name']}}
                        </td>
                        <td>
                            <!--単価-->{{number_format($sale_slip_detail_val['sale_slip_detail_unit_price'])}}
                        </td>
                        <td>
                            <!--数量-->{{$sale_slip_detail_val['sale_slip_detail_unit_num']}}
                        </td>
                        <td>
                            <!--単位-->{{$sale_slip_detail_val['unit_name']}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endforeach
        </div>

        <div class="d-flex justify-content-center">
            {{ $saleSlipList->links() }}
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
            // 表示条件のセレクトボックスに値を入れる
            //-------------------------------------

            // ソート条件を取得
            var display_sort_selected = $("#display_sort_selected").val();
            // ソート条件を設定
            $('#display-sort').val(display_sort_selected);

            // 表示件数を取得
            var display_num_selected = $("#display_num_selected").val();
            // 表示件数を設定
            $('#display-num').val(display_num_selected);

            // infomart表示非表示条件を取得
            var display_num_selected = $("#no_display_selected").val();
            // infomart表示非表示条件を設定
            if (display_num_selected == 1) {
                $('#no_display').prop("checked", true);
            } else {
                $('#no_display').prop("checked", false);
            }


            //-------------------------------------
            // 並び順が変更された時
            //-------------------------------------
            $('#display-sort').change(function() {
                $('#search-btn').click();
            });

            //-------------------------------------
            // 表示件数が変更された時
            //-------------------------------------
            $('#display-num').change(function() {
                $('#search-btn').click();
            });

            //-------------------------------------
            // infomart非表示チェックボックスが変更された時
            //-------------------------------------
            $('#no_display').change(function() {
                $('#search-btn').click();
            });

            //-------------------------------------
            // Enterと-を押したときにタブ移動する処理
            //-------------------------------------
            $(document).on("keydown", "input", function(event) {

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

                } else if (selector_code.match(/payment_method_type/)) { // 支払方法
                    $.ajax({
                            headers: {
                                "X-CSRF-TOKEN": $("[name='_token']").val()
                            },
                            url: "./AjaxSetPaymentMethodType",
                            type: "POST",
                            dataType: "JSON",
                            data: fd,
                            processData: false,
                            contentType: false
                        })
                        .done(function(data) {

                            $("#payment_method_type_text").val(data[0]);
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
                        .done(function(data) {

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
    
    .float-clear {
        clear: both;
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
    
    #index-search-form {
        width: 100%;
        margin-bottom: auto;
    }
    
    .search-area {
        max-width: 1300px;
        width: 90%;
        padding: 10px 0px 0px;
        border: 1px solid #bcbcbc;
        border-radius: 5px;
        margin: auto;
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
        margin: auto;
    }
    
    .sum-display-div {
        float: left;
        margin-right: 1rem;
        font-weight: bold;
        font-size: 14px;
    }
    
    .display-condition-div {
        float: right;
        margin-right: 1rem;
        font-weight: bold;
        font-size: 12px;
        padding: 2px;
    }
    
    .display-condition-select {
        font-size: 10px;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    }
    
    .no-display-area {
        max-width: 1300px;
        width: 90%;
        padding-top: 10px;
        padding-left: 20px;
        margin: auto;
        font-weight: bold;
        font-size: 14px;
    }
    
    #no_display {
        position: relative;
        top: 2px
    }
    
    .no-display-area label {
        margin-left: 10px;
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
    
    .edit-btn,
    .delivery-slip-btn {
        border-radius: 5px;
        color: #fff;
        background-color: #72be92;
        width: 80%;
        margin: auto;
        display: block;
        text-align: center;
        padding: 5px;
    }
    
    .delivery-slip-btn {
        background-color: #e3342fa6!important;
        border: #e3342fa6!important;
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
</style>