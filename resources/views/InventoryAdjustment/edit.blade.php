@extends('layouts.app') @section('content')
<div class="container">
    <div class="row justify-content-center">

    <div class="top-title">在庫調整画面</div>

    <form class="smn-form" method="post" enctype="multipart/form-data" action="./InventoryAdjustmentConfirm" onsubmit="return inputCheck();">
        {{ csrf_field() }}
        {{--  伝票詳細エリア  --}}
        <div class="detail-area">
            <table class="detail-table">
                <tr>
                    <th style="width: 3%;">選択</th>
                    <th style="width: 4%;">伝票ID</th>
                    <th style="width: 7%;">伝票日付</th>
                    <th style="width: 7%;">納品日</th>
                    <th class="width-10">取引先コード</th>
                    <th class="width-10">取引先名</th>
                    <th class="width-5">製品ID</th>
                    <th style="width: 15%;">製品</th>
                    <th class="width-5">規格</th>
                    <th class="width-5">単価</th>
                    <th style="width: 7%;">スタッフ名</th>
                    <th style="width: 6%;">在庫残数</th>
                    <th class="width-5">調整数</th>
                    <th class="width-5">単位</th>
                    <th style="width: 6%;">残金合計</th>
                </tr>
                @foreach ($supplySlipList as $supplySlips)
                    @if ($supplySlips->remaining_quantity > 0)
                    <tr>
                        <input type="hidden" class="supply_slip_detail" name="data[InventoryManage][{{$supplySlips->supply_slip_detail_id}}][supply_slip_detail_id]" value="{{$supplySlips->supply_slip_detail_id}}">
                        <td class="text-center"><input type="checkbox" name="data[InventoryManage][{{$supplySlips->supply_slip_detail_id}}][check]" value="{{$supplySlips->supply_slip_detail_id}}"></td>
                        <td class="text-right">
                            {{$supplySlips->supply_slip_id}}
                            <input type="hidden" name="data[InventoryManage][{{$supplySlips->supply_slip_detail_id}}][supply_slip_id]" value="{{$supplySlips->supply_slip_id}}">
                        </td>
                        <td class="text-center">
                            {{$supplySlips->supply_slip_date}}
                            <input type="hidden" name="data[InventoryManage][{{$supplySlips->supply_slip_detail_id}}][supply_slip_date]" value="{{$supplySlips->supply_slip_date}}">
                        </td>
                        <td class="text-center">
                            {{$supplySlips->delivery_date}}
                            <input type="hidden" name="data[InventoryManage][{{$supplySlips->supply_slip_detail_id}}][delivery_date]" value="{{$supplySlips->delivery_date}}">
                        </td>
                        <td class="text-right">
                            {{$supplySlips->company_code}}
                            <input type="hidden" name="data[InventoryManage][{{$supplySlips->supply_slip_detail_id}}][company_code]" value="{{$supplySlips->company_code}}">
                        </td>
                        <td class="text-center">
                            {{$supplySlips->company_name}}
                            <input type="hidden" name="data[InventoryManage][{{$supplySlips->supply_slip_detail_id}}][company_name]" value="{{$supplySlips->company_name}}">
                        </td>
                        <td class="text-right">
                            {{$supplySlips->product_code}}
                            <input type="hidden" name="data[InventoryManage][{{$supplySlips->supply_slip_detail_id}}][product_code]" value="{{$supplySlips->product_code}}">
                        </td>
                        <td class="text-center">
                            {{$supplySlips->product_name}}
                            <input type="hidden" name="data[InventoryManage][{{$supplySlips->supply_slip_detail_id}}][product_name]" value="{{$supplySlips->product_name}}">
                        </td>
                        <td class="text-center">
                            {{$supplySlips->standard_name}}
                            <input type="hidden" name="data[InventoryManage][{{$supplySlips->supply_slip_detail_id}}][standard_name]" value="{{$supplySlips->standard_name}}">
                        </td>
                        <td class="text-right">
                            {{number_format($supplySlips->unit_price)}}
                            <input type="hidden" name="data[InventoryManage][{{$supplySlips->supply_slip_detail_id}}][unit_price]" value="{{$supplySlips->unit_price}}">
                        </td>
                        <td class="text-center">
                            {{$supplySlips->staff_name}}
                            <input type="hidden" name="data[InventoryManage][{{$supplySlips->supply_slip_detail_id}}][staff_name]" value="{{$supplySlips->staff_name}}">
                        </td>
                        <td class="text-right">
                            {{$supplySlips->remaining_quantity}}
                            <input type="hidden" name="data[InventoryManage][{{$supplySlips->supply_slip_detail_id}}][remaining_quantity]" value="{{$supplySlips->remaining_quantity}}">
                        </td>
                        <td class="text-center">
                            <input type="number" id="inventory-unit-num-{{$supplySlips->supply_slip_detail_id}}" name="data[InventoryManage][{{$supplySlips->supply_slip_detail_id}}][unit_num]">
                        </td>
                        <td class="text-center">
                            {{$supplySlips->unit_name}}
                            <input type="hidden" name="data[InventoryManage][{{$supplySlips->supply_slip_detail_id}}][unit_name]" value="{{$supplySlips->unit_name}}">
                        </td>
                        <td class="text-right">
                            {{number_format($supplySlips->balanced_amount)}}
                            <input type="hidden" name="data[InventoryManage][{{$supplySlips->supply_slip_detail_id}}][balanced_amount]" value="{{$supplySlips->balanced_amount}}">
                        </td>
                    </tr>
                    @endif
                @endforeach
            </table>
        </div>

        <table class="inventory-table">
            <tr>
                <th class="width-10">担当者</th>
                <td>
                    <input type="text" class="form-control staff_code_input" id="staff_code" name="data[InventoryManage][staff_code]" tabindex="1">
                    <input type="hidden" id="staff_id" name="data[InventoryManage][staff_id]">
                </td>
                <td>
                    <input type="text" class="form-control" id="staff_text" name="data[InventoryManage][staff_text]" readonly>
                </td>
            </tr>
            <tr>
                <th>在庫調整理由</th>
                <td>
                    <select class="form-control" name="data[InventoryManage][reason]" tabindex="2">
                        <option value="1">賞味期限切れ</option>
                        <option value="2">紛失</option>
                        <option value="3">その他</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>備考</th>
                <td colspan="5">
                    <textarea class="form-control" id="memo" name="data[InventoryManage][memo]" row="5" tabindex="3" style="margin-top: 0px; margin-bottom: 0px; height: 150px;"></textarea>
                </td>
            </tr>
        </table>

        <div class="button-area">
            <button class="inventory-btn btn btn-primary" type="submit">確認画面</button>
        </div>

    </form>

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
        $(document).on("blur", 'input[name="data[InventoryManage][staff_code]"]', function(event) {

            var tabindex = parseInt($(this).attr('tabindex'), 10);
            var set_val = $(this).val();
            var selector_code = $(this).attr('id');
            var selector_id = selector_code.replace('_code', '_id');
            var selector_text = selector_code.replace('_code', '_text');

            var fd = new FormData();
            fd.append("inputText", set_val);

            if (selector_code.match(/staff/)) { // 担当者IDの部分

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
// 入力チェック
// -----------
function inputCheck() {

    var submit_flg = true;

    // チェックボックスの値を取得
    $('.detail-table input:checked').each(function(){

        // idを取得
        var id = $(this).val();

        // 調整数が入力されているかチェック
        var unit_num = $('#inventory-unit-num-' + id).val();

        // 調整数が入力されていない場合はアラート出す
        if (unit_num == '') {
            alert('調整数が入力されていません。');
            submit_flg = false;
            return false;
        }

        // 担当者が入力されていない場合はアラート出す
        var staff_id = $('#staff_id').val();
        if (staff_id == '') {
            alert('担当者が入力されていません。');
            submit_flg = false;
            return false;
        }

    })

    if (!submit_flg) {
        return false;
    }
}
</script>
<style>
    .top-title {
        max-width: 1300px;
        font-size: 1.4em;
        font-weight: bold;
        width: 90%;
        padding: 25px 0px 25px 20px;
    }

    .smn-form {
        max-width: 1300px;
        width: 100%;
        margin: auto;
    }

    .detail-area {
        width: 100%;
    }

    .detail-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #bbb;
    }

    .inventory-table {
        width: 100%;
        margin-top: 5%;
    }

    .button-area {
        width: 100%;
        text-align: center;
        margin-top: 5%;
    }

    .inventory-btn {
        width: 50%;
    }

    th {
        font-size: 12px;
        text-align: center!important;
        border: 1px solid #bbb;
        margin: 0;
        padding: 0;
    }

    td {
        font-size: 12px;
        border: 1px solid #bbb;
    }

    input[type="number"] {
        width: 70px;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
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

    .width-25 {
        width: 25%!important;
    }

    .width-30 {
        width: 30%!important;
    }

</style>