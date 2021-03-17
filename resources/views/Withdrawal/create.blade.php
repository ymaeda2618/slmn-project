@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">
        <div class="top-title">支払登録画面</div>

        <form class="smn-form" id="withdrawal-create-form" method="post" action="./registerWithdrawal" enctype="multipart/form-data" onsubmit="return submitCheck();">
            {{ csrf_field() }}

            <div class="form-group">
                <label class="column-label" for="withdrawal_date">支払日付</label>
                <input type="date" class="form-control " id="withdrawal_date" name="data[Withdrawal][withdrawal_date]" value="<?php echo date('Y-m-d');?>" tabindex="1">
            </div>

            <div class="search-area">
                <div class="payment-date-form">
                    <div class="radio_box">
                        <label><input type="radio" name="data[Withdrawal][search_date]" value="1"> 伝票日付</label>
                        <label><input type="radio" name="data[Withdrawal][search_date]" value="2"> 納品日付</label>
                    </div>
                    <input type="date" class="form-control width-45 payment_from_date" id="payment_from_date" name="data[Withdrawal][payment_from_date]" value="<?php echo date('Y-m-d');?>" onchange='javascript:changeCalcFlg()' tabindex="2">
                    <p class="payment-block">〜</p>
                    <input type="date" class="form-control width-45 payment_to_date" id="payment_to_date" name="data[Withdrawal][payment_to_date]" value="<?php echo date('Y-m-d');?>" onchange='javascript:changeCalcFlg()' tabindex="3">
                </div>

                <table class="withdrawal-from-table">
                    <tr>
                        <th colspan="2" class="payment-label">支払先企業</th>
                        <th colspan="2" class="payment-label">支払先店舗</th>
                    </tr>
                    <tr>
                        <td class="width-20">
                            <input type="text" class="form-control withdrawal_company_code_input" id="withdrawal_company_code" name="data[Withdrawal][withdrawal_company_code]" onchange='javascript:changeCalcFlg()' tabindex="4">
                            <input type="hidden" id="withdrawal_company_id" name="data[Withdrawal][withdrawal_company_id]">
                        </td>
                        <td class="width-30">
                            <input type="text" class="form-control" id="withdrawal_company_text" name="data[Withdrawal][withdrawal_company_text]" readonly>
                        </td>
                        <td class="width-20">
                            <input type="text" class="form-control withdrawal_shop_code_input" id="withdrawal_shop_code" name="data[Withdrawal][withdrawal_shop_code]" tabindex="5">
                            <input type="hidden" id="withdrawal_shop_id" name="data[Withdrawal][withdrawal_shop_id]">
                        </td>
                        <td class="width-30">
                            <input type="text" class="form-control" id="withdrawal_shop_text" name="data[Withdrawal][withdrawal_shop_text]" readonly>
                        </td>
                    </tr>

                </table>
                <button id="search-btn" class="search-btn btn btn-primary" type="button" onclick='javascript:searchSupplySlips()'>抽出</button>
                <button id="calc-btn" class="calc-btn btn btn-primary" type="button" onclick="javascript:calcPaymentPrice()">計算</button>
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
                </table>
                <div id="supply-slip-area"></div>
            </div>
            {{--  抽出結果表示欄 終了  --}}

            <table class="withdrawal-table">
                <tr>
                    <th class="width-5">担当者</th>
                    <td>
                        <input type="text" class="form-control staff_code_input" id="staff_code" name="data[Withdrawal][staff_code]" tabindex="6">
                        <input type="hidden" id="staff_id" name="data[Withdrawal][staff_id]">
                    </td>
                    <td class="width-30">
                        <input type="text" class="form-control" id="staff_text" name="data[Withdrawal][staff_text]" readonly>
                    </td>
                </tr>
                <tr>
                    <th>支払金額</th>
                    <td class="width-20">
                        <input type="number" class="form-control" id="price" name="data[Withdrawal][price]" readonly>
                    </td>
                </tr>
                <tr>
                    <th>調整金額</th>
                    <td class="width-20">
                        <input type="number" class="form-control" id="adjustment_price" name="data[Withdrawal][adjustment_price]" onchange='javascript:calcTotalPaymentPrice()' tabindex="7">
                    </td>
                </tr>
                <tr>
                    <th>支払手段</th>
                    <td>
                        <select class="form-control" name="data[Withdrawal][payment_method_id]" tabindex="8">
                            <option value="1">東信当座</option>
                            <option value="2">東信普通</option>
                            <option value="3">みずほ</option>
                            <option value="4">みずほ個人</option>
                            <option value="5">UFJ個人</option>
                            <option value="6">現金</option>
                            <option value="7">小切手</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>メモ</th>
                    <td colspan="5">
                        <textarea class="form-control" id="memo" name="data[Withdrawal][memo]" row="5" tabindex="9" style="margin-top: 0px; margin-bottom: 0px; height: 150px;"></textarea>
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
                    <td colspan="2"><input type="number" class="form-control" id="total_price" name="data[Withdrawal][total_price]" value='0' readonly></td>
                </tr>
            </table>

            <br>
            <br>
            <table class="register-btn-table">
                <tr>
                    <td class="width-50">
                        <input type="submit" id="register-btn" class="register-btn btn btn-primary" value="支払登録" tabindex="9">
                        {{--  <button id="register-btn" class="register-btn btn btn-primary" type="button">支払登録</button>  --}}
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

        // 一番最初は売上先企業にフォーカスする
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
                $("#withdrawal-create-form [tabindex]").attr("tabindex", function(a, b) {

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
        $(document).on("blur", 'input[name!="data[Withdrawal][search_date]"]', function(event) {

            var tabindex = parseInt($(this).attr('tabindex'), 10);
            var set_val = $(this).val();
            var selector_code = $(this).attr('id');
            var selector_id = selector_code.replace('_code', '_id');
            var selector_text = selector_code.replace('_code', '_text');

            var fd = new FormData();
            fd.append("inputText", set_val);

            if (selector_code.match(/withdrawal_company/)) { // 支払先企業

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

            } else if (selector_code.match(/withdrawal_shop/)) { // 支払先店舗

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

        //-------------------------------------
        // autocomplete処理 支払企業ID
        //-------------------------------------
        $(".withdrawal_company_code_input").autocomplete({
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
        // autocomplete処理 支払店舗ID
        //-------------------------------------
        $(".withdrawal_shop_code_input").autocomplete({
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
})(jQuery);

// -----------
// 支払金額計算
// -----------
function searchSupplySlips() {

    // 入力値取得
    var payment_from_date = $('#payment_from_date').val();
    var payment_to_date   = $('#payment_to_date').val();
    var payment_company   = $('#withdrawal_company_id').val();
    var search_date_val   = $('input:radio[name="data[Withdrawal][search_date]"]:checked').val();
    var action = 'create';

    if (!search_date_val) {
        alert('抽出日付を選択してください。');
        return;
    }

    if (payment_from_date == '' && payment_to_date == '') {
        alert('日付を入力してください。');
        return;
    }

    if (payment_company == '') {
        alert('支払先企業を入力してください。');
        return;
    }

    var fd = new FormData();
    fd.append("payment_from_date", payment_from_date);
    fd.append("payment_to_date", payment_to_date);
    fd.append("payment_company", payment_company);
    fd.append("search_date_val", search_date_val);
    fd.append("action", action);

    // ajaxで対象範囲の支払金額を計算して持ってくる
    $.ajax({
        headers: {
            "X-CSRF-TOKEN": $("[name='_token']").val()
        },
        url: "./AjaxSearchSupplySlips",
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
function calcPaymentPrice() {

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
        notaxSubTotal8 += parseInt($('#supply-slip-subTotal8-' + id).val());

        // 10%税抜額
        notaxSubTotal10 += parseInt($('#supply-slip-subTotal10-' + id).val());

        // 配送額
        deliveryPrice += parseInt($('#supply-slip-deliveryPrice-' + id).val());

        // 調整額
        adjustPrice += parseInt($('#supply-slip-adjustPrice-' + id).val());

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

    calcTotalPaymentPrice();
}

// --------------
// 調整後支払金額の計算
// --------------
function calcTotalPaymentPrice() {

    // 入力値取得
    var adjust_price = $('#adjustment_price').val();
    if (adjust_price == '') adjust_price = 0;
    $('#adjust_total_price').val(parseInt(adjust_price));

    // 支払金額に値があるか確認なければ何もしない
    var payment_price = $('#price').val();
    if (payment_price == '') {
        return;
    }

    // -----------------
    // 調整金額も含めて計算
    // -----------------

    // 総合計計算
    var total_payment_price = 0;
    total_payment_price = parseInt(total) + parseInt(adjust_price);

    $("#total_price").val(total_payment_price);

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
    var withdrawal_date   = '';
    var payment_from_date = '';
    var payment_to_date   = '';
    var payment_company   = '';
    var payment_shop      = '';
    var staff_code        = '';
    var total_price       = '';

    // ----------
    // 入力チェック
    // ----------
    withdrawal_date = $("#withdrawal_date").val();
    if (withdrawal_date == '') {
        alert('支払日付を入力してください');
        return false;
    }

    payment_from_date = $("#payment_from_date").val();
    payment_to_date = $("#payment_to_date").val();
    if (payment_from_date == '' || payment_to_date == '') {
        alert('伝票日付を入力してください');
        return false;
    }

    payment_company = $("#withdrawal_company_code").val();
    if (payment_company == '') {
        alert('支払先企業を入力してください');
        return false;
    }

    staff_code = $("#staff_code").val();
    if (staff_code == '') {
        alert('担当者を入力してください');
        return false;
    }

    total_price = $("#total_price").val();
    if (total_price == '') {
        alert('調整後支払金額が空白です');
        return false;
    }

    // 支払日付を変更した場合に、「抽出」ボタンを押して支払金額の計算をしたか確認する
    var calc_flg = $("#calc-flg").val();
    if (calc_flg == '0') {
        alert('支払伝票のチェックボックスが変更されています。計算ボタンを押してください。');
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
// supply_slip_idの取得
// -------------------
function discardSupplySlipId(id) {

    // 対象IDのチェック状態を取得
    var isCheck = $('#supply-slip-id-' + id).prop('checked');

    if (isCheck) {
        // チェックしたら詳細エリアにIDを追加
        $('#supply-slip-area').append('<input type="hidden" id="supply-slip-detail-id-' + id + '" name="data[WithdrawalDetail][supply_slip_ids][]" value="' + id + '">');
    } else {
        // チェック外れたらIDを詳細エリアから外す
        $('#supply-slip-detail-id-' + id).remove();
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

    .payment-label {
        font-size: 0.9em;
        font-weight: bold;
    }

    .withdrawal-from-table {
        width: 100%;
    }

    .withdrawal-table {
        width: 100%;
        margin-top: 2%;
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

    .payment-date-form {
        width: 100%;
        margin-bottom: 2%;
    }

    .payment_from_date {
        float: left;
        display: block;
    }

    .payment-block {
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
    }

    .register-btn {
        width: 40%;
    }

</style>