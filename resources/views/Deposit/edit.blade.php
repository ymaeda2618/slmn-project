@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">
        <div class="top-title">入金編集画面</div>

        <form class="smn-form" id="deposit-create-form" method="post" action="./../editRegisterDeposit" enctype="multipart/form-data" onsubmit="return submitCheck();">
            {{ csrf_field() }}

            <input type="hidden" id="deposit_id" name="data[Deposit][id]" value="{{$depositDatas->deposit_id}}">
            <div class="form-group">
                <label class="column-label" for="deposit_date">入金日付</label>
                <input type="date" class="form-control " id="deposit_date" name="data[Deposit][deposit_date]" tabindex="1" value="{{$depositDatas->deposit_date}}">
            </div>

            <div class="search-area">
                <div class="sale-date-form">
                    <div class="radio_box">
                        <label><input type="radio" name="data[Deposit][search_date]" value="1"> 伝票日付</label>
                        <label><input type="radio" name="data[Deposit][search_date]" value="2"> 納品日付</label>
                    </div>
                    <input type="date" class="form-control width-45 sale_from_date" id="sale_from_date" name="data[Deposit][sale_from_date]" onchange='javascript:changeCalcFlg()' tabindex="2" value="{{$depositDatas->sale_from_date}}">
                    <p class="sale-block">〜</p>
                    <input type="date" class="form-control width-45 sale_to_date" id="sale_to_date" name="data[Deposit][sale_to_date]" onchange='javascript:changeCalcFlg()' tabindex="3" value="{{$depositDatas->sale_to_date}}">
                </div>

                <table class="deposit-from-table">
                    <tr>
                        <th colspan="2" class="sale-label">売上企業</th>
                        <th colspan="2" class="sale-label">売上店舗</th>
                    </tr>
                    <tr>
                        <td class="width-20">
                            <input type="text" class="form-control deposit_company_code_input" id="deposit_company_code" name="data[Deposit][deposit_company_code]" onchange='javascript:changeCalcFlg()' tabindex="4" value="{{$depositDatas->sale_company_code}}">
                            <input type="hidden" id="deposit_company_id" name="data[Deposit][deposit_company_id]" value="{{$depositDatas->sale_company_id}}">
                        </td>
                        <td class="width-30">
                            <input type="text" class="form-control" id="deposit_company_text" name="data[Deposit][deposit_company_text]" value="{{$depositDatas->sale_company_name}}" readonly>
                        </td>
                        <td class="width-20">
                            <input type="text" class="form-control deposit_shop_code_input" id="deposit_shop_code" name="data[Deposit][deposit_shop_code]" value="{{$depositDatas->sale_shop_code}}" tabindex="5">
                            <input type="hidden" id="deposit_shop_id" name="data[Deposit][deposit_shop_id]" value="{{$depositDatas->sale_shop_id}}">
                        </td>
                        <td class="width-30">
                            <input type="text" class="form-control" id="deposit_shop_text" name="data[Deposit][deposit_shop_text]" value="{{$depositDatas->sale_shop_name}}" readonly>
                        </td>
                    </tr>

                </table>
                <button id="search-btn" class="search-btn btn btn-primary" type="button" onclick='javascript:searchSaleSlips()'>抽出</button>
                <button id="calc-btn" class="calc-btn btn btn-primary" type="button" onclick="javascript:calcSalePrice()">計算</button>
                <input type="hidden" id="calc-flg" value="0">
            </div>

            {{--  抽出結果表示欄 開始  --}}
            <div id="result-area" class="result-area">
                <table class="result-table" onchange="javascript:changeCalcFlg()">
                    <tr>
                        <th class="width-5">選択</th>
                        <th>伝票日付</th>
                        <th>8%税抜合計</th>
                        <th>外税8%</th>
                        <th>10%税抜合計</th>
                        <th>外税10%</th>
                        <th>小計</th>
                        <th>配送額</th>
                        <th>調整額</th>
                        <th>調整後金額</th>
                        <th class="width-5">明細</th>
                    </tr>
                    @foreach ($saleSlipDatas as $saleSlipData)
                    <?php
                    // 金額計算
                    $notaxSubTotal8  = $saleSlipData->notax_sub_total_8;                     // 税抜8%金額
                    $tax8            = round($saleSlipData->notax_sub_total_8 * 8 / 100);    // 8%消費税
                    $notaxSubTotal10 = $saleSlipData->notax_sub_total_10;                    // 税抜10%金額
                    $tax10           = round($saleSlipData->notax_sub_total_10 * 10 / 100);  // 8%消費税
                    $subTotal        = $notaxSubTotal8 + $tax8 + $notaxSubTotal10 + $tax10;  // 小計
                    $delivery_price  = $saleSlipData->delivery_price;                        // 配送額
                    $adjust_price    = $saleSlipData->adjust_price;                          // 調整額
                    $total           = $subTotal + $delivery_price + $adjust_price;          // 調整後総合計額
                    $saleFlg         = $saleSlipData->sale_flg;                              // 入金フラグ

                        $checked = '';
                        foreach ($depositDetailDatas as $depositDetailData) {
                            if ($depositDetailData->sale_slip_id == $saleSlipData->id) {
                                $checked = $saleFlg == 1 ? 'checked' : '';
                            }
                        }

                    ?>
                    <tr>
                        <td><input type="checkbox" id="sale-slip-id-{{$saleSlipData->id}}" name="data[DepositDetail][{{$saleSlipData->id}}][id]" value="{{$saleSlipData->id}}" <?php echo $checked; ?> onchange="javascript:discardSaleSlipId({{$saleSlipData->id}})"></td>
                        <td>{{$saleSlipData->date}}
                            <input type="hidden" name="data[DepositDetail][{{$saleSlipData->id}}][date]" value="{{$saleSlipData->date}}">
                        </td>
                        <td><?php echo number_format($notaxSubTotal8); ?>
                            <input type="hidden" id="sale-slip-subTotal8-{{$saleSlipData->id}}" name="data[DepositDetail][{{$saleSlipData->id}}][notax_subTotal_8]" value="{{$notaxSubTotal8}}">
                        </td>
                        <td><?php echo number_format($tax8); ?>
                            <input type="hidden" name="data[DepositDetail][{{$saleSlipData->id}}][tax8]" value="{{$tax8}}">
                        </td>
                        <td><?php echo number_format($notaxSubTotal10); ?>
                            <input type="hidden" id="sale-slip-subTotal10-{{$saleSlipData->id}}"name="data[DepositDetail][{{$saleSlipData->id}}][notax_subTotal_10]" value="{{$notaxSubTotal10}}">
                        </td>
                        <td><?php echo number_format($tax10); ?>
                            <input type="hidden" name="data[DepositDetail][{{$saleSlipData->id}}][tax10]" value="{{$tax10}}">
                        </td>
                        <td><?php echo number_format($subTotal); ?>
                            <input type="hidden" name="data[DepositDetail][{{$saleSlipData->id}}][subTotal]" value="{{$subTotal}}">
                        </td>
                        <td><?php echo number_format($delivery_price); ?>
                            <input type="hidden" id="sale-slip-deliveryPrice-{{$saleSlipData->id}}" name="data[DepositDetail][{{$saleSlipData->id}}][delivery_price]" value="{{$delivery_price}}">
                        </td>
                        <td><?php echo number_format($adjust_price); ?>
                            <input type="hidden" id="sale-slip-adjustPrice-{{$saleSlipData->id}}" name="data[DepositDetail][{{$saleSlipData->id}}][adjust_price]" value="{{$adjust_price}}">
                        </td>
                        <td id="sale-slip-total{{$saleSlipData->id}}" data-value="{{$total}}"><?php echo number_format($total); ?>
                            <input type="hidden" name="data[DepositDetail][{{$saleSlipData->id}}][total]" value="{{$total}}">
                        </td>
                        <td>
                            <a href="./../SaleSlipEdit/{{$saleSlipData->id}}">明細</a>
                        </td>
                    </tr>
                    @endforeach
                </table>
                <div id="sale-slip-area"></div>
            </div>
            {{--  抽出結果表示欄 終了  --}}

            <table class="deposit-table">
                <tr>
                    <th class="width-5">担当者</th>
                    <td>
                        <input type="text" class="form-control staff_code_input" id="staff_code" name="data[Deposit][staff_code]" value="{{$depositDatas->staff_code}}" tabindex="6">
                        <input type="hidden" id="staff_id" name="data[Deposit][staff_id]" value="{{$depositDatas->staff_id}}">
                    </td>
                    <td class="width-30">
                        <input type="text" class="form-control" id="staff_text" name="data[Deposit][staff_text]" value="{{$depositDatas->staff_name}}" readonly>
                    </td>
                </tr>
                <tr>
                    <th>支払金額</th>
                    <td class="width-20">
                        <input type="number" class="form-control" id="price" name="data[Deposit][price]" onchange='javascript:calcTotalSalePrice()' value="{{$depositDatas->sub_total}}" readonly>
                    </td>
                </tr>
                <tr>
                    <th>調整金額</th>
                    <td class="width-20">
                        <input type="number" class="form-control" id="adjustment_price" name="data[Deposit][adjustment_price]" onchange='javascript:calcTotalSalePrice()' value="{{$depositDatas->adjustment_amount}}" tabindex="7">
                    </td>
                </tr>
                <tr>
                    <th>支払手段</th>
                    <td>
                        <select class="form-control" name="data[Deposit][deposit_method_id]" tabindex="8">
                            <option value="1" @php $depositDatas->deposit_method_id == 1 ? print 'selected' : '' @endphp >東信当座</option>
                            <option value="2" @php $depositDatas->deposit_method_id == 2 ? print 'selected' : '' @endphp >東信普通</option>
                            <option value="3" @php $depositDatas->deposit_method_id == 3 ? print 'selected' : '' @endphp >みずほ</option>
                            <option value="4" @php $depositDatas->deposit_method_id == 4 ? print 'selected' : '' @endphp >みずほ個人</option>
                            <option value="5" @php $depositDatas->deposit_method_id == 5 ? print 'selected' : '' @endphp >UFJ個人</option>
                            <option value="6" @php $depositDatas->deposit_method_id == 6 ? print 'selected' : '' @endphp >現金</option>
                            <option value="7" @php $depositDatas->deposit_method_id == 7 ? print 'selected' : '' @endphp >小切手</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>メモ</th>
                    <td colspan="5">
                        <textarea class="form-control" id="memo" name="data[Deposit][memo]" row="5" tabindex="9" style="margin-top: 0px; margin-bottom: 0px; height: 150px;">@php print $depositDatas->remarks @endphp</textarea>
                    </td>
                </tr>
            </table>

            <table class="total-table">
                <tr>
                    <th>8%対象額</th>
                    <th>8%税額</th>
                    <th>8%税込額</th>
                </tr>
                <tr>
                    <td><input type="tel" class="form-control" id="notax_sub_total_8" value='0' readonly></td>
                    <td><input type="tel" class="form-control" id="tax_total_8" value='0' readonly></td>
                    <td><input type="tel" class="form-control" id="sub_total_8" value='0' readonly></td>
                </tr>
                <tr>
                    <th>10%対象額</th>
                    <th>10%税額</th>
                    <th>10%税込額</th>
                </tr>
                <tr>
                    <td><input type="tel" class="form-control" id="notax_sub_total_10" value='0' readonly></td>
                    <td><input type="tel" class="form-control" id="tax_total_10" value='0' readonly></td>
                    <td><input type="tel" class="form-control" id="sub_total_10" value='0' readonly></td>
                </tr>
                <tr>
                    <th>税抜小計</th>
                    <th>税額</th>
                    <th>税込小計</th>
                </tr>
                <tr>
                    <td><input type="tel" class="form-control" id="notax_sub_total" value='0' readonly></td>
                    <td><input type="tel" class="form-control" id="tax_total" value='0' readonly></td>
                    <td><input type="tel" class="form-control" id="sub_total" value='0' readonly></td>
                </tr>
                <tr>
                    <th>配送額総計</th>
                    <th>伝票調整額総計</th>
                    <th>調整額</th>
                </tr>
                <tr>
                    <td><input type="text" class="form-control" id="delivery_total_price" value='0' readonly></td>
                    <td><input type="text" class="form-control" id="slip_adjust_total_price" value='0' readonly></td>
                    <td><input type="text" class="form-control" id="adjust_total_price" value='0' readonly></td>
                </tr>
                <tr>
                    <th colspan="2">最終支払額</th>
                </tr>
                <tr>
                    <td colspan="2"><input type="number" class="form-control" id="total_price" name="data[Deposit][total_price]" value="{{$depositDatas->amount}}" readonly></td>
                </tr>
            </table>

            <table class="register-btn-table">
                <tr>
                    <td class="width-50">
                        <input type="submit" id="register-btn" class="register-btn btn btn-primary" value="請求登録" tabindex="9">
                    </td>
                </tr>
            </table>

        </form>
    </div>

