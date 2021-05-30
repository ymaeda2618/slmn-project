@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">
        <div class="top-title">経費登録</div>

        <form class="smn-form" id="expense_billing-create-form" method="post" action="./registerExpenseBilling" enctype="multipart/form-data" onsubmit="return submitCheck();">
            {{ csrf_field() }}

            <div class="form-group">
                <label class="column-label" for="date">請求日</label>
                <input type="date" class="form-control " id="date" name="data[ExpenseBilling][date]" value="<?php echo date('Y-m-d');?>">
            </div>

            <div class="form-group">
                <label class="column-label payment-date-label" for="due_date">支払期日</label>
                <input type="date" class="form-control " id="due_date" name="data[ExpenseBilling][due_date]" value="<?php echo date('Y-m-d');?>">
            </div>

            <div class="form-group">
                <div>
                    <div class="sales-label">仕入先企業</div>
                </div>
                <div>
                    <div class="width-20">
                        <input type="text" class="form-control supply_company_code_input" id="supply_company_code" name="data[ExpenseBilling][supply_company_code]" onchange='javascript:changeCalcFlg()'>
                        <input type="hidden" id="supply_company_id" name="data[ExpenseBilling][supply_company_id]">
                    </div>
                    <div class="width-20">
                        <input type="text" class="form-control" id="supply_company_text" name="data[ExpenseBilling][supply_company_text]" readonly>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="column-label" for="expense_item">項目種別</label>
                <select class="file-control" id="expense_item" name="data[ExpenseBilling][expense_item_id]">
                @foreach ($expenseItemList as $expenseItems)
                    <option value="{{$expenseItems->id}}">{{$expenseItems->name}}</option>
                @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="column-label" for="name">項目名</label>
                <input type="text" class="form-control" id="name" name="data[ExpenseBilling][name]">
            </div>

            <div class="form-group">
                <label class="column-label" for="price">金額</label>
                <input type="number" class="form-control" id="price" name="data[ExpenseBilling][price]">
            </div>

            <div class="form-group">
                <label class="column-label" for="staff_id">担当者</label>
                <select class="file-control" id="staff_id" name="data[ExpenseBilling][staff_id]">
                @foreach ($staffList as $staffs)
                    <option value="{{$staffs->id}}">{{$staffs->name}}</option>
                @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="column-label" for="memo">備考</label>
                <textarea class="form-control" id="memo" name="data[ExpenseBilling][memo]" row="5" style="margin-top: 0px; margin-bottom: 0px; height: 150px;"></textarea>
            </div>

            <div class="register-btn-area">
                <div>
                    <div class='status-memo-area'>0:未支払 1:支払済 2:繰越</div>
                </div>
                <div>
                    <div class="width-20">
                        <input type="tel" class="form-control" id="expense_billing_submit_type" name="data[ExpenseBilling][submit_type]" value="0">
                    </div>
                    <div class="width-30">
                        <input type="text" class="form-control" id="expense_billing_submit_type_text" name="data[ExpenseBilling][expense_billing_submit_type_text]" value="未入金" readonly>
                    </div>
                    <div class="width-50">
                        <input type="button" id="register-btn" class="register-btn btn btn-primary" value="請求登録">
                    </div>
                </div>
            </div>

        </form>
    </div>

</div>
@endsection
<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<script type="text/javascript">
    (function($) {
        jQuery(window).load(function() {

            //-------------------------------------
            // Enterと-を押したときにタブ移動する処理
            //-------------------------------------
            $(document).on("keyup", "input", function(event) {

                if (event.keyCode === 13) { // Enterが押された時

                    var this_id = $(this).attr('id');

                    if (this_id == 'expense_billing_submit_type') {

                        var submit_type = $(this).val();
                        // 全角数字を半角に変換
                        submit_type = submit_type.replace(/[０-９]/g, function(s) {
                            return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
                        });
                        $(this).val(submit_type);

                        if (submit_type == 0) {
                            $('#deposit_submit_type_text').val("未支払");
                            $('#register-btn').prop('disabled', false);
                            $('#register-btn').focus();
                        } else if (submit_type == 1) {
                            $('#deposit_submit_type_text').val("支払済");
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

                    }

                    return false;

                }
            });

            //-------------------------------------
            // フォーカスアウトしたときの処理
            //-------------------------------------
            $(document).on("blur", 'input[name!="data[Deposit][search_date]"]', function(event) {

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

                if (selector_code.match(/supply_company/)) { // 仕入企業

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

                }
            });

            //-------------------------------------
            // autocomplete処理 仕入企業ID
            //-------------------------------------
            $(".supply_company_code").autocomplete({
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
            // 登録を押したときの処理
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
            $('#expense_billing_submit_type').blur(function() {
                var submit_type = $(this).val();
                // 全角数字を半角に変換
                submit_type = submit_type.replace(/[０-９]/g, function(s) {
                    return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
                });
                $(this).val(submit_type);

                if (submit_type == 0) {
                    $('#expense_billing_submit_type_text').val("未入金");
                    $('#register-btn').prop('disabled', false);
                    $('#register-btn').focus();
                } else if (submit_type == 1) {
                    $('#expense_billing_submit_type_text').val("入金済");
                    $('#register-btn').prop('disabled', false);
                    $('#register-btn').focus();
                } else if (submit_type == 2) {
                    $('#expense_billing_submit_type_text').val("繰越");
                    $('#register-btn').prop('disabled', false);
                    $('#register-btn').focus();
                } else {
                    $('#expense_billing_submit_type_text').val("存在しない登録番号です。");
                    $('#register-btn').prop('disabled', true);
                }
            });

        });

    })(jQuery);

    //------------
    // 入力チェック
    //------------
    function submitCheck() {

        var date = $('#date').val(); // 請求日
        var due_date = $('#due_date').val(); // 支払期限
        var supply_company_code = $('#supply_company_code').val(); // 仕入先企業コード
        var expense_item = $('#expense_item').val(); // 項目種別
        var name = $('#name').val(); // 項目名
        var price = $('#price').val(); // 金額

        if (date == "") {
            alert('請求日が入力されておりません。');
            return false;
        }

        if (due_date == "") {
            alert('支払期限が入力されておりません。');
            return false;
        }

        if (supply_company_code == "") {
            alert('仕入先企業コードが入力されておりません。');
            return false;
        }

        if (expense_item == "" || expense_item == 0) {
            alert('項目種別が選択されておりません。');
            return false;
        }

        if (name == "") {
            alert('項目名が入力されておりません。');
            return false;
        }

        if (price == "") {
            alert('金額が入力されておりません。');
            return false;
        }

        if (isNaN(price)) {
            alert('金額が数値ではありません。');
            return false;
        }

        return true;
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
</style>
