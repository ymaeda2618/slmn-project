@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">仕入発注伝票 登録画面</div>

        <form class="smn-form" id="order-supply-unit-price-create-form" method="post" action="./OrderSupplyUnitPriceRegister" enctype="multipart/form-data" onsubmit="return inputCheck();">
            {{ csrf_field() }}

            <br>
            <table class="supply-from-table">
                <tr>
                    <th class="column-label" colspan="2">仕入店舗</th>
                </tr>
                <tr>
                    <td class="width-20">
                        <input type="text" class="form-control supply_company_code_input" id="supply_company_code" name="data[OrderSupplyUnitPrice][supply_company_code]" tabindex="1">
                        <input type="hidden" id="supply_company_id" name="data[OrderSupplyUnitPrice][supply_company_id]">
                    </td>
                    <td class="width-30">
                        <input type="text" class="form-control" id="supply_company_text" name="data[OrderSupplyUnitPrice][supply_company_text]" readonly>
                    </td>
                </tr>

            </table>

            <table class="product-table">
                <tr>
                    <th colspan="2">製品ID</th>
                    <th>金額</th>
                    <th>適用開始日</th>
                    <th colspan="2">担当者</th>
                    <th>削除</th>
                </tr>
                <tr id="product-partition-0" class="partition-area"></tr>
                <tr id="product-upper-0">
                    {{-- 製品 START --}}
                    <td class="width-10">
                        <input type="text" class="form-control product_code_input" id="product_code_0" name="data[OrderSupplyUnitPriceDetail][0][product_code]" tabindex="3">
                        <input type="hidden" id="product_id_0" name="data[OrderSupplyUnitPriceDetail][0][product_id]">
                    </td>
                    <td class="width-20">
                        <input type="text" class="form-control" id="product_text_0" name="data[OrderSupplyUnitPriceDetail][0][product_text]" placeholder="製品欄" readonly>
                    </td>
                    {{-- 製品 END --}}
                    {{-- 金額 START --}}
                    <td class="width-10">
                        <input type="number" class="form-control" id="order_unit_price_0" name="data[OrderSupplyUnitPriceDetail][0][order_unit_price]" tabindex="4">
                    </td>
                    {{-- 金額 END --}}
                    {{-- 適用開始日 START --}}
                    <td class="width-10">
                        <input type="date" class="form-control " id="apply_from" name="data[OrderSupplyUnitPriceDetail][0][apply_from]" value="<?php echo date('Y-m-d');?>" tabindex="5">
                    </td>
                    {{-- 適用開始日 END --}}
                    {{-- 担当者 START --}}
                    <td class="width-10">
                        <input type="text" class="form-control staff_code_input" id="staff_code_0" name="data[OrderSupplyUnitPriceDetail][0][staff_code]" value="1009" tabindex="6">
                        <input type="hidden" id="staff_id_0" name="data[OrderSupplyUnitPriceDetail][0][staff_id]" value="9">
                    </td>
                    <td class="width-20">
                        <input type="text" class="form-control" id="staff_text_0" name="data[OrderSupplyUnitPriceDetail][0][staff_text]" value="石塚 貞雄" placeholder="担当者" readonly>
                    </td>
                    {{-- 担当者 END --}}
                    <td class="width-5">
                        <button id="remove-product-btn" type="button" class="btn remove-product-btn btn-secondary" onclick='javascript:removeProduct(0)'>削除</button>
                    </td>
                    {{-- 削除 END --}}
                </tr>
            </table>
            <br><br>
            <div class="add-product-btn-area">
                <button id="add-product-btn" type="button" class="btn add-product-btn btn-primary">伝票追加</button>
                <input type='hidden' name="product_num" id="product_num" value="1">
            </div>

            <div class="form-group">
                <label class="column-label" for="remarks">備考欄</label>
                <textarea id="remarks" class="form-control" name="data[OrderSupplyUnitPrice][remarks]" rows="4" cols="40"></textarea>
            </div>

            <table class="register-btn-table">
                <tr>
                    <td class="width-50">
                        <button id="register-btn" class="register-btn btn btn-primary" type="button">登録</button>
                    </td>
                </tr>
            </table>

            <input type="hidden" name="submit_type" id="submit_type" value="1">
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

            // 一番最初は仕入先店舗にフォーカスする
            $('#supply_company_code').focus();

            //-------------------------------------
            // Enterと-を押したときにタブ移動する処理
            //-------------------------------------
            $(document).on("keyup", "input", function(event) {

                if (event.keyCode === 13) { // Enterが押された時

                    var this_id = $(this).attr('id');

                    // 現在のtabIndex取得
                    var tabindex = parseInt($(this).attr('tabindex'), 10);
                    if (isNaN(tabindex)) return false;

                    // ひとつ前のタブの最小値を取得
                    var min = 0;
                    $("#order-supply-unit-price-create-form [tabindex]").attr("tabindex", function(a, b) {

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

                } else if (event.keyCode === 111) { // スラッシュが押された時

                    var this_id = $(this).attr('id');

                    // 文字列の最後の文字を削除
                    $(this).val($(this).val().slice(0, -1));

                    // 現在のtabIndex取得
                    var tabindex = parseInt($(this).attr('tabindex'), 10);
                    if (isNaN(tabindex)) return false;

                    // ひとつ前のタブの最大値を取得
                    var max = 0;
                    $("#order-supply-unit-price-create-form [tabindex]").attr("tabindex", function(a, b) {

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

                } else if (event.keyCode === 107) { // プラスが押された時

                    var this_id = $(this).attr('id');

                    // 文字列の最後の文字を削除
                    $(this).val($(this).val().slice(0, -1));

                    if (this_id.match(/memo/)) {

                        $('#add-product-btn').trigger('click');

                        return false;
                    }

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

                } else if (selector_code.match(/product_code/)) { // 製品IDの部分

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

            //--------------------
            // 伝票追加処理
            //--------------------
            $("#add-product-btn").on('click', function() {

                // 伝票ナンバーを取得
                var product_num = $("#product_num").val();

                var fd = new FormData();
                fd.append("product_num", product_num); // 押されたボタンID

                $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $("[name='_token']").val()
                        },
                        url: "./AjaxAddProduct",
                        type: "POST",
                        dataType: "JSON",
                        data: fd,
                        processData: false,
                        contentType: false
                    })
                    .done(function(data) {

                        // 伝票ナンバーを取得
                        $("#product_num").val(data[0]);

                        // 伝票追加
                        $(".product-table").append(data[1]);

                        // 製品ID
                        let product_code_selector = $(data[2]).autocomplete({
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
                        $("#product-code-area-" + product_num).append(product_code_selector);

                        // 担当ID
                        let staff_code_selector = $(data[3]).autocomplete({
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
                        $("#staff-code-area-" + product_num).append(staff_code_selector);

                    })
                    .fail(function(XMLHttpRequest, textStatus, errorThrown) {
                        alert(XMLHttpRequest);
                        alert(textStatus);
                        alert(errorThrown);
                        // 送信失敗
                        alert("失敗しました。");
                    });
            });

            //---------------
            // 登録ボタン押下時
            //---------------
            $(document).on("click", ".register-btn", function() {
                $('#order-supply-unit-price-create-form').submit();
            });

        });
    })(jQuery);

    function removeProduct(remove_num) {

        // 削除
        $("#product-partition-" + remove_num).remove();
        $("#product-upper-" + remove_num).remove();
        $("#product-lower-" + remove_num).remove();

    }

    //------------
    // 入力チェック
    //------------
    function inputCheck() {

        // ----------
        // 変数初期化
        // ----------
        var supply_company_code;    // 仕入店舗
        var product_code;           // 製品ID
        var order_unit_price;       // 金額
        var staff_code;             // 担当

        // -----------
        // 入力チェック
        // -----------
        supply_company_code = $("#supply_company_code").val();
        if (supply_company_code == '') {
            alert('「仕入店舗」を入力してください。');
            return false;
        }

        // 複数データがある場合
        var product_num = $("#product_num").val();
        for (i = 0; i < product_num; i++) {

            // 製品ID
            product_code = $("#product_code_" + i).val();
            if (product_code == '') {
                alert('「製品ID」を入力してください。');
                return false;
            }

            // 単価
            order_unit_price = $("#order_unit_price_" + i).val();
            if (order_unit_price == '') {
                alert('「金額」を入力してください。');
                return false;
            }

            // スタッフID
            staff_code = $("#staff_code_" + i).val();
            if (staff_code == '') {
                alert('「担当者」を入力してください。');
                return false;
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
        margin-bottom: 3rem !important;
    }

    .file-control {
        width: 100%;
        height: calc(1.6em + 0.75rem + 2px);
        padding: 0.375rem 0.75rem;
    }

    .column-label {
        font-size: 0.9em;
        font-weight: bold;
    }

    #standard_add_btn {
        margin: 10px auto 0px;
    }

    #standart_list_area {
        width: 100%;
    }

    .standard_list td {
        width: 10%;
    }

    .standard_list td:first-of-type {
        width: 90%;
    }

    .standard_del_btn {
        margin: auto 5px;
    }

    .supply-from-table {
        width: 100%;
        margin-bottom: 50px;
    }

    .register-btn-table {
        width: 100%;
    }

    .product-table {
        width: 100%;
    }

    .add-product-btn-area {
        text-align: right;
        padding: 0px 0px 20px;
    }

    .add-slip-btn {
        min-width: 100px;
        background-color: #e3342f!important;
        border-color: #e3342f!important;
    }

    .remove-product-btn {
        height: calc(2rem + 6px)!important;
        width: 100%;
    }

    .total-table {
        width: 100%;
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

    .width-50 {
        width: 50%!important;
        text-align: center;
    }

    #register-btn {
        width: 80%;
        height: 40px;
    }

    .partition-area {
        width: 100%;
        height: 1.0em;
    }

    .apply_to_box {
        width: 100%;
        padding-top: 3%;
    }
</style>
