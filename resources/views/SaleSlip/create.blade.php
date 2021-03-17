@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">売上伝票 新規成画面</div>

        <form class="smn-form" id="sale-slip-create-form" method="post" action="./registerSaleSlips" enctype="multipart/form-data" onsubmit="return inputCheck();">
            {{ csrf_field() }}
            <div class="form-group">
                <div class="sale_date_box">
                    <label class="column-label" for="sale_date">売上日付</label>
                    <input type="date" class="form-control " id="sale_date " name="data[SaleSlip][sale_date]" value="<?php echo date('Y-m-d');?>">
                </div>

                <div class="delivery_date_box">
                    <label class="column-label" for="delivery_date">納品日付</label>
                    <input type="date" class="form-control " id="delivery_date " name="data[SaleSlip][delivery_date]" value="<?php echo date('Y-m-d');?>">
                </div>
            </div>


            <table class="sale-from-table">
                <tr>
                    <th colspan="2">売上企業</th>
                    <th colspan="2">売上店舗</th>
                </tr>
                <tr>
                    <td class="width-20">
                        <input type="text" class="form-control sale_company_code_input" id="sale_company_code" name="data[SaleSlip][sale_company_code]" tabindex="1">
                        <input type="hidden" id="sale_company_id" name="data[SaleSlip][sale_company_id]">
                    </td>
                    <td class="width-30">
                        <input type="text" class="form-control" id="sale_company_text" name="data[SaleSlip][sale_company_text]" readonly>
                    </td>
                    <td class="width-20">
                        <input type="text" class="form-control sale_shop_code_input" id="sale_shop_code" name="data[SaleSlip][sale_shop_code]" tabindex="2">
                        <input type="hidden" id="sale_shop_id" name="data[SaleSlip][sale_shop_id]">
                    </td>
                    <td class="width-30">
                        <input type="text" class="form-control" id="sale_shop_text" name="data[SaleSlip][sale_shop_text]" readonly>
                    </td>
                </tr>

            </table>

            <!--<div class="add-slip-btn-area">
                <button id="add-slip-btn" type="button" class="btn add-slip-btn btn-primary">伝票追加</button>
                <input type='hidden' name="slip_num" id="slip_num" value="1">
            </div>-->

            <table class="slip-table">
                <tr>
                    <th colspan="2">製品ID</th>
                    <th colspan="2">個数</th>
                    <th colspan="2">産地</th>
                    <th>税率</th>
                    <th rowspan="4">削除</th>
                </tr>
                <tr>
                    <th colspan="2">規格</th>
                    <th colspan="2">数量</th>
                    <th colspan="2">担当</th>
                    <th>セリNO.</th>
                </tr>
                <tr>
                    <th colspan="2">品質</th>
                    <th colspan="2">単価</th>
                    <th colspan="2">摘要</th>
                </tr>
                <!--<tr>
                    <th colspan="2">製品ID</th>
                    <th colspan="2">単価</th>
                    <th colspan="2">担当</th>
                    <th>税率</th>
                    <th rowspan="4">削除</th>
                </tr>
                <tr>
                    <th colspan="2">規格</th>
                    <th colspan="2">数量</th>
                    <th colspan="3">発注数量</th>
                </tr>
                <tr>
                    <th colspan="2">品質</th>
                    <th colspan="2">金額</th>
                    <th>対応仕入</th>
                    <th>対象件数</th>
                    <th>対象数量</th>
                </tr>
                <tr>
                    <th colspan="7">摘要</th>
                </tr>-->
                <tr id="slip-partition-0" class="partition-area"></tr>

                <tr id="slip-upper-0">
                    <td class="width-10">
                        <input type="text" class="form-control product_code_input" id="product_code_0" name="data[SaleSlipDetail][0][product_code]" tabindex="3">
                        <input type="hidden" id="product_id_0" name="data[SaleSlipDetail][0][product_id]">
                    </td>
                    <td class="width-20">
                        <input type="text" class="form-control" id="product_text_0" name="data[SaleSlipDetail][0][product_text]" placeholder="製品欄" readonly>
                    </td>
                    <!--<td class="width-15" colspan="2">
                        <input type="number" class="form-control" id="unit_price_0" name="data[SaleSlipDetail][0][unit_price]" onKeyUp='javascript:priceNumChange(0)' tabindex="4">
                    </td>-->
                    <td>
                        <input type="number" class="form-control" id="inventory_unit_num_0" name="data[SaleSlipDetail][0][inventory_unit_num]" tabindex="4">
                    </td>
                    <td>
                        <input type="text" class="form-control" id="inventory_unit_text_0" name="data[SaleSlipDetail][0][inventory_unit_text]" placeholder="個数欄" readonly>
                        <input type="hidden" id="inventory_unit_id_0" name="data[SaleSlipDetail][0][inventory_unit_id]" value="0">
                    </td>
                    <td class="width-10">
                        <input type="text" class="form-control staff_code_input" id="staff_code_0" name="data[SaleSlipDetail][0][staff_code]" tabindex="7">
                        <input type="hidden" id="staff_id_0" name="data[SaleSlipDetail][0][staff_id]">
                    </td>
                    <td class="width-20">
                        <input type="text" class="form-control" id="staff_text_0" name="data[SaleSlipDetail][0][staff_text]" placeholder="担当欄" readonly>
                    </td>
                    <td class="width-15">
                        <input type="text" class="form-control" id="tax_text_0" name="data[SaleSlipDetail][0][tax_text]" placeholder="税率欄" readonly>
                        <input type='hidden' id='tax_id_0' name="data[SaleSlipDetail][0][tax_id]" value="0">
                    </td>
                    <td rowspan="4" class="width-5">
                        <button id="remove-slip-btn" type="button" class="btn remove-slip-btn btn-secondary" onclick='javascript:removeSlip(0) '>削除</button>
                    </td>
                </tr>

                <tr id="slip-middle-0">
                    <td>
                        <input type="text" class="form-control standard_code_input" id="standard_code_0" name="data[SaleSlipDetail][0][standard_code]">
                        <input type="hidden" id="standard_id_0" name="data[SaleSlipDetail][0][standard_id]">
                    </td>
                    <td>
                        <input type="text" class="form-control" id="standard_text_0" name="data[SaleSlipDetail][0][standard_text]" placeholder="規格欄" readonly>
                    </td>
                    <!--<td>
                        <input type="number" class="form-control" id="unit_num_0" name="data[SaleSlipDetail][0][unit_num]" onKeyUp='javascript:priceNumChange(0)' tabindex="5">
                    </td>
                    <td>
                        <input type="text" class="form-control" id="unit_text_0" name="data[SaleSlipDetail][0][unit_text]" readonly>
                        <input type="hidden" id="unit_id_0" name="data[SaleSlipDetail][0][unit_id]" value="0">
                    </td>
                    <td>
                        <input type="number" class="form-control" id="inventory_unit_num_0" name="data[SaleSlipDetail][0][inventory_unit_num]" tabindex="5">
                    </td>
                    <td>
                        <input type="text" class="form-control" id="inventory_unit_text_0" name="data[SaleSlipDetail][0][inventory_unit_text]" placeholder="個数欄" readonly>
                        <input type="hidden" id="inventory_unit_id_0" name="data[SaleSlipDetail][0][inventory_unit_id]" value="0">
                    </td>-->
                    <td>
                        <input type="number" class="form-control" id="unit_num_0" name="data[SaleSlipDetail][0][unit_num]" onKeyUp='javascript:priceNumChange(0)' tabindex="5">
                    </td>
                    <td>
                        <input type="text" class="form-control" id="unit_text_0" name="data[SaleSlipDetail][0][unit_text]" placeholder="数量欄" readonly>
                        <input type="hidden" id="unit_id_0" name="data[SaleSlipDetail][0][unit_id]" value="0">
                    </td>
                    <!--<td colspan="2">
                        <input type="number" class="form-control" id="inventory_unit_num_0" name="data[SaleSlipDetail][0][inventory_unit_num]" tabindex="7">
                    </td>
                    <td>
                        <input type="text" class="form-control" id="inventory_unit_text_0" name="data[SaleSlipDetail][0][inventory_unit_text]" readonly>
                        <input type="hidden" id="inventory_unit_id_0" name="data[SaleSlipDetail][0][inventory_unit_id]" value="0">
                    </td>-->
                    <td>
                        <input type="button" class="form-control btn btn-primary" id="supply_sale_slip_btn_0" value="対応仕入" onclick='javascript:showSupplySaleModal(0)'>
                    </td>
                    <td>
                        <input type="text" class="form-control" id="sale_supply_slip_count_0" name="data[SaleSlipDetail][0][supply_count]" value="0" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control" id="sale_supply_slip_unit_num_0" name="data[SaleSlipDetail][0][supply_unit_num]" value="0" readonly>
                    </td>
                </tr>

                <tr id="slip-lower-0">
                    <td>
                        <input type="text" class="form-control quality_code_input" id="quality_code_0" name="data[SaleSlipDetail][0][quality_code]">
                        <input type="hidden" id="quality_id_0" name="data[SaleSlipDetail][0][quality_id]">
                    </td>
                    <td>
                        <input type="text" class="form-control" id="quality_text_0" name="data[SaleSlipDetail][0][quality_text]" placeholder="品質欄" readonly>
                    </td>
                    <!--<td colspan="2">
                        <input type="text" class="form-control" id="notax_price_0" name="data[SaleSlipDetail][0][notax_price]" value="0" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control" id="unit_num_0" name="data[SaleSlipDetail][0][unit_num]" onKeyUp='javascript:priceNumChange(0)' tabindex="6">
                    </td>
                    <td>
                        <input type="text" class="form-control" id="unit_text_0" name="data[SaleSlipDetail][0][unit_text]" placeholder="数量欄" readonly>
                        <input type="hidden" id="unit_id_0" name="data[SaleSlipDetail][0][unit_id]" value="0">
                    </td>-->
                    <!--<td>
                        <input type="button" class="form-control btn btn-primary" id="supply_sale_slip_btn_0" value="対応仕入" onclick='javascript:showSupplySaleModal(0)'>
                    </td>
                    <td>
                        <input type="text" class="form-control" id="sale_supply_slip_count_0" name="data[SaleSlipDetail][0][supply_count]" value="0" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control" id="sale_supply_slip_unit_num_0" name="data[SaleSlipDetail][0][supply_unit_num]" value="0" readonly>
                    </td>-->
                    <td class="width-15" colspan="2">
                        <input type="number" class="form-control" id="unit_price_0" name="data[SaleSlipDetail][0][unit_price]" onKeyUp='javascript:priceNumChange(0)' tabindex="6">
                    </td>
                    <td colspan="3">
                        <input type="text" class="form-control" id="memo_0" name="data[SaleSlipDetail][0][memo]" placeholder="摘要欄" tabindex="8">
                    </td>
                </tr>

                <tr id="slip-most-lower-0">
                    <td>
                        小計
                    </td>
                    <td colspan="3">
                        <input type="text" class="form-control" id="notax_price_0" name="data[SaleSlipDetail][0][notax_price]" value="0" readonly>
                    </td>
                    <!--<td colspan="7">
                        <input type="text" class="form-control" id="memo_0" name="data[SaleSlipDetail][0][memo]" tabindex="8">
                    </td>-->
                </tr>

            </table>
            <div id="supply-slip-area">
                <div id="supply-slip-area-0"></div>
            </div>
            <br><br>
            <div class="add-slip-btn-area">
                <button id="add-slip-btn" type="button" class="btn add-slip-btn btn-primary">伝票追加</button>
                <input type='hidden' name="slip_num" id="slip_num" value="1">
            </div>

            <table class="total-table">
                <tr>
                    <th>8%対象額</th>
                    <th>8%税額</th>
                    <th>8%税込額</th>
                </tr>
                <tr>
                    <td><input type="tel" class="form-control" id="notax_sub_total_8" name="data[SaleSlip][notax_sub_total_8]" value='0' readonly></td>
                    <td><input type="tel" class="form-control" id="tax_total_8" name="data[SaleSlip][tax_total_8]" value='0' readonly></td>
                    <td><input type="tel" class="form-control" id="sub_total_8" name="data[SaleSlip][sub_total_8]" value='0' readonly></td>
                </tr>
                <tr>
                    <th>10%対象額</th>
                    <th>10%税額</th>
                    <th>10%税込額</th>
                </tr>
                <tr>
                    <td><input type="tel" class="form-control" id="notax_sub_total_10" name="data[SaleSlip][notax_sub_total_10]" value='0' readonly></td>
                    <td><input type="tel" class="form-control" id="tax_total_10" name="data[SaleSlip][tax_total_10]" value='0' readonly></td>
                    <td><input type="tel" class="form-control" id="sub_total_10" name="data[SaleSlip][sub_total_10]" value='0' readonly></td>
                </tr>
                <tr>
                    <th>税抜小計</th>
                    <th>税額</th>
                    <th>税込小計</th>
                </tr>
                <tr>
                    <td><input type="tel" class="form-control" id="notax_sub_total" name="data[SaleSlip][notax_sub_total]" value='0' readonly></td>
                    <td><input type="tel" class="form-control" id="tax_total" name="data[SaleSlip][tax_total]" value='0' readonly></td>
                    <td><input type="tel" class="form-control" id="sub_total" name="data[SaleSlip][sub_total]" value='0' readonly></td>
                </tr>
                <tr>
                    <th>配送コード</th>
                    <th>配送名</th>
                    <th>配送税込額</th>
                </tr>
                <tr>
                    <td>
                        <input type="text" class="form-control delivery_code_input" id="delivery_code" name="data[SaleSlip][delivery_code]">
                        <input type="hidden" id="delivery_id" name="data[SaleSlip][delivery_id]">
                    </td>
                    <td><input type="text" class="form-control" id="delivery_text" name="data[SaleSlip][delivery_text]" readonly></td>
                    <td><input type="tel" class="form-control" id="delivery_price" name="data[SaleSlip][delivery_price]" value='0' onKeyUp='javascript:adjustPrice()'></td>
                </tr>
                <tr>
                    <th>調整額</th>
                    <th colspan="2">調整後税込額</th>
                </tr>
                <tr>
                    <td><input type="tel" class="form-control" id="adjust_price" name="data[SaleSlip][adjust_price]" value='0' onKeyUp='javascript:adjustPrice()'></td>
                    <td colspan="2"><input type="tel" class="form-control" id="total" name="data[SaleSlip][total]" value='0' readonly></td>
                </tr>
            </table>
            <br><br>
            <div class="form-group">
                <label class="column-label" for="remarks">備考欄</label>
                <textarea id="remarks" class="form-control" name="data[SaleSlip][remarks]" rows="4" cols="40"></textarea>
            </div>


            <br>
            <br>
            <table class="register-btn-table">
                <tr>
                    <td class="width-20">
                        <input type="text" class="form-control" id="sale_submit_type" name="data[SaleSlip][sale_submit_type]" value="1">
                    </td>
                    <td class="width-30">
                        <input type="text" class="form-control" id="sale_submit_type_text" name="data[SaleSlip][sale_submit_type_text]" value="登録" readonly>
                    </td>
                    <td class="width-50">
                        <button id="register-btn" class="register-btn btn btn-primary" type="button">登録</button>
                    </td>
                </tr>
            </table>

            <input type="hidden" name="submit_type" id="submit_type" value="1">
        </form>

        <!-- モーダル -->
        <div class="modal fade" id="sale_supply_slip_modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4>
                            <div class="modal-title" id="myModalLabel">売上対象の仕入れを選択してください</div>
                        </h4>
                    </div>
                    <div class="modal-body text-left" id="modal_body_area">
                        <div class="product-area" id="modal_product_area"></div>
                        <table class="modal-table" id="modal_table_area">
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="modal-submit">決定</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">選択をやめる</button>
                        <input type="hidden" id="sale_slip_num" value="">
                    </div>
                </div>
            </div>
        </div>

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

                    if (this_id == "delivery_code") { // 配送コードの場合

                        var this_val = $('#delivery_price').val();
                        $('#delivery_price').val("");
                        $('#delivery_price').focus();
                        $('#delivery_price').val(this_val);

                    } else if (this_id == "delivery_price") { // 配送額の場合

                        var this_val = $('#adjust_price').val();
                        $('#adjust_price').val("");
                        $('#adjust_price').focus();
                        $('#adjust_price').val(this_val);

                    } else if (this_id == "adjust_price") { // 調整額の場合

                        var this_val = $('#remarks').val();
                        $('#remarks').val("");
                        $('#remarks').focus();
                        $('#remarks').val(this_val);

                    } else if (this_id == "sale_submit_type") {

                        var submit_type = $(this).val();

                        if (submit_type == 1) {
                            $('#sale_submit_type_text').val("登録");
                            $('#register-btn').focus();
                        } else if (submit_type == 2) {
                            $('#sale_submit_type_text').val("一時保存");
                            $('#register-btn').focus();
                        } else {
                            alert("存在しない登録番号です。");
                        }

                    } else {

                        // 現在のtabIndex取得
                        var tabindex = parseInt($(this).attr('tabindex'), 10);
                        if (isNaN(tabindex)) return false;

                        // ひとつ前のタブの最小値を取得
                        var min = 0;
                        $("#sale-slip-create-form [tabindex]").attr("tabindex", function(a, b) {

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

                } else if (event.keyCode === 47) { // スラッシュが押された時

                    var this_id = $(this).attr('id');

                    if (this_id == "delivery_code") { // 配送コードの場合

                        var max = -1;
                        $("#sale-slip-create-form [tabindex]").attr("tabindex", function(a, b) {
                            max = Math.max(max, +b);
                        });

                        if (max != -1) {

                            var this_val = $('input[tabindex="' + max + '"]').val();
                            $('input[tabindex="' + max + '"]').val("");
                            $('input[tabindex="' + max + '"]').focus();
                            $('input[tabindex="' + max + '"]').val(this_val);

                        }

                    } else if (this_id == "delivery_price") { // 配送額の場合

                        var this_val = $('#delivery_code').val();
                        $('#delivery_code').val("");
                        $('#delivery_code').focus();
                        $('#delivery_code').val(this_val);

                    } else if (this_id == "adjust_price") { // 調整額の場合

                        var this_val = $('#delivery_price').val();
                        $('#delivery_price').val("");
                        $('#delivery_price').focus();
                        $('#delivery_price').val(this_val);

                    } else if (this_id == "sale_submit_type") { // 登録タイプの場合

                        var this_val = $('#remarks').val();
                        $('#remarks').val("");
                        $('#remarks').focus();
                        $('#remarks').val(this_val);

                    } else {

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
                    }

                    return false;
                } else if (event.keyCode === 43) { // プラスが押された時

                    var this_id = $(this).attr('id');

                    if (this_id.match(/memo/)) {

                        $('#add-slip-btn').trigger('click');

                        return false;
                    }

                } else if (event.keyCode === 42) { // *が押された時

                    var this_val = $('#sale_submit_type').val();
                    $('#sale_submit_type').val("");
                    $('#sale_submit_type').focus();
                    $('#sale_submit_type').val(this_val);

                    return false;
                }

            });

            //-------------------------------------
            // 備考欄
            //-------------------------------------

            $(document).on("keypress", "#remarks", function(event) {

                if (event.keyCode === 47) { // マイナスが押された時

                    var this_val = $('#adjust_price').val();
                    $('#adjust_price').val("");
                    $('#adjust_price').focus();
                    $('#adjust_price').val(this_val);

                    return false;
                } else if (event.keyCode === 43) { // プラスが押された時

                    var this_val = $('#sale_submit_type').val();
                    $('#sale_submit_type').val("");
                    $('#sale_submit_type').focus();
                    $('#sale_submit_type').val(this_val);

                    return false;
                }
            });

            //-------------------------------------
            // 登録ボタン
            //-------------------------------------

            $(document).on("keypress", "#register-btn", function(event) {

                if (event.keyCode === 47) { // マイナスが押された時

                    var this_val = $('#sale_submit_type').val();
                    $('#sale_submit_type').val("");
                    $('#sale_submit_type').focus();
                    $('#sale_submit_type').val(this_val);

                    return false;
                }
            });


            //-------------------------------------
            // フォーカスアウトしたときの処理
            //-------------------------------------
            $(document).on("blur", "input", function(event) {

                var tabindex = parseInt($(this).attr('tabindex'), 10);
                var set_val = $(this).val();
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

                            // 税の設定をする
                            var selector_tax_id = selector_id.replace('product_', 'tax_');
                            var selector_tax_text = selector_text.replace('product_', 'tax_');
                            $("#" + selector_tax_id).val(data[3]);
                            $("#" + selector_tax_text).val(data[4]);

                            // 単位の設定をする
                            var selector_unit_id = selector_id.replace('product_', 'unit_');
                            var selector_unit_text = selector_text.replace('product_', 'unit_');
                            $("#" + selector_unit_id).val(data[5]);
                            $("#" + selector_unit_text).val(data[6]);

                            // 仕入単位
                            var selector_inventory_unit_id = selector_id.replace('product_', 'inventory_unit_');
                            var selector_inventory_unit_text = selector_text.replace('product_', 'inventory_unit_');
                            $("#" + selector_inventory_unit_id).val(data[7]);
                            $("#" + selector_inventory_unit_text).val(data[8]);
                            if (data[8] == '' || data[8] == null) {
                                var selector_inventory_unit_num = selector_id.replace('product_id_', 'inventory_unit_num_');
                                $("#" + selector_inventory_unit_num).attr('readonly', true);
                            } else {
                                var selector_inventory_unit_num = selector_id.replace('product_id_', 'inventory_unit_num_');
                                $("#" + selector_inventory_unit_num).attr('readonly', false);
                            }

                            // 金額設定
                            var selector_unit_price = selector_id.replace('product_id_', 'unit_price_');
                            var selector_unit_num = selector_id.replace('product_id_', 'unit_num_');

                            // 製品が変わった場合は再計算
                            if (before_product_id != data[1]) {

                                $("#" + selector_unit_price).val('');
                                $("#" + selector_unit_num).val('');
                                priceNumChange(parseInt(selector_id.replace('product_id_', ''), 10));
                            }

                        });

                } else if (selector_code.match(/standard_code/)) { // 規格IDの部分

                    var this_selector_id = $(this).attr('id');
                    var this_id = this_selector_id.replace('standard_code_', '');
                    var product_id = $("#product_id_" + this_id).val();
                    fd.append("productId", product_id);

                    $.ajax({
                            headers: {
                                "X-CSRF-TOKEN": $("[name='_token']").val()
                            },
                            url: "./AjaxSetStandard",
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

                            $("#" + selector_text).val(data[2]);
                        });

                } else if (selector_code.match(/quality_code/)) { // 品質IDの部分

                    $.ajax({
                            headers: {
                                "X-CSRF-TOKEN": $("[name='_token']").val()
                            },
                            url: "./AjaxSetQuality",
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

                } else if (selector_code.match(/origin/)) { // 産地IDの部分

                    $.ajax({
                            headers: {
                                "X-CSRF-TOKEN": $("[name='_token']").val()
                            },
                            url: "./AjaxSetOriginArea",
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
                } else if (selector_code.match(/delivery_code/)) { // 配送IDの部分

                    $.ajax({
                            headers: {
                                "X-CSRF-TOKEN": $("[name='_token']").val()
                            },
                            url: "./AjaxSetDelivery",
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
            // autocomplete処理 規格ID
            //-------------------------------------
            $(".standard_code_input").autocomplete({
                source: function(req, resp) {

                    var selector_id = this.bindings[0].id;
                    var this_id = selector_id.replace('standard_code_', '');
                    var product_id = $("#product_id_" + this_id).val();

                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./AjaxAutoCompleteStandard",
                        type: "POST",
                        cache: false,
                        dataType: "json",
                        data: {
                            productId: product_id,
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
            // autocomplete処理 品質ID
            //-------------------------------------
            $(".quality_code_input").autocomplete({
                source: function(req, resp) {
                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./AjaxAutoCompleteQuality",
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
            // autocomplete処理 産地ID
            //-------------------------------------
            $(".origin_area_code_input").autocomplete({
                source: function(req, resp) {
                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./AjaxAutoCompleteOriginArea",
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
            // autocomplete処理 産地ID
            //-------------------------------------
            $(".delivery_code_input").autocomplete({
                source: function(req, resp) {
                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./AjaxAutoCompleteDelivery",
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
            $("#add-slip-btn").on('click', function() {

                // 伝票ナンバーを取得
                var slip_num = $("#slip_num").val();

                var fd = new FormData();
                fd.append("slip_num", slip_num); // 押されたボタンID

                $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $("[name='_token']").val()
                        },
                        url: "./SaleSlipAjaxAddSlip",
                        type: "POST",
                        dataType: "JSON",
                        data: fd,
                        processData: false,
                        contentType: false
                    })
                    .done(function(data) {

                        // 伝票ナンバーを取得
                        $("#slip_num").val(data[0]);

                        // 伝票追加
                        $(".slip-table").append(data[1]);

                        // 仕入伝票格納エリア
                        $("#supply-slip-area").append(data[2]);

                        // 製品ID
                        let product_code_selector = $(data[3]).autocomplete({
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
                        $("#product-code-area-" + slip_num).append(product_code_selector);

                        // 規格ID
                        let standard_code_selector = $(data[4]).autocomplete({
                            source: function(req, resp) {

                                var product_id = $("#product_id_" + slip_num).val();

                                $.ajax({
                                    headers: {
                                        "X-CSRF-TOKEN": $("[name='_token']").val()
                                    },
                                    url: "./AjaxAutoCompleteStandard",
                                    type: "POST",
                                    cache: false,
                                    dataType: "json",
                                    data: {
                                        productId: product_id,
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
                        $("#standard-code-area-" + slip_num).append(standard_code_selector);

                        // 品質ID
                        let quality_code_selector = $(data[5]).autocomplete({
                            source: function(req, resp) {
                                $.ajax({
                                    headers: {
                                        "X-CSRF-TOKEN": $("[name='_token']").val()
                                    },
                                    url: "./AjaxAutoCompleteQuality",
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
                        $("#quality-code-area-" + slip_num).append(quality_code_selector);

                        // 担当ID
                        let staff_code_selector = $(data[6]).autocomplete({
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
                        $("#staff-code-area-" + slip_num).append(staff_code_selector);

                    })
                    .fail(function(XMLHttpRequest, textStatus, errorThrown) {
                        alert(XMLHttpRequest);
                        alert(textStatus);
                        alert(errorThrown);
                        // 送信失敗
                        alert("失敗しました。");
                    });
            });

            //-------------------------------------
            // Enterと-を押したときにタブ移動する処理
            //-------------------------------------
            $(document).on("click", ".register-btn", function() {

                var this_val = $("#sale_submit_type").val();

                if (this_val == "1") {
                    $('#sale-slip-create-form').submit();
                } else if (this_val == "2") {
                    $('#sale-slip-create-form').submit();
                } else {
                    return false;
                }
            });
        });
    })(jQuery);

    function priceNumChange(this_slip_num) {

        // まず入力された売上詳細の金額を計算する
        var this_unit_price = $("#unit_price_" + this_slip_num).val();
        var this_unit_num = $("#unit_num_" + this_slip_num).val();

        if (!this_unit_price) this_unit_price = 0;
        if (!this_unit_num) this_unit_num = 0;

        var this_calc_price = this_unit_price * this_unit_num;
        $("#notax_price_" + this_slip_num).val(this_calc_price);


        // 伝票ナンバーを取得(最大値)
        var slip_max_num = $("#slip_num").val();

        // 更新対象のproduct_idを取得
        var unit_price = 0;
        var unit_num = 0;
        var calc_price = 0;
        var tax_id = 0;

        notax_sub_total_8 = 0;
        tax_total_8 = 0;
        sub_total_8 = 0;

        notax_sub_total_10 = 0;
        tax_total_10 = 0;
        sub_total_10 = 0;

        notax_sub_total = 0;
        tax_total = 0;
        sub_total = 0;

        // 配送額
        delivery_price = parseInt($("#delivery_price").val(), 10);
        if (!delivery_price) delivery_price = 0;

        // 調整額取得
        adjust_price = parseInt($("#adjust_price").val(), 10);
        if (!adjust_price) adjust_price = 0;
        total = 0;

        // ループ処理をする
        for (var slip_num = 0; slip_num < slip_max_num; slip_num++) {

            unit_price = $("#unit_price_" + slip_num).val();
            unit_num = $("#unit_num_" + slip_num).val();

            if (!unit_price) unit_price = 0;
            if (!unit_num) unit_num = 0;

            calc_price = unit_price * unit_num;

            // 税額を取得
            tax_id = $("#tax_id_" + slip_num).val();

            if (tax_id == 1) { // 8%の場合

                // 計算値を算入
                notax_sub_total_8 += calc_price;
                // 税額計算
                tax_total_8 += Math.round(calc_price * 0.08);
                // 税込額計算
                sub_total_8 = notax_sub_total_8 + tax_total_8;

            } else if (tax_id == 2) { // 10%の場合

                // 計算値を算入
                notax_sub_total_10 += calc_price;
                // 税額計算
                tax_total_10 += Math.round(calc_price * 0.1);
                // 税込額計算
                sub_total_10 = notax_sub_total_10 + tax_total_10;
            }
        }



        // 計算値を算入
        notax_sub_total = notax_sub_total_8 + notax_sub_total_10;
        tax_total = tax_total_8 + tax_total_10;
        sub_total = notax_sub_total + tax_total;

        // 調整後金額を取得
        total = sub_total + delivery_price + adjust_price;


        // 各課税額に入れる
        $("#notax_sub_total_8").val(notax_sub_total_8);
        $("#tax_total_8").val(tax_total_8);
        $("#sub_total_8").val(sub_total_8);

        $("#notax_sub_total_10").val(notax_sub_total_10);
        $("#tax_total_10").val(tax_total_10);
        $("#sub_total_10").val(sub_total_10);

        $("#notax_sub_total").val(notax_sub_total);
        $("#tax_total").val(tax_total);
        $("#sub_total").val(sub_total);

        $("#adjust_price").val(adjust_price);
        $("#total").val(total);

    }

    function productIdChange(slip_num) {

        // 更新対象のproduct_idを取得
        var selected_product_id = $("#product_id_" + slip_num).val();

        var fd = new FormData();
        fd.append("slip_num", slip_num);
        fd.append("selected_product_id", selected_product_id);

        $.ajax({
                headers: {
                    "X-CSRF-TOKEN": $("[name='_token']").val()
                },
                url: "./SaleSlipAjaxChangeProductId",
                type: "POST",
                dataType: "JSON",
                data: fd,
                processData: false,
                contentType: false
            })
            .done(function(data) {

                // 規格を変更
                $("#standard_id_" + slip_num).remove();
                $("#slip-standard-" + slip_num).append(data[0]);
                // 税率のhiddenエリアに格納
                $("#tax_id_" + slip_num).val(data[1]);
                // 税率の名称エリア
                $("#tax_name_" + slip_num).val(data[2]);

            })
            .fail(function(XMLHttpRequest, textStatus, errorThrown) {
                alert(XMLHttpRequest);
                alert(textStatus);
                alert(errorThrown);
                // 送信失敗
                alert("失敗しました。");
            });

        // 再計算
        priceNumChange(slip_num);

    }

    function removeSlip(remove_num) {

        // 削除
        $("#slip-partition-" + remove_num).remove();
        $("#slip-upper-" + remove_num).remove();
        $("#slip-middle-" + remove_num).remove();
        $("#slip-lower-" + remove_num).remove();
        $("#slip-most-lower-" + remove_num).remove();
        $(".use_num_" + remove_num).remove();
        $(".supply_slip_id_" + remove_num).remove();

        // 再計算
        priceNumChange(remove_num);

    }

    //-----------------------
    // 調整額入力時の処理
    //-----------------------
    function adjustPrice() {

        // 配送額
        delivery_price = parseInt($("#delivery_price").val(), 10);
        if (!delivery_price) delivery_price = 0;

        // 調整額取得
        adjust_price = parseInt($("#adjust_price").val(), 10);
        if (!adjust_price) adjust_price = 0;

        total = sub_total + delivery_price + adjust_price;

        // 調整後税込額
        $("#total").val(total);
    }

    //-----------------------
    // 対応仕入入力
    //-----------------------
    function showSupplySaleModal(slip_num) {

        // 対象product_idを取得
        var product_id = $("#product_id_" + slip_num).val();

        // 数量を取得
        var unit_num = $("#unit_num_" + slip_num).val();

        if (isNaN(product_id) || product_id == "") {

            alert("製品が選択されておりません。");
            return;
        }

        if (isNaN(unit_num) || unit_num == "") {

            alert("売上数量を先に入力してください。");
            return;
        }

        var use_num_arr = [];
        var supply_slip_id_arr = [];
        var use_num;
        var use_num_count = 0;
        var use_num_total = 0;

        // すでに取得済みのデータを取得
        $(".use_num_" + slip_num).each(function() {

            use_num_arr.push($(this).val());
        });

        $(".supply_slip_id_" + slip_num).each(function() {

            supply_slip_id_arr.push($(this).val());
        });

        var fd = new FormData();
        fd.append("slip_num", slip_num);
        fd.append("product_id", product_id);
        fd.append("use_num_arr", use_num_arr);
        fd.append("supply_slip_id_arr", supply_slip_id_arr);
        fd.append("action", 'create');

        $.ajax({
                headers: {
                    "X-CSRF-TOKEN": $("[name='_token']").val()
                },
                url: "./AjaxShowSupplySlip",
                type: "POST",
                dataType: "JSON",
                data: fd,
                processData: false,
                contentType: false
            })
            .done(function(data) {

                $("#modal_product_area").remove();
                $("#modal_table_area").remove();
                $("#sum-area").remove();
                $("#modal_body_area").append(data[0]);
                $("#sale_slip_no").val(slip_num);
                $("#sale_supply_slip_modal").modal("show") //モーダル出現
            });
    }

    //-----------------------
    // チェックボックスをチェック、利用数を変更したときの処理
    //-----------------------
    $(document).on('change', '.modal-checkbox', function() {

        changeModalCount();
    });

    $(document).on('keyup', '.modal-sale-num', function() {

        changeModalCount();
    });

    function changeModalCount() {
        var checkbox_id;
        var use_num_id;
        var use_num;
        var use_num_count = 0;
        var use_num_total = 0;
        var remain_num = 0;

        // 数量を取得
        var slip_num = $("#sale_slip_no").val();
        var unit_num = parseInt($("#unit_num_" + slip_num).val(), 10);

        // 全てのチェックボックスを処理する
        $('.modal-checkbox').each(function(i, elem) {

            // チェックされているボックスの場合
            if (elem.checked) {

                // チェックされているIDを取得
                checkbox_id = elem.id;
                // remain_num_idを取得
                remain_num_id = checkbox_id.replace('checkbox_', 'remain_num_');
                // use_numのIDを取得
                use_num_id = checkbox_id.replace('checkbox_', 'use_num_');
                // 在庫数を取得
                remain_num = parseInt($('#' + remain_num_id).val(), 10);
                // use_numに入力されている値を取得
                use_num = parseInt($('#' + use_num_id).val(), 10);

                if (remain_num < use_num) { // 在庫数を超えている数が指定されている場合はアラート出して終了する

                    alert("在庫数を超える数を指定することはできません。");
                    $('#' + checkbox_id).removeAttr('checked').prop('checked', false).change();
                    $('#' + use_num_id).val(remain_num);


                } else if (isNaN(use_num) || !Number.isFinite(use_num)) {

                    alert("数値以外のものが入力されている箇所があります。");
                    $('#' + checkbox_id).removeAttr('checked').prop('checked', false).change();
                    $('#' + use_num_id).val(remain_num);

                } else {

                    // 件数カウント
                    use_num_count += 1;
                    // 取得単位数をカウント
                    use_num_total += use_num;
                }

            }
        });

        // 数値変換する
        use_num_total = parseInt(use_num_total, 10);

        // 売上の数量を超えていた場合はアラートを出す
        if (unit_num < use_num_total) {
            alert("売上伝票の数量を超えています。");
        }

        $('#sum_count_area').text(use_num_count);
        $('#sum_unit_num_area').text(use_num_total);
    }

    //------------
    // 入力チェック
    //------------
    function inputCheck() {

        // ----------
        // 変数初期化
        // ----------
        var sale_company_code; // 売上企業
        var sale_shop_code; // 売上店舗
        var product_code; // 製品ID
        var unit_price; // 単価
        var unit_num; // 数量
        var staff_code; // 担当
        var inventory_unit_num // 発注数量

        // -----------
        // 入力チェック
        // -----------
        sale_company_code = $("#sale_company_code").val();
        sale_shop_code = $("#sale_shop_code").val();
        if (sale_company_code == '') {
            alert('「売上企業」を入力してください。');
            return false;
        }

        // 複数データがある場合
        var slip_num = $("#slip_num").val();
        for (i = 0; i < slip_num; i++) {

            // 製品ID
            product_code = $("#product_code_" + i).val();
            if (product_code == '') {
                alert('「製品ID」を入力してください。');
                return false;
            }

            // 単価
            unit_price = $("#unit_price_" + i).val();
            if (unit_price == '') {
                alert('「単価」を入力してください。');
                return false;
            }

            // 数量
            unit_num = $("#unit_num_" + i).val();
            if (unit_num == '') {
                alert('「数量」を入力してください。');
                return false;
            }

            // 担当
            staff_code = $("#staff_code_" + i).val();
            if (staff_code == '') {
                alert('「担当」を入力してください。');
                return false;
            }

        }
    }

    //-----------------------
    // モーダルで決定が押された時の処理
    //-----------------------
    $(document).on('click', '#modal-submit', function() {

        var checkbox_id;
        var use_num_id;
        var supply_slip_id;
        var use_num;
        var use_num_count = 0;
        var use_num_total = 0;


        var sale_slip_no = $("#sale_slip_no").val();
        var use_num_count = $('#sum_count_area').text();
        var use_num_total = $('#sum_unit_num_area').text();
        var html = "";

        var count = 0;

        // 売上数量を取得
        var unit_num = parseInt($("#unit_num_" + sale_slip_no).val(), 10);

        // 数値変換する
        use_num_total = parseInt(use_num_total, 10);

        // 売上の数量を超えていた場合はアラートを出す
        if (unit_num < use_num_total) {
            alert("売上伝票の数量を超えています。");
            return false;
        }

        // 全てのチェックボックスを処理する
        $('.modal-checkbox').each(function(i, elem) {

            // チェックされているボックスの場合
            if (elem.checked) {

                // チェックされているIDを取得
                checkbox_id = elem.id;
                // use_numのIDを取得
                use_num_id = checkbox_id.replace('checkbox_', 'use_num_');
                // use_numに入力されている値を取得
                use_num = parseInt($('#' + use_num_id).val(), 10);
                // 仕入詳細IDを取得
                supply_slip_id = checkbox_id.replace('checkbox_', '');

                html += "<input type='hidden' class='use_num_" + sale_slip_no + "' name='data[InventoryManage][" + sale_slip_no + "][use_num][" + count + "]' value='" + use_num + "'>";
                html += "<input type='hidden' class='supply_slip_id_" + sale_slip_no + "' name='data[InventoryManage][" + sale_slip_no + "][supply_slip_id][" + count + "]' value='" + supply_slip_id + "'>";
                count += 1;
            }
        });

        $(".use_num_" + sale_slip_no + "").remove();
        $(".supply_slip_id_" + sale_slip_no + "").remove();
        $(".use_num_" + sale_slip_no).remove();
        $(".supply_slip_id_" + sale_slip_no).remove();
        $("#supply-slip-area-" + sale_slip_no + "").append(html);
        $("#sale_supply_slip_count_" + sale_slip_no + "").val(use_num_count);
        $("#sale_supply_slip_unit_num_" + sale_slip_no + "").val(use_num_total);
        $("#sale_supply_slip_modal").modal("hide") //モーダル閉じる

    });
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

    .sale-from-table {
        width: 100%;
        margin-bottom: 50px;
    }

    .register-btn-table {
        width: 100%;
    }

    .slip-table {
        width: 100%;
    }

    .add-slip-btn-area {
        text-align: right;
        padding: 0px 0px 40px;
    }

    .add-slip-btn {
        min-width: 100px;
        background-color: #e3342f!important;
        border-color: #e3342f!important;
    }

    .remove-slip-btn {
        height: calc(9.2rem + 6px)!important;
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
    /*modal関連*/

    .modal-dialog {
        width: 90%!important;
        max-width: 1100px!important;
    }

    .modal-header {
        border-bottom: none!important;
    }

    .modal-footer {
        justify-content: unset!important;
    }

    .product-area {
        padding: 0px 1rem 1rem;
        font-size: 18px;
        font-weight: bold;
    }

    .modal-table {
        width: 100%;
    }

    .modal-table th {
        font-size: 14px;
        border: 1px solid #dadada;
        padding: 0.3rem;
        width: 10%;
    }

    .modal-table th:nth-of-type(1) {
        width: 5%;
    }

    .modal-table th:nth-of-type(2) {
        width: 8%;
    }

    .modal-table td {
        font-size: 13px;
        border: 1px solid #dadada;
        padding: 0.3rem;
    }

    .modal-table td:nth-of-type(1) {
        padding-left: 0.5rem;
    }

    .modal-table td:nth-of-type(2) {
        text-align: center;
    }

    .modal-sale-num {
        width: 100%;
        text-align: right;
        padding-right: 0.5rem;
    }

    #sum-area {
        margin-top: 1rem;
        font-size: 15px;
        font-weight: bold;
        letter-spacing: 4px;
    }

    #sum_count_area {
        float: left;
        margin-left: 1.5rem;
    }

    #sum_count_name_area {
        float: left;
    }

    #sum_unit_num_area {
        float: left;
        margin-left: 1.5rem;
    }

    #sum_unit_name_area {
        float: left;
    }

    .delivery_date_box {
        width: 100%;
        padding-top: 3%;
    }
</style>
