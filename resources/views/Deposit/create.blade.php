@extends('layouts.app') @section('content')
    <div class="container">

        <div class="row justify-content-center">
            <div class="top-title">請求登録画面</div>

            <form class="smn-form" id="deposit-create-form" method="post" action="./registerDeposit"
                enctype="multipart/form-data" onsubmit="return submitCheck();">
                {{ csrf_field() }}

                <div class="form-group">
                    <label class="column-label payment-date-label" for="payment_date">支払期日</label>
                    <input type="date" class="form-control " id="deposit_company_payment_date"
                        name="data[Deposit][payment_date]" value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" tabindex="2">
                </div>

                <div class="search-area">
                    <div class="sales-date-form">
                        <div class="radio_box">
                            <label><input type="radio" name="data[Deposit][search_date]" value="1" checked>
                                伝票日付</label>
                            <label><input type="radio" name="data[Deposit][search_date]" value="2"> 納品日付</label>
                        </div>
                        <input type="date" class="form-control width-45 sales_from_date" id="sales_from_date"
                            name="data[Deposit][sales_from_date]" value="<?php echo date('Y-m-d'); ?>"
                            onchange='javascript:changeCalcFlg()' tabindex="3">
                        <p class="sales-block">〜</p>
                        <input type="date" class="form-control width-45 sales_to_date" id="sales_to_date"
                            name="data[Deposit][sales_to_date]" value="<?php echo date('Y-m-d'); ?>"
                            onchange='javascript:changeCalcFlg()' tabindex="4">
                    </div>

                    <div class="form-group" style="margin-bottom: 0 !important;">
                        <div class="radio_box">
                            <label><input type="radio" name="data[Deposit][invoice_output_type]" value="0"
                                    onchange="toggleCompanyInput()"> 本部企業毎</label>
                            <label><input type="radio" name="data[Deposit][invoice_output_type]" value="1" checked
                                    onchange="toggleCompanyInput()"> 売上先店舗毎</label>
                        </div>
                    </div>

                    <table class="deposit-from-table">
                        <tr>
                            <th colspan="2" class="sales-label owner_company_area" style="display: none;">本部企業</th>
                            <th colspan="2" class="sales-label sale_company_area">売上先店舗</th>
                        </tr>
                        <tr>
                            <td colspan="2" class="width-50 owner_company_area" id="owner_company_area"
                                style="display: none;">
                                <div class="d-flex">
                                    <input type="text" class="form-control mr-2 deposit_owner_code_input"
                                        id="deposit_owner_code" name="data[Deposit][deposit_owner_code]"
                                        onchange='changeCalcFlg()' tabindex="5" style="width: 50%;">
                                    <input type="text" class="form-control" id="deposit_owner_text"
                                        name="data[Deposit][deposit_owner_text]" readonly style="width: 50%;">
                                </div>
                                <input type="hidden" id="deposit_owner_id" name="data[Deposit][deposit_owner_id]">
                            </td>
                            <td colspan="2" class="width-50 sale_company_area" id="sale_company_area">
                                <div class="d-flex">
                                    <input type="text" class="form-control mr-2 deposit_company_code_input"
                                        id="deposit_company_code" name="data[Deposit][deposit_company_code]"
                                        onchange='changeCalcFlg()' tabindex="6" style="width: 50%;">
                                    <input type="text" class="form-control" id="deposit_company_text"
                                        name="data[Deposit][deposit_company_text]" readonly style="width: 50%;">
                                </div>
                                <input type="hidden" id="deposit_company_id" name="data[Deposit][deposit_company_id]">
                                <input type="hidden" id="deposit_company_tax_calc_type"
                                    name="data[Deposit][deposit_company_tax_calc_type]">
                            </td>
                        </tr>

                    </table>
                    <button id="search-btn" class="search-btn btn btn-primary" type="button"
                        onclick='javascript:searchSaleSlips()'>抽出</button>
                    <button id="calc-btn" class="calc-btn btn btn-primary" type="button"
                        onclick="javascript:calcSalesPrice()">計算</button>
                    <input type="hidden" id="calc-flg" value="0">
                </div>

                {{-- 抽出結果表示欄 開始 --}}
                <div id="result-area" class="result-area">
                    <input type="checkbox" id="checkbox_all">
                    <label for="checkbox_all">全て選択 / 解除</label>
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
                    <div id="sale-slip-area"></div>
                </div>
                {{-- 抽出結果表示欄 終了 --}}

                <table class="deposit-table">
                    <tr>
                        <th class="width-5">担当者</th>
                        <td>
                            <input type="text" class="form-control staff_code_input" id="staff_code"
                                name="data[Deposit][staff_code]" value="1009" tabindex="7">
                            <input type="hidden" id="staff_id" name="data[Deposit][staff_id]" value="9">
                        </td>
                        <td class="width-30">
                            <input type="text" class="form-control" id="staff_text" name="data[Deposit][staff_text]"
                                value="石塚 貞雄" readonly>
                        </td>
                    </tr>
                    <tr>
                        <th>請求金額</th>
                        <td class="width-20">
                            <input type="number" class="form-control" id="price" name="data[Deposit][price]"
                                readonly>
                        </td>
                    </tr>
                    <tr>
                        <th>調整金額</th>
                        <td class="width-20">
                            <input type="number" class="form-control" id="adjustment_price"
                                name="data[Deposit][adjustment_price]" onchange='javascript:calcTotalSalesPrice()'
                                tabindex="8">
                        </td>
                    </tr>
                    <tr>
                        <th>振込先</th>
                        <td>
                            <select name="data[Deposit][bank_account_id]" id="bank_account_id" class="form-control" required>
                                @foreach ($bankAccounts as $account)
                                    <option value="{{ $account->id }}">
                                        {{ $account->bank_name }}（{{ $account->branch_name }}）{{ $account->account_number }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>メモ</th>
                        <td colspan="5">
                            <textarea class="form-control" id="memo" name="data[Deposit][memo]" row="5" tabindex="10"
                                style="margin-top: 0px; margin-bottom: 0px; height: 150px;"></textarea>
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
                        <td><input type="tel" class="form-control" id="notax_sub_total_8" value='0' readonly>
                        </td>
                        <td><input type="tel" class="form-control" id="tax_total_8" value='0' readonly></td>
                        <td><input type="tel" class="form-control" id="sub_total_8" value='0' readonly></td>
                    </tr>
                    <tr>
                        <th>10%対象額</th>
                        <th>10%税額</th>
                        <th>10%税込額</th>
                    </tr>
                    <tr>
                        <td><input type="tel" class="form-control" id="notax_sub_total_10" value='0' readonly>
                        </td>
                        <td><input type="tel" class="form-control" id="tax_total_10" value='0' readonly></td>
                        <td><input type="tel" class="form-control" id="sub_total_10" value='0' readonly></td>
                    </tr>
                    <tr>
                        <th>税抜小計</th>
                        <th>税額</th>
                        <th>税込小計</th>
                    </tr>
                    <tr>
                        <td><input type="tel" class="form-control" id="notax_sub_total" value='0' readonly>
                        </td>
                        <td><input type="tel" class="form-control" id="tax_total" value='0' readonly></td>
                        <td><input type="tel" class="form-control" id="sub_total" value='0' readonly></td>
                    </tr>
                    <tr>
                        <th>配送額総計</th>
                        <th>伝票調整額総計</th>
                        <th>調整額</th>
                    </tr>
                    <tr>
                        <td><input type="text" class="form-control" id="delivery_total_price" value='0'
                                readonly></td>
                        <td><input type="text" class="form-control" id="slip_adjust_total_price" value='0'
                                readonly></td>
                        <td><input type="text" class="form-control" id="adjust_total_price" value='0' readonly>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2">最終請求金額</th>
                    </tr>
                    <tr>
                        <td colspan="2"><input type="number" class="form-control" id="total_price"
                                name="data[Deposit][total_price]" value='0' readonly></td>
                    </tr>
                </table>

                <br>
                <br>
                <table class="register-btn-table">
                    <tr>
                        <td class='status-memo-area' colspan="3">0:未入金 1:入金済 2:繰越</td>
                    </tr>
                    <tr>
                        <td class="width-20">
                            <input type="tel" class="form-control" id="deposit_submit_type"
                                name="data[Deposit][deposit_submit_type]" value="0">
                        </td>
                        <td class="width-30">
                            <input type="text" class="form-control" id="deposit_submit_type_text"
                                name="data[Deposit][deposit_submit_type_text]" value="入金済" readonly>
                        </td>
                        <td class="width-50">
                            <input type="button" id="register-btn" class="register-btn btn btn-primary" value="請求登録"
                                tabindex="11">
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

    //チェックボックスのリスト
    let checkbox_list;

    (function($) {
        jQuery(window).load(function() {

            // 一番最初は本部企業にフォーカスする
            $('#deposit_company_code').focus();

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

            //全選択・解除のチェックボックス
            let checkbox_all = document.querySelector("#checkbox_all");
            //全選択のチェックボックスイベント
            checkbox_all.addEventListener('change', change_all);

            toggleCompanyInput();

            //-------------------------------------
            // Enterと-を押したときにタブ移動する処理
            //-------------------------------------
            $(document).on("keyup", "input", function(event) {

                if (event.keyCode === 13) { // Enterが押された時

                    var this_id = $(this).attr('id');

                    if (this_id == 'deposit_submit_type') {

                        var submit_type = $(this).val();
                        // 全角数字を半角に変換
                        submit_type = submit_type.replace(/[０-９]/g, function(s) {
                            return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
                        });
                        $(this).val(submit_type);

                        if (submit_type == 0) {
                            $('#deposit_submit_type_text').val("未入金");
                            $('#register-btn').prop('disabled', false);
                            $('#register-btn').focus();
                        } else if (submit_type == 1) {
                            $('#deposit_submit_type_text').val("入金済");
                            $('#register-btn').prop('disabled', false);
                            $('#register-btn').focus();
                        } else if (submit_type == 2) {
                            $('#deposit_submit_type_text').val("繰越");
                            $('#register-btn').prop('disabled', false);
                            $('#register-btn').focus();
                        } else {
                            $('#deposit_submit_type_text').val("存在しない登録番号です。");
                            $('#register-btn').prop('disabled', true);
                        }

                    } else {

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
                    }

                    return false;

                } else if (event.keyCode === 111) { // スラッシュが押された時

                    var this_id = $(this).attr('id');

                    // 文字列の最後の文字を削除
                    $(this).val($(this).val().slice(0, -1));

                    // 現在のtabIndex取得
                    var tabindex = parseInt($(this).attr('tabindex'), 10);
                    if (isNaN(tabindex)) return false;

                    // ひとつ前のタブの最大値を取得
                    var max = 0;
                    $("#deposit-create-form [tabindex]").attr("tabindex", function(a, b) {

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
                // 全角数字を半角に変換
                set_val = set_val.replace(/[０-９]/g, function(s) {
                    return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
                });
                $(this).val(set_val);
                var selector_code = $(this).attr('id');
                var selector_id = selector_code.replace('_code', '_id');
                var selector_text = selector_code.replace('_code', '_text');
                var selector_tax_calc_type = selector_code.replace('_code', '_tax_calc_type');
                var selector_payment_date = selector_code.replace('_code', '_payment_date');

                var fd = new FormData();
                fd.append("inputText", set_val);

                if (selector_code.match(/deposit_company/)) { // 売上先店舗

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
                            $("#" + selector_tax_calc_type).val(data[3]);
                            $("#" + selector_payment_date).val(data[4]);
                        });

                } else if (selector_code.match(/deposit_owner/)) { // 本部企業

                    $.ajax({
                            headers: {
                                "X-CSRF-TOKEN": $("[name='_token']").val()
                            },
                            url: "./AjaxSetOwnerCompany",
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
            // autocomplete処理 売上先店舗ID
            //-------------------------------------
            $(".deposit_company_code_input").autocomplete({
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
            // autocomplete処理 本部企業ID
            //-------------------------------------
            $(".deposit_owner_code_input").autocomplete({
                source: function(req, resp) {
                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./AjaxAutoCompleteOwnerCompany",
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

                var this_val = $("#deposit_submit_type").val();

                if (this_val == "0") {
                    $('#deposit-create-form').submit();
                } else if (this_val == "1") {
                    $('#deposit-create-form').submit();
                } else if (this_val == "2") {
                    $('#deposit-create-form').submit();
                } else {
                    return false;
                }
            });

            // ------------------------------
            // submit_typeのフォーカスが外れた時
            // ------------------------------
            $('#deposit_submit_type').blur(function() {
                var submit_type = $(this).val();
                // 全角数字を半角に変換
                submit_type = submit_type.replace(/[０-９]/g, function(s) {
                    return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
                });
                $(this).val(submit_type);

                if (submit_type == 0) {
                    $('#deposit_submit_type_text').val("未入金");
                    $('#register-btn').prop('disabled', false);
                    $('#register-btn').focus();
                } else if (submit_type == 1) {
                    $('#deposit_submit_type_text').val("入金済");
                    $('#register-btn').prop('disabled', false);
                    $('#register-btn').focus();
                } else if (submit_type == 2) {
                    $('#deposit_submit_type_text').val("繰越");
                    $('#register-btn').prop('disabled', false);
                    $('#register-btn').focus();
                } else {
                    $('#deposit_submit_type_text').val("存在しない登録番号です。");
                    $('#register-btn').prop('disabled', true);
                }
            });

        });

    })(jQuery);

    // -----------
    // 売上金額計算
    // -----------
    function searchSaleSlips() {

        // 入力値取得
        var sales_from_date = $('#sales_from_date').val();
        var sales_to_date = $('#sales_to_date').val();
        var search_date_val = $('input:radio[name="data[Deposit][search_date]"]:checked').val();
        var output_type = $('input:radio[name="data[Deposit][invoice_output_type]"]:checked').val();
        var action = 'create';

        // 入力値チェック
        if (!search_date_val) {
            alert('抽出日付を選択してください。');
            return;
        }

        if (sales_from_date == '' && sales_to_date == '') {
            alert('日付を入力してください。');
            return;
        }

        // FormData作成
        var fd = new FormData();
        fd.append("sales_from_date", sales_from_date);
        fd.append("sales_to_date", sales_to_date);
        fd.append("search_date_val", search_date_val);
        fd.append("output_type", output_type);
        fd.append("action", action);

        if (output_type === "0") {
            // 本部企業で検索
            var owner_company = $('#deposit_owner_id').val();
            if (owner_company == '') {
                alert('本部企業を入力してください。');
                return;
            }
            fd.append("owner_company_id", owner_company);
        } else {
            // 店舗で検索
            var sales_company = $('#deposit_company_id').val();
            if (sales_company == '') {
                alert('売上先店舗を入力してください。');
                return;
            }
            fd.append("sale_company_id", sales_company);
        }

        // ajaxで対象範囲の売上金額を計算して持ってくる
        $.ajax({
                headers: {
                    "X-CSRF-TOKEN": $("[name='_token']").val()
                },
                url: "./AjaxSearchSaleSlips",
                type: "POST",
                dataType: "JSON",
                data: fd,
                processData: false,
                contentType: false
            })
            .done(function(data) {
                if (data != '') {
                    $(".result-table").html(data[0]);
                    // チェックボックスのリストを取得
                    checkbox_list = document.querySelectorAll(".checkbox_list");
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
    // 選択された値の売上金額の計算
    // ------------------------
    function calcSalesPrice() {

        if (!$('.result-table').length) {
            alert("抽出してから計算ボタンを押してください。");
            return;
        }

        var total = 0;
        var notaxSubTotal8 = 0;
        var subTotal8 = 0;
        var notaxSubTotal10 = 0;
        var subTotal10 = 0;
        var deliveryPrice = 0;
        var adjustPrice = 0;
        var tax8 = 0;
        var tax10 = 0;

        // チェックされている要素を全削除
        $('#sale-slip-area').empty();

        // 企業の税金計算区分取得(0:伝票ごとに消費税計算 1:請求書ごとに消費税計算)
        var tax_calc_type = $('#deposit_company_tax_calc_type').val();

        // チェックボックスの値を取得して計算
        $('.result-table input:checked').each(function() {

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

            // 消費税計算方法が伝票ごとの消費税計算の場合
            if (tax_calc_type == 0) {
                // 8%消費税額
                subTotal8 += parseInt($('#sale-slip-tax8-' + id).val()) + parseInt($('#sale-slip-subTotal8-' +
                    id).val());
                // 10%消費税額
                subTotal10 += parseInt($('#sale-slip-tax10-' + id).val()) + parseInt($(
                    '#sale-slip-subTotal10-' + id).val());
            }

            // チェックしたら詳細エリアにIDを追加
            $('#sale-slip-area').append('<input type="hidden" id="sale-slip-detail-id-' + id +
                '" name="data[DepositDetail][sale_slip_ids][]" value="' + id + '">');

        });

        // 消費税計算方法が請求書ごとの消費税計算の場合
        if (tax_calc_type == "1") {
            // 8%税込額
            subTotal8 = Math.floor(notaxSubTotal8 * 1.08);
            // 10%税込額
            subTotal10 = Math.floor(notaxSubTotal10 * 1.1);
        }

        // 8%消費税額
        tax8 = parseInt(subTotal8) - parseInt(notaxSubTotal8);

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

        calcTotalSalesPrice();
    }

    // --------------
    // 調整後売上金額の計算
    // --------------
    function calcTotalSalesPrice() {

        // 入力値取得
        var adjust_price = $('#adjustment_price').val();
        if (adjust_price == '') adjust_price = 0;
        $('#adjust_total_price').val(parseInt(adjust_price));

        // 売上金額に値があるか確認なければ何もしない
        var sales_price = $('#price').val();
        if (sales_price == '') {
            return;
        }

        // -----------------
        // 調整金額も含めて計算
        // -----------------

        // 総合計計算
        var total_sales_price = 0;
        total_sales_price = parseInt(sales_price) + parseInt(adjust_price);

        $("#total_price").val(total_sales_price);

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
        var deposit_date = '';
        var sales_from_date = '';
        var sales_to_date = '';
        var staff_code = '';
        var total_price = '';

        // -------------------
        // 入金日付チェック
        // -------------------
        deposit_date = $("#deposit_company_payment_date").val();
        if (deposit_date == '') {
            alert('入金日付を入力してください');
            return false;
        }

        // -------------------
        // 伝票日付チェック
        // -------------------
        sales_from_date = $("#sales_from_date").val();
        sales_to_date = $("#sales_to_date").val();
        if (sales_from_date == '' || sales_to_date == '') {
            alert('伝票日付を入力してください');
            return false;
        }

        // -------------------
        // 出力タイプごとに企業コードチェック
        // -------------------
        const outputType = $('input[name="data[Deposit][invoice_output_type]"]:checked').val();
        if (outputType === '0') {
            const ownerCode = $("#deposit_owner_code").val();
            if (ownerCode == '') {
                alert('本部企業を入力してください');
                return false;
            }
        } else {
            const companyCode = $("#deposit_company_code").val();
            if (companyCode == '') {
                alert('売上先店舗を入力してください');
                return false;
            }
        }

        // -------------------
        // 担当者チェック
        // -------------------
        staff_code = $("#staff_code").val();
        if (staff_code == '') {
            alert('担当者を入力してください');
            return false;
        }

        // -------------------
        // 金額チェック
        // -------------------
        total_price = $("#total_price").val();
        if (total_price == '') {
            alert('調整後入金金額が空白です');
            return false;
        }

        // -------------------
        // 抽出 → 計算実行済みか確認
        // -------------------
        var calc_flg = $("#calc-flg").val();
        if (calc_flg == '0') {
            alert('売上伝票のチェックボックスが変更されています。計算ボタンを押してください。');
            return false;
        }

        return true;

    }

    // --------------
    // 計算フラグの変更
    // --------------
    function changeCalcFlg() {

        $("#calc-flg").val(0);

    }

    // -------------------
    // 計算対象のチェックボックスを全選択・全解除
    // -------------------
    function change_all() {

        //チェックされているか
        if ($('#checkbox_all').prop("checked")) {

            //全て選択
            for (let i in checkbox_list) {
                if (checkbox_list.hasOwnProperty(i)) {
                    checkbox_list[i].checked = true;
                }
            }

        } else {
            //全て解除
            for (let i in checkbox_list) {
                if (checkbox_list.hasOwnProperty(i)) {
                    checkbox_list[i].checked = false;
                }
            }
        }
    }

    // --------------------
    // 本部、売上先店舗の切り替え
    // --------------------
    function toggleCompanyInput() {
        const selected = document.querySelector('input[name="data[Deposit][invoice_output_type]"]:checked').value;

        const ownerAreas = document.getElementsByClassName('owner_company_area');
        const saleAreas = document.getElementsByClassName('sale_company_area');

        const ownerDisplay = selected === '0' ? '' : 'none';
        const saleDisplay = selected === '0' ? 'none' : '';

        // HTMLCollectionはループでスタイル変更
        for (let i = 0; i < ownerAreas.length; i++) {
            ownerAreas[i].style.display = ownerDisplay;
        }
        for (let i = 0; i < saleAreas.length; i++) {
            saleAreas[i].style.display = saleDisplay;
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

    .deposit-from-table {
        width: 100%;
    }

    .deposit-table {
        width: 100%;
        margin-top: 2%;
    }

    .width-5 {
        width: 5% !important;
    }

    .width-10 {
        width: 10% !important;
    }

    .width-15 {
        width: 15% !important;
    }

    .width-20 {
        width: 20% !important;
    }

    .width-30 {
        width: 30% !important;
    }

    .width-40 {
        width: 40% !important;
    }

    .width-45 {
        width: 45% !important;
    }

    .slip-table {
        width: 100%;
    }

    .partition-area {
        width: 100%;
        height: 1.0em;
    }

    .remove-slip-btn {
        height: calc(4.2rem + 7px) !important;
        width: 100%;
    }

    .sales-date-form {
        width: 100%;
        margin-bottom: 2%;
    }

    .sales_from_date {
        float: left;
        display: block;
    }

    .sales-block {
        float: left;
        width: 10%;
        text-align: center;
    }

    .result-area {
        display: block;
        width: 100%;
        height: auto;
        margin-top: 2%;
        overflow: auto;
        min-height: 150px;
    }

    .result-table {
        border-collapse: collapse;
        margin: auto;
        padding: 0;
        width: 100%;
        table-layout: fixed;
        font-size: 10px;
    }

    .result-table th {
        border-right: 1px solid #bbb;
        text-align: center;
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

    .result-table tr {
        padding: 1%;
        border-bottom: 1px solid #bbb;
    }

    .result-table tr:last-child {
        border-bottom: none
    }

    .result-table td {
        padding: 0.5%;
        border-right: 1px solid #bbb;
        text-align: center;
    }

    .result-table th:last-child,
    .result-table td:last-child {
        border: none;
    }

    .calc-btn {
        margin: 1%;
        background-color: #FF570D !important;
        border-color: #FF570D !important;
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
        width: 85%;
    }

    .payment-date-label {
        margin-top: 2%;
    }

    .status-memo-area {
        width: 100%;
        padding: 20px 10px;
        font-weight: bold;
        letter-spacing: 2px;
        text-align: left;
    }
</style>
