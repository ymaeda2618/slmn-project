@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">仕入伝票 新規成画面</div>

        <form class="smn-form" id="supply-slip-create-form" method="post" action="./../editRegisterSupplySlips" enctype="multipart/form-data" onsubmit="return inputCheck();">
            {{ csrf_field() }}
            <div class="form-group">
                <div class="supply_date_box">
                    <label class="column-label" for="supply_date">仕入日付</label>
                    <input type="date" class="form-control " id="supply_date " name="data[SupplySlip][supply_date]" value="{{$SupplySlipList->supply_slip_supply_date}}">
                </div>

                <div class="delivery_date_box">
                    <label class="column-label" for="delivery_date">納品日付</label>
                    <input type="date" class="form-control " id="delivery_date " name="data[SupplySlip][delivery_date]" value="{{$SupplySlipList->supply_slip_delivery_date}}">
                </div>
            </div>


            <table class="supply-from-table">
                <tr>
                    <th colspan="2">仕入企業</th>
                    <th colspan="2">仕入店舗</th>
                </tr>
                <tr>
                    <td class="width-20">
                        <input type="text" class="form-control supply_company_code_input" id="supply_company_code" name="data[SupplySlip][supply_company_code]" value="{{$SupplySlipList->supply_company_code}}" tabindex="1">
                        <input type="hidden" id="supply_company_id" name="data[SupplySlip][supply_company_id]" value="{{$SupplySlipList->supply_company_id}}">
                    </td>
                    <td class="width-30">
                        <input type="text" class="form-control" id="supply_company_text" name="data[SupplySlip][supply_company_text]" value="{{$SupplySlipList->supply_company_name}}" readonly>
                    </td>
                    <td class="width-20">
                        <input type="text" class="form-control supply_shop_code_input" id="supply_shop_code" name="data[SupplySlip][supply_shop_code]" value="{{$SupplySlipList->supply_shop_code}}" tabindex="2">
                        <input type="hidden" id="supply_shop_id" name="data[SupplySlip][supply_shop_id]" value="{{$SupplySlipList->supply_shop_id}}">
                    </td>
                    <td class="width-30">
                        <input type="text" class="form-control" id="supply_shop_text" name="data[SupplySlip][supply_shop_text]" value="{{$SupplySlipList->supply_shop_name}}" readonly>
                    </td>
                </tr>

            </table>

            <!--<div class="add-slip-btn-area">
                <button id="add-slip-btn" type="button" class="btn add-slip-btn btn-primary">伝票追加</button>
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

                @foreach ($SupplySlipDetailList as $SupplySlipDetails)
                <?php $tabInitialNum = intval(9*($SupplySlipDetails->sort) + 3); ?>
                <tr id="slip-partition-{{$SupplySlipDetails->sort}}" class="partition-area"></tr>
                <input type="hidden" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][id]" value="{{$SupplySlipDetails->supply_slip_detail_id}}">
                <tr id="slip-upper-{{$SupplySlipDetails->sort}}">
                    <td class="width-10">
                        <input type="text" class="form-control product_code_input" id="product_code_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][product_code]" value="{{$SupplySlipDetails->product_code}}" tabindex="{{ $tabInitialNum }}">
                        <input type="hidden" id="product_id_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][product_id]" value="{{$SupplySlipDetails->product_id}}">
                    </td>
                    <td class="width-20">
                        <input type="text" class="form-control" id="product_text_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][product_text]" value="{{$SupplySlipDetails->product_name}}" placeholder="製品欄" readonly>
                    </td>
                    <!--<td class="width-15" colspan="2">
                        <input type="number" class="form-control" id="unit_price_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][unit_price]" value="{{$SupplySlipDetails->unit_price}}" tabindex="{{$tabInitialNum + 1}}" onKeyUp='javascript:priceNumChange({{$SupplySlipDetails->sort}})'>
                    </td>-->
                    <td>
                        <input type="number" class="form-control" id="inventory_unit_num_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][inventory_unit_num]" value="{{$SupplySlipDetails->inventory_unit_num}}" tabindex="{{$tabInitialNum + 1}}"
                            onKeyUp='javascript:priceNumChange({{ $SupplySlipDetails->inventory_unit_num}})' placeholder="個数欄">
                    </td>
                    <td>
                        <input type="text" class="form-control" id="inventory_unit_text_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][inventory_unit_text]" value="{{$SupplySlipDetails->inventory_unit_name}}" placeholder="個数欄" readonly>
                        <input type="hidden" id="inventory_unit_id_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][inventory_unit_id]" value="{{$SupplySlipDetails->inventory_unit_id}}">
                    </td>
                    <td class="width-10">
                        <input type="text" class="form-control origin_area_code_input" id="origin_area_code_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][origin_area_code]" value="{{$SupplySlipDetails->origin_area_id}}" tabindex="{{$tabInitialNum + 4}}">
                        <input type="hidden" id="origin_area_id_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][origin_area_id]" value="{{$SupplySlipDetails->origin_area_id}}">
                    </td>
                    <td class="width-20">
                        <input type="text" class="form-control" id="origin_area_text_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][origin_area_text]" value="{{$SupplySlipDetails->origin_area_name}}" placeholder="産地欄" readonly>
                    </td>
                    <td class="width-15">
                        <input type="text" class="form-control" id="tax_text_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][tax_text]" value="{{$SupplySlipDetails->tax_name}}" placeholder="税率欄" readonly>
                        <input type='hidden' id='tax_id_{{$SupplySlipDetails->sort}}' name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][tax_id]" value="{{$SupplySlipDetails->tax_id}}">
                    </td>
                    <td rowspan="4" class="width-5">
                        <button id="remove-slip-btn" type="button" class="btn remove-slip-btn btn-secondary" onclick='javascript:removeSlip({{$SupplySlipDetails->sort}}) '>削除</button>
                    </td>
                </tr>
                <tr id="slip-middle-{{$SupplySlipDetails->sort}}">
                    <td>
                        <input type="text" class="form-control standard_code_input" id="standard_code_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][standard_code]" value="{{$SupplySlipDetails->standard_code}}">
                        <input type="hidden" id="standard_id_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][standard_id]" value="{{$SupplySlipDetails->standard_id}}">
                    </td>
                    <td>
                        <input type="text" class="form-control" id="standard_text_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][standard_text]" value="{{$SupplySlipDetails->standard_name}}" placeholder="規格欄" readonly>
                    </td>
                    <!--<td>
                        <input type="number" class="form-control" id="unit_num_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][unit_num]" value="{{$SupplySlipDetails->unit_num}}" tabindex="{{$tabInitialNum + 2}}" onKeyUp='javascript:priceNumChange({{ $SupplySlipDetails->product_code }})'>
                    </td>
                    <td>
                        <input type="text" class="form-control" id="unit_text_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][unit_text]" value="{{$SupplySlipDetails->unit_name}}" readonly>
                        <input type="hidden" id="unit_id_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][unit_id]" value="{{$SupplySlipDetails->unit_id}}">
                    </td>
                    <td>
                        <input type="number" class="form-control" id="inventory_unit_num_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][inventory_unit_num]" value="{{$SupplySlipDetails->inventory_unit_num}}" tabindex="{{$tabInitialNum + 2}}"
                            onKeyUp='javascript:priceNumChange({{ $SupplySlipDetails->inventory_unit_num}})' placeholder="個数欄">
                    </td>
                    <td>
                        <input type="text" class="form-control" id="inventory_unit_text_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][inventory_unit_text]" value="{{$SupplySlipDetails->inventory_unit_name}}" placeholder="個数欄" readonly>
                        <input type="hidden" id="inventory_unit_id_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][inventory_unit_id]" value="{{$SupplySlipDetails->inventory_unit_id}}">
                    </td>-->
                    <td>
                        <input type="number" class="form-control" id="unit_num_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][unit_num]" value="{{$SupplySlipDetails->unit_num}}" tabindex="{{$tabInitialNum + 2}}" onKeyUp='javascript:priceNumChange({{ $SupplySlipDetails->sort }})'>
                    </td>
                    <td>
                        <input type="text" class="form-control" id="unit_text_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][unit_text]" value="{{$SupplySlipDetails->unit_name}}" placeholder="数量欄" readonly>
                        <input type="hidden" id="unit_id_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][unit_id]" value="{{$SupplySlipDetails->unit_id}}">
                    </td>
                    <td class="width-10">
                        <input type="text" class="form-control staff_code_input" id="staff_code_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][staff_code]" value="{{$SupplySlipDetails->staff_code}}" tabindex="{{$tabInitialNum + 5}}">
                        <input type="hidden" id="staff_id_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][staff_id]" value="{{$SupplySlipDetails->staff_id}}">
                    </td>
                    <td class="width-20">
                        <input type="text" class="form-control" id="staff_text_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][staff_text]" value="{{$SupplySlipDetails->staff_name}}" placeholder="担当欄" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control" id="seri_no_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][seri_no]" value="{{$SupplySlipDetails->seri_no}}" placeholder="セリNO欄" tabindex="{{$tabInitialNum + 6}}">
                    </td>
                </tr>
                <tr id="slip-lower-{{$SupplySlipDetails->sort}}">
                    <td>
                        <input type="text" class="form-control quality_code_input" id="quality_code_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][quality_code]" value="{{$SupplySlipDetails->quality_code}}">
                        <input type="hidden" id="quality_id_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][quality_id]" value="{{$SupplySlipDetails->quality_id}}">
                    </td>
                    <td>
                        <input type="text" class="form-control" id="quality_text_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][quality_text]" value="{{$SupplySlipDetails->quality_name}}" placeholder="品質欄" readonly>
                    </td>
                    <!--<td colspan="2">
                        <input type="text" class="form-control" id="notax_price_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][notax_price]" value="{{$SupplySlipDetails->notax_price}}" readonly>
                    </td>-->
                    <!--<td>
                        <input type="number" class="form-control" id="unit_num_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][unit_num]" value="{{$SupplySlipDetails->unit_num}}" tabindex="{{$tabInitialNum + 3}}" onKeyUp='javascript:priceNumChange({{ $SupplySlipDetails->sort }})'>
                    </td>
                    <td>
                        <input type="text" class="form-control" id="unit_text_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][unit_text]" value="{{$SupplySlipDetails->unit_name}}" placeholder="数量欄" readonly>
                        <input type="hidden" id="unit_id_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][unit_id]" value="{{$SupplySlipDetails->unit_id}}">
                    </td>
                    <td colspan="2">
                        @php $readonly = empty($SupplySlipDetails->inventory_unit_id) ? 'readonly' : ''; @endphp
                        <input type="number" class="form-control" id="inventory_unit_num_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][inventory_unit_num]" value="{{$SupplySlipDetails->inventory_unit_num}}" {{$readonly}} tabindex="{{$tabInitialNum + 5}}">
                    </td>-->
                    <!--<td>
                        <input type="text" class="form-control" id="inventory_unit_text_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][inventory_unit_text]" value="{{$SupplySlipDetails->inventory_unit_name}}" readonly>
                        <input type="hidden" id="inventory_unit_id_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][inventory_unit_id]" value="{{$SupplySlipDetails->inventory_unit_id}}">
                    </td>-->
                    <td class="width-15" colspan="2">
                        <input type="number" class="form-control" id="unit_price_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][unit_price]" value="{{$SupplySlipDetails->unit_price}}" tabindex="{{$tabInitialNum + 3}}" onKeyUp='javascript:priceNumChange({{$SupplySlipDetails->sort}})'>
                    </td>
                    <td colspan="4">
                        <input type="text" class="form-control" id="memo_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][memo]" value="{{$SupplySlipDetails->memo}}" placeholder="摘要欄" tabindex="{{$tabInitialNum + 8}}">
                    </td>
                </tr>
                <tr id="slip-most-lower-{{$SupplySlipDetails->sort}}">
                    <td>
                        小計
                    </td>
                    <td colspan="3">
                        <input type="text" class="form-control" id="notax_price_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][notax_price]" value="{{$SupplySlipDetails->notax_price}}" readonly>
                    </td>
                    <!--<td colspan="7">
                        <input type="text" class="form-control" id="memo_{{$SupplySlipDetails->sort}}" name="data[SupplySlipDetail][{{$SupplySlipDetails->sort}}][memo]" value="{{$SupplySlipDetails->memo}}" tabindex="{{$tabInitialNum + 8}}">
                    </td>-->
                </tr>
                @endforeach
            </table>
            <br><br>
            <div class="add-slip-btn-area">
                <button id="add-slip-btn" type="button" class="btn add-slip-btn btn-primary">伝票追加</button>
            </div>

            <table class="total-table">
                <tr>
                    <th>8%対象額</th>
                    <th>8%税額</th>
                    <th>8%税込額</th>
                </tr>
                <tr>
                    <td><input type="tel" class="form-control" id="notax_sub_total_8" name="data[SupplySlip][notax_sub_total_8]" value="{{$SupplySlipList->notax_sub_total_8}}" readonly></td>
                    <td><input type="tel" class="form-control" id="tax_total_8" name="data[SupplySlip][tax_total_8]" value="{{$SupplySlipList->tax_total_8}}" readonly></td>
                    <td><input type="tel" class="form-control" id="sub_total_8" name="data[SupplySlip][sub_total_8]" value="{{$SupplySlipList->sub_total_8}}" readonly></td>
                </tr>
                <tr>
                    <th>10%対象額</th>
                    <th>10%税額</th>
                    <th>10%税込額</th>
                </tr>
                <tr>
                    <td><input type="tel" class="form-control" id="notax_sub_total_10" name="data[SupplySlip][notax_sub_total_10]" value="{{$SupplySlipList->notax_sub_total_10}}" readonly></td>
                    <td><input type="tel" class="form-control" id="tax_total_10" name="data[SupplySlip][tax_total_10]" value="{{$SupplySlipList->tax_total_10}}" readonly></td>
                    <td><input type="tel" class="form-control" id="sub_total_10" name="data[SupplySlip][sub_total_10]" value="{{$SupplySlipList->sub_total_10}}" readonly></td>
                </tr>
                <tr>
                    <th>税抜小計</th>
                    <th>税額</th>
                    <th>税込小計</th>
                </tr>
                <tr>
                    <td><input type="tel" class="form-control" id="notax_sub_total" name="data[SupplySlip][notax_sub_total]" value="{{$SupplySlipList->notax_sub_total}}" readonly></td>
                    <td><input type="tel" class="form-control" id="tax_total" name="data[SupplySlip][tax_total]" value="{{$SupplySlipList->tax_total}}" readonly></td>
                    <td><input type="tel" class="form-control" id="sub_total" name="data[SupplySlip][sub_total]" value="{{$SupplySlipList->sub_total}}" readonly></td>
                </tr>
                <tr>
                    <th>配送コード</th>
                    <th>配送名</th>
                    <th>配送税込額</th>
                </tr>
                <tr>
                    <td>
                        <input type="text" class="form-control delivery_code_input" id="delivery_code" name="data[SupplySlip][delivery_code]" value="{{$SupplySlipList->delivery_code}}">
                        <input type="hidden" id="delivery_id" name="data[SupplySlip][delivery_id]" value="{{$SupplySlipList->delivery_id}}">
                    </td>
                    <td><input type="text" class="form-control" id="delivery_text" name="data[SupplySlip][delivery_text]" value="{{$SupplySlipList->delivery_name}}" readonly></td>
                    <td><input type="tel" class="form-control" id="delivery_price" name="data[SupplySlip][delivery_price]" value="{{$SupplySlipList->delivery_price}}" onKeyUp='javascript:adjustPrice()'></td>
                </tr>
                <tr>
                    <th>調整額</th>
                    <th colspan="2">調整後税込額</th>
                </tr>
                <tr>
                    <td><input type="tel" class="form-control" id="adjust_price" name="data[SupplySlip][adjust_price]" value="{{$SupplySlipList->adjust_price}}" onKeyUp='javascript:adjustPrice()'></td>
                    <td colspan="2"><input type="tel" class="form-control" id="total" name="data[SupplySlip][total]" value="{{$SupplySlipList->total}}" readonly></td>
                </tr>
            </table>
            <br><br>
            <div class="form-group">
                <label class="column-label" for="remarks">備考欄</label>
                <textarea id="remarks" class="form-control" name="data[SupplySlip][remarks]" rows="4" cols="40">{{$SupplySlipList->remarks}}</textarea>
            </div>
            <input type="hidden" name="data[SupplySlip][id]" value="{{$SupplySlipList->supply_slip_id}}">
            <input type='hidden' name="slip_num" id="slip_num" value="{{intval($SupplySlipDetails->sort) + 1}}">


            <br>
            <br>
            <table class="register-btn-table">
                <tr>
                    <td class="width-20">
                        <input type="text" class="form-control" id="supply_submit_type" name="data[SupplySlip][supply_submit_type]" value="{{$SupplySlipList->supply_submit_type}}">
                    </td>
                    <td class="width-30">
                        <input type="text" class="form-control" id="supply_submit_type_text" name="data[SupplySlip][supply_submit_type_text]" value="登録" readonly>
                    </td>
                    <td class="width-50">
                        <button id="register-btn" class="register-btn btn btn-primary" type="button">登録</button>
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

            // 一番最初は仕入先企業にフォーカスする
            $('#supply_company_code').focus();

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

                    } else if (this_id == "supply_submit_type") {

                        var submit_type = $(this).val();

                        if (submit_type == 1) {
                            $('#supply_submit_type_text').val("登録");
                            $('#register-btn').focus();
                        } else if (submit_type == 2) {
                            $('#supply_submit_type_text').val("一時保存");
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
                        $("#supply-slip-create-form [tabindex]").attr("tabindex", function(a, b) {

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
                        $("#supply-slip-create-form [tabindex]").attr("tabindex", function(a, b) {
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

                    } else if (this_id == "supply_submit_type") { // 登録タイプの場合

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
                        $("#supply-slip-create-form [tabindex]").attr("tabindex", function(a, b) {

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

                    var this_val = $('#supply_submit_type').val();
                    $('#supply_submit_type').val("");
                    $('#supply_submit_type').focus();
                    $('#supply_submit_type').val(this_val);

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

                    var this_val = $('#supply_submit_type').val();
                    $('#supply_submit_type').val("");
                    $('#supply_submit_type').focus();
                    $('#supply_submit_type').val(this_val);

                    return false;
                }
            });

            //-------------------------------------
            // 登録ボタン
            //-------------------------------------

            $(document).on("keypress", "#register-btn", function(event) {

                if (event.keyCode === 47) { // マイナスが押された時

                    var this_val = $('#supply_submit_type').val();
                    $('#supply_submit_type').val("");
                    $('#supply_submit_type').focus();
                    $('#supply_submit_type').val(this_val);

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

                if (selector_code.match(/supply_company/)) { // 仕入先企業

                    $.ajax({
                            headers: {
                                "X-CSRF-TOKEN": $("[name='_token']").val()
                            },
                            url: "./../AjaxSetSupplyCompany",
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

                } else if (selector_code.match(/supply_shop/)) { // 仕入先店舗

                    $.ajax({
                            headers: {
                                "X-CSRF-TOKEN": $("[name='_token']").val()
                            },
                            url: "./../AjaxSetSupplyShop",
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
                            url: "./../AjaxSetProduct",
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
                            url: "./../AjaxSetStandard",
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
                            url: "./../AjaxSetQuality",
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
                            url: "./../AjaxSetOriginArea",
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
                } else if (selector_code.match(/delivery_code/)) { // 配送IDの部分

                    $.ajax({
                            headers: {
                                "X-CSRF-TOKEN": $("[name='_token']").val()
                            },
                            url: "./../AjaxSetDelivery",
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
            $(".supply_company_code_input").autocomplete({
                source: function(req, resp) {
                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./../AjaxAutoCompleteSupplyCompany",
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
            // autocomplete処理 仕入店舗ID
            //-------------------------------------
            $(".supply_shop_code_input").autocomplete({
                source: function(req, resp) {
                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./../AjaxAutoCompleteSupplyShop",
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
                        url: "./../AjaxAutoCompleteProduct",
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
                        url: "./../AjaxAutoCompleteStandard",
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
                        url: "./../AjaxAutoCompleteQuality",
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
                        url: "./../AjaxAutoCompleteOriginArea",
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
            // autocomplete処理 産地ID
            //-------------------------------------
            $(".delivery_code_input").autocomplete({
                source: function(req, resp) {
                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./../AjaxAutoCompleteDelivery",
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
                        url: "./../SupplySlipAjaxAddSlip",
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

                        // 製品ID
                        let product_code_selector = $(data[2]).autocomplete({
                            source: function(req, resp) {
                                $.ajax({
                                    headers: {
                                        "X-CSRF-TOKEN": $("[name='_token']").val()
                                    },
                                    url: "./../AjaxAutoCompleteProduct",
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
                        let standard_code_selector = $(data[3]).autocomplete({
                            source: function(req, resp) {

                                var product_id = $("#product_id_" + slip_num).val();

                                $.ajax({
                                    headers: {
                                        "X-CSRF-TOKEN": $("[name='_token']").val()
                                    },
                                    url: "./../AjaxAutoCompleteStandard",
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
                        let quality_code_selector = $(data[4]).autocomplete({
                            source: function(req, resp) {
                                $.ajax({
                                    headers: {
                                        "X-CSRF-TOKEN": $("[name='_token']").val()
                                    },
                                    url: "./../AjaxAutoCompleteQuality",
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

                        // 産地ID
                        let origin_code_selector = $(data[5]).autocomplete({
                            source: function(req, resp) {
                                $.ajax({
                                    headers: {
                                        "X-CSRF-TOKEN": $("[name='_token']").val()
                                    },
                                    url: "./../AjaxAutoCompleteOriginArea",
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
                        $("#origin-code-area-" + slip_num).append(origin_code_selector);

                        // 担当ID
                        let staff_code_selector = $(data[6]).autocomplete({
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

                var this_val = $("#supply_submit_type").val();

                if (this_val == "1") {
                    $('#supply-slip-create-form').submit();
                } else if (this_val == "2") {
                    $('#supply-slip-create-form').submit();
                } else {
                    return false;
                }
            });

            // ------------------------------
            // submit_typeのフォーカスが外れた時
            // ------------------------------
            $('#supply_submit_type').blur(function() {
                var submitType = $('#supply_submit_type').val();
                if (submitType == 1) {
                    $('#supply_submit_type_text').val('登録');
                } else if (submitType == 2) {
                    $('#supply_submit_type_text').val('一時保存');
                } else {
                    alert("存在しない登録番号です。");
                }
            });

        });
    })(jQuery);

    function priceNumChange(this_slip_num) {

        // まず入力された仕入詳細の金額を計算する
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
                url: "./../SaleSlipAjaxChangeProductId",
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

    //------------
    // 入力チェック
    //------------
    function inputCheck() {

        // ----------
        // 変数初期化
        // ----------
        var supply_company_code; // 仕入企業
        var supply_shop_code; // 仕入店舗
        var product_code; // 製品ID
        var unit_price; // 単価
        var unit_num; // 受注数量
        var staff_code; // 担当
        var inventory_unit_num; // 仕入単位

        // -----------
        // 入力チェック
        // -----------
        supply_company_code = $("#supply_company_code").val();
        supply_shop_code = $("#supply_shop_code").val();
        if (supply_company_code == '') {
            alert('「仕入企業」を入力してください。');
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

            // 受注数量
            unit_num = $("#unit_num_" + i).val();
            if (unit_num == '') {
                alert('「受注数量」を入力してください。');
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

    .delivery_date_box {
        width: 100%;
        padding-top: 3%;
    }
</style>