</div>
@endsection
<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript">

var notax_sub_total_8;
var tax_total_8;
var sub_total_8;

var notax_sub_total_10;
var tax_total_10;
var sub_total_10;

var notax_sub_total;
var tax_total;
var sub_total;

var adjust_price;
var total;

(function($) {
    jQuery(window).load(function() {

        // 一番最初は売上企業にフォーカスする
        $('#sale_company_code').focus();

        // 初期化処理
        notax_sub_total_8 = 0;
        tax_total_8 = 0;
        sub_total_8 = 0;

        notax_sub_total_10 = 0;
        tax_total_10 = 0;
        sub_total_10 = 0;

        notax_sub_total = 0;
        tax_total = 0;
        sub_total = 0;

        adjust_price = 0;
        total = 0;

        //-------------------------------------
        // Enterと-を押したときにタブ移動する処理
        //-------------------------------------
        $(document).on("keypress", "input", function(event) {

            if (event.keyCode === 13) { // Enterが押された時

                var this_id = $(this).attr('id');

                // 現在のtabIndex取得
                var tabindex = parseInt($(this).attr('tabindex'), 10);
                if (isNaN(tabindex)) return false;

                // ひとつ前のタブの最小値を取得
                var min = 0;
                $("#deposit-create-form [tabindex]").attr("tabindex", function(a, b) {

                    b = parseInt(b, 10);
                    if (tabindex < b) {
                        if (min == 0) min = b;
                        else if (min > b) min = b;
                    }
                });

                tabindex = min;

                if ($('input[tabindex="' + tabindex + '"]').length) {

                    var this_val = $('input[tabindex="' + tabindex + '"]').val();
                    $('input[tabindex="' + tabindex + '"]').val("");
                    $('input[tabindex="' + tabindex + '"]').focus();
                    $('input[tabindex="' + tabindex + '"]').val(this_val);

                } else {

                    var this_val = $('#delivery_code').val();
                    $('#delivery_code').val("");
                    $('#delivery_code').focus();
                    $('#delivery_code').val(this_val);
                }

                return false;

            } else if (event.keyCode === 47) { // スラッシュが押された時

                var this_id = $(this).attr('id');

                // 現在のtabIndex取得
                var tabindex = parseInt($(this).attr('tabindex'), 10);
                if (isNaN(tabindex)) return false;

                // ひとつ前のタブの最大値を取得
                var max = 0;
                $("#sale-slip-create-form [tabindex]").attr("tabindex", function(a, b) {

                    b = parseInt(b, 10);
                    if (tabindex > b) {
                        if (max == 0) max = b;
                        else if (max < b) {
                            max = b;
                        }
                    }
                });

                tabindex = max;

                if ($('input[tabindex="' + tabindex + '"]').length) {
                    var this_val = $('input[tabindex="' + tabindex + '"]').val();
                    $('input[tabindex="' + tabindex + '"]').val("");
                    $('input[tabindex="' + tabindex + '"]').focus();
                    $('input[tabindex="' + tabindex + '"]').val(this_val);
                }

                return false;

            }

        });

        //-------------------------------------
        // フォーカスアウトしたときの処理
        //-------------------------------------
        $(document).on("blur", 'input[name!="data[Deposit][search_date]"]', function(event) {

            var tabindex = parseInt($(this).attr('tabindex'), 10);
            var set_val = $(this).val();
            var selector_code = $(this).attr('id');
            var selector_id = selector_code.replace('_code', '_id');
            var selector_text = selector_code.replace('_code', '_text');

            var fd = new FormData();
            fd.append("inputText", set_val);

            if (selector_code.match(/deposit_company/)) { // 売上企業

                $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./../AjaxSetSaleCompany",
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

            } else if (selector_code.match(/deposit_shop/)) { // 売上店舗

                $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./../AjaxSetSaleShop",
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

            } else if (selector_code.match(/staff/)) { // 担当者IDの部分

                $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./../AjaxSetStaff",
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

        //-------------------------------------
        // autocomplete処理 売上企業ID
        //-------------------------------------
        $(".deposit_company_code_input").autocomplete({
            source: function(req, resp) {
                $.ajax({
                    headers: {
                        "X-CSRF-TOKEN": $("[name='_token']").val()
                    },
                    url: "./../AjaxAutoCompleteSaleCompany",
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
        $(".deposit_shop_code_input").autocomplete({
            source: function(req, resp) {
                $.ajax({
                    headers: {
                        "X-CSRF-TOKEN": $("[name='_token']").val()
                    },
                    url: "./../AjaxAutoCompleteSaleShop",
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
                    url: "./../AjaxAutoCompleteStaff",
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
        // Enterと-を押したときにタブ移動する処理
        //-------------------------------------
        $(document).on("click", ".register-btn", function() {

            var this_val = $("#sale_submit_type").val();

            if (this_val == "1") {
                $('#sale-slip-create-form').submit();
            } else if (this_id == "2") {
                $('#sale-slip-create-form').submit();
            } else {
                return false;
            }
        });
    });
    $(function(){
        $('.result-table input:checked').each(function(){
            // idを取得
            var id = $(this).val();

            discardSaleSlipId(id);
        });

        calcSalePrice();
    });
})(jQuery);

// -----------
// 売上金額計算
// -----------
function searchSaleSlips() {

    // 入力値取得
    var sale_from_date = $('#sale_from_date').val();
    var sale_to_date   = $('#sale_to_date').val();
    var sale_company   = $('#deposit_company_id').val();
    var search_date_val = $('input:radio[name="data[Deposit][search_date]"]:checked').val();
    var action = 'edit';

    if (!search_date_val) {
        alert('抽出日付を選択してください。');
        return;
    }

    if (sale_from_date == '' && sale_to_date == '') {
        alert('日付を入力してください。');
        return;
    }

    if (sale_company == '') {
        alert('売上企業を入力してください。');
        return;
    }

    var fd = new FormData();
    fd.append("sales_from_date", sale_from_date);
    fd.append("sales_to_date", sale_to_date);
    fd.append("sales_company", sale_company);
    fd.append("search_date_val", search_date_val);
    fd.append("action", action);

    // ajaxで対象範囲の売上金額を計算して持ってくる
    $.ajax({
        headers: {
            "X-CSRF-TOKEN": $("[name='_token']").val()
        },
        url: "./../AjaxSearchSaleSlips",
        type: "POST",
        dataType: "JSON",
        data: fd,
        processData: false,
        contentType: false
    })
    .done(function(data) {

        if (data != '') {
            $(".result-table").html(data[0]);
        } else {
            alert("抽出対象が存在しません。");
        }

    })
    .fail(function(XMLHttpRequest, textStatus, errorThrown) {
        alert(XMLHttpRequest);
        alert(textStatus);
        alert(errorThrown);
        // 送信失敗
        alert("失敗しました。");
    });

}

// ------------------------
// 選択された値の支払金額の計算
// ------------------------
function calcSalePrice() {

    if(!$('.result-table').length) {
        alert("抽出してから計算ボタンを押してください。");
        return;
    }

    var total           = 0;
    var notaxSubTotal8  = 0;
    var subTotal8       = 0;
    var notaxSubTotal10 = 0;
    var subTotal10      = 0;
    var deliveryPrice   = 0;
    var adjustPrice     = 0;
    var tax8            = 0;
    var tax10           = 0;

    // チェックボックスの値を取得して計算
    $('.result-table input:checked').each(function(){

        // idを取得
        var id = $(this).val();

        // 8%税抜額
        notaxSubTotal8 += parseInt($('#sale-slip-subTotal8-' + id).val());

        // 10%税抜額
        notaxSubTotal10 += parseInt($('#sale-slip-subTotal10-' + id).val());

        // 配送額
        deliveryPrice += parseInt($('#sale-slip-deliveryPrice-' + id).val());

        // 調整額
        adjustPrice += parseInt($('#sale-slip-adjustPrice-' + id).val());

        discardSaleSlipId(id)

    });

    // 8%税込額
    subTotal8 = Math.round(notaxSubTotal8 * 1.08);

    // 8%消費税額
    tax8 = parseInt(subTotal8) - parseInt(notaxSubTotal8);

    // 10%税込額
    subTotal10 = Math.round(notaxSubTotal10 * 1.1);

    // 10%消費税額
    tax10 = parseInt(subTotal10) - parseInt(notaxSubTotal10);

    // 合計金額計算
    total = parseInt(subTotal8) + parseInt(subTotal10) + parseInt(deliveryPrice) + parseInt(adjustPrice);

    // ------------
    // 計算結果を表示
    // ------------
    // 8%税抜額
    $('#notax_sub_total_8').val(parseInt(notaxSubTotal8));

    // 8%消費税額
    $('#tax_total_8').val(parseInt(tax8));

    // 8%税込額
    $('#sub_total_8').val(parseInt(subTotal8));

    // 10%税抜額
    $('#notax_sub_total_10').val(parseInt(notaxSubTotal10));

    // 10%消費税額
    $('#tax_total_10').val(parseInt(tax10));

    // 10%税込額
    $('#sub_total_10').val(parseInt(subTotal10));

    // 税抜小計
    $('#notax_sub_total').val(parseInt(notaxSubTotal8 + notaxSubTotal10));

    // 税額
    $('#tax_total').val(parseInt(tax8 + tax10));

    // 税込小計
    $('#sub_total').val(parseInt(subTotal8 + subTotal10));

    // 配送額総計
    $('#delivery_total_price').val(parseInt(deliveryPrice));

    // 伝票調整額総計
    $('#slip_adjust_total_price').val(parseInt(adjustPrice));

    // 調整額含む前の計算結果
    $('#price').val(parseInt(total));

    calcTotalSalePrice();
}

// --------------
// 総合計金額の計算
// --------------
function calcTotalSalePrice() {

    // 入力値取得
    var adjust_price = $('#adjustment_price').val();
    if (adjust_price == '') adjust_price = 0;
    $('#adjust_total_price').val(parseInt(adjust_price));

    // 支払金額に値があるか確認なければ何もしない
    var sale_price = $('#price').val();
    if (sale_price == '') {
        return;
    }

    // -------------------------------------
    // 支払金額が存在していれば調整金額も含めて計算
    // -------------------------------------

    // 総合計計算
    var total_sale_price = 0;
    total_sale_price = parseInt(sale_price) + parseInt(adjust_price);

    $("#total_price").val(total_sale_price);

    // 計算フラグを更新する
    $("#calc-flg").val(1);

}

// --------------
// 登録時のチェック
// --------------
function submitCheck() {

    // ---------
    // 変数初期化
    // ---------
    var deposit_date   = '';
    var sale_from_date = '';
    var sale_to_date   = '';
    var sale_company   = '';
    var sale_shop      = '';
    var staff_code     = '';
    var total_price    = '';

    // ----------
    // 入力チェック
    // ----------
    deposit_date = $("#deposit_date").val();
    if (deposit_date == '') {
        alert('入金日付を入力してください');
        return false;
    }

    sale_from_date = $("#sale_from_date").val();
    sale_to_date = $("#sale_to_date").val();
    if (sale_from_date == '' || sale_to_date == '') {
        alert('伝票日付を入力してください');
        return false;
    }

    sale_company = $("#deposit_company_code").val();
    if (sale_company == '') {
        alert('売上企業を入力してください');
        return false;
    }

    staff_code = $("#staff_code").val();
    if (staff_code == '') {
        alert('担当者を入力してください');
        return false;
    }

    total_price = $("#total_price").val();
    if (total_price == '') {
        alert('調整後入金金額が空白です');
        return false;
    }

    // 支払日付を変更した場合に、「抽出」ボタンを押して支払金額の計算をしたか確認する
    var calc_flg = $("#calc-flg").val();
    if (calc_flg == '0') {
        alert('売上伝票のチェックボックスが変更されています。計算ボタンを押してください。');
        return false;
    }

}

// --------------
// 計算フラグの変更
// --------------
function changeCalcFlg() {

    $("#calc-flg").val(0);

}

// -------------------
// sale_slip_idの取得
// -------------------
function discardSaleSlipId(id) {

    // 対象IDのチェック状態を取得
    var isCheck = $('#sale-slip-id-' + id).prop('checked');

    if (isCheck) {
        if (!($('#sale-slip-detail-id-' + id).length)) {
            // チェックしたら詳細エリアにIDを追加
            $('#sale-slip-area').append('<input type="hidden" id="sale-slip-detail-id-' + id + '" name="data[DepositDetail][sale_slip_ids][]" value="' + id + '">');
        }
    } else {
        if ($('#sale-slip-detail-id-' + id).length) {
            // チェック外れたらIDを詳細エリアから外す
            $('#sale-slip-detail-id-' + id).remove();
        }
    }
}

</script>
<style>
    /* 共通 */

    .top-title {
        font-size: 1.4em;
        font-weight: bold;
        width: 100%;
        text-align: center;
        padding: 25px 0px;
    }

    .smn-form {
        max-width: 1300px;
        width: 90%;
        margin: auto;
    }

    .form-group {
        margin-bottom: 2rem !important;
    }

    .file-control {
        width: 100%;
        height: calc(1.6em + 0.75rem + 2px);
        padding: 0.375rem 0.75rem;
    }

    .column-label {
        display: block;
        width: 100%;
        font-size: 0.9em;
        font-weight: bold;
    }

    .sale-label {
        font-size: 0.9em;
        font-weight: bold;
    }

    .deposit-from-table {
        width: 100%;
    }

    .deposit-table {
        width: 100%;
        margin-top: 4%;
    }

    .width-5 {
        width: 5%!important;
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

    .width-40 {
        width: 40%!important;
    }

    .width-45 {
        width: 45%!important;
    }

    .slip-table {
        width: 100%;
    }

    .partition-area {
        width: 100%;
        height: 1.0em;
    }

    .remove-slip-btn {
        height: calc(4.2rem + 7px)!important;
        width: 100%;
    }

    .sale-date-form {
        width: 100%;
        margin-bottom: 2%;
    }

    .sale_from_date {
        float: left;
        display: block;
    }

    .sale-block {
        float: left;
        width: 10%;
        text-align: center;
    }

    .result-area {
        display: block;
        width: 100%;
        height: 30vh;
        margin-top: 2%;
        overflow: auto;
    }

    .result-table {
        border-collapse: collapse;
        margin: auto;
        padding: 0;
        width: 100%;
        table-layout: fixed;
    }

    .result-table th {
        padding: 1% 0;
        border-right: 1px solid #bbb;
        text-align: center;
    }

    .result-table tr {
        padding: 1%;
        border-bottom: 1px solid #bbb;
    }

    .result-table tr:last-child{
        border-bottom: none
    }

    .result-table td {
        padding: 0.5%;
        border-right: 1px solid #bbb;
        text-align: center;
    }

    .result-table th:last-child,
        .result-table td:last-child{
        border: none;
    }

    .calc-btn {
        margin: 1%;
        background-color: #FF570D!important;
        border-color: #FF570D!important;
    }

    .total-table {
        width: 100%;
        margin-top: 4%;
    }

    .register-btn-table {
        width: 100%;
        text-align: center;
        margin-top: 4%;
    }

    .register-btn {
        width: 40%;
    }

</style>