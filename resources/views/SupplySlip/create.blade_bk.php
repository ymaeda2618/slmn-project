@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">仕入伝票 新規成画面</div>

        <form class="smn-form" id="event-create-form" method="post" action="./SupplySlipConfirm" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group">
                <label class="column-label" for="supply_date">仕入日付</label>
                <input type="date" class="form-control " id="supply_date " name="data[SupplySlip][supply_date] ">
            </div>
            <div class="form-group ">
                <label class="column-label " for="supply_company_name ">仕入先企業</label>
                <select class="form-control " id="supply_company_id " name="data[SupplySlip][supply_company_id] ">
                    @foreach ($SupplyCompanyList as $SupplyCompanies)
                    <option value="{{$SupplyCompanies->id}}">{{$SupplyCompanies->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group ">
                <label class="column-label " for="supply_company_name ">仕入先店舗</label>
                <select class="form-control " id="supply_company_id " name="data[SupplySlip][supply_company_id] ">
                    <option value="0">-</option>
                </select>
            </div>

            <div class="add-slip-btn-area">
                <button id="add-slip-btn" type="button" class="btn add-slip-btn btn-primary" onclick="javascript:addSlip()">伝票追加</button>
                <input type='hidden' name="slip_num" id="slip_num" value="1">
            </div>

            <table class="slip-table">
                <tr>
                    <th>製品ID</th>
                    <th rowspan="2">品質</th>
                    <th>単価</th>
                    <th>数量</th>
                    <th>産地</th>
                    <th rowspan="2">税率</th>
                    <th rowspan="2">削除</th>
                </tr>
                <tr>
                    <th>規格</th>
                    <th colspan="2">金額</th>
                    <th>セリNO.</th>
                </tr>
                <tr id="slip-upper-0">
                    <td>
                        <select class=" form-control" id="product_id_0" name="data[SupplySlip][product_id][0]" onchange='javascript:productIdChange(0)'>
                            <option value="0">-</option>
                            @foreach ($ProductList as $Products)
                            <option value="{{$Products->id}}">{{$Products->name}}</option>
                            @endforeach
                        </select>
                    </td>
                    <td rowspan="2">
                        <select class="form-control quality_id_select row-double-contents" id="quality_id[0]" name="data[SupplySlip][quality_id][0]">
                            <option value="0">-</option>
                            @foreach ($QualityList as $Qualities)
                            <option value="{{$Qualities->id}}">{{$Qualities->name}}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" class="form-control" id="unit_price_0" name="data[SupplySlip][unit_price][0]" value='0' onKeyUp='javascript:priceNumChange(0)'>
                    </td>
                    <td>
                        <input type="number" class="form-control" id="unit_num_0" name="data[SupplySlip][unit_num][0]" value='0' onKeyUp='javascript:priceNumChange(0)'>
                    </td>
                    <td>
                        <select class="form-control " id="origin_area_id[0]" name="data[SupplySlip][origin_area_id][0]">
                            <option value="0">-</option>
                            @foreach ($OriginAreaList as $OriginAreas)
                            <option value="{{$OriginAreas->id}}">{{$OriginAreas->name}}</option>
                            @endforeach
                        </select>
                    </td>
                    <td rowspan="2">
                        <input type="text" class="form-control row-double-contents" id="tax_name_0" name="data[SupplySlip][tax_name][0]" readonly>
                        <input type='hidden' id='tax_id_0' name="data[SupplySlip][tax_name][0]" value="0">
                    </td>
                    <td rowspan="2">
                        <button id="remove-slip-btn" type="button" class="btn remove-slip-btn btn-secondary" onclick='javascript:removeSlip(0) '>削除</button>
                    </td>
                </tr>
                <tr id="slip-lower-0">
                    <td id='slip-standard-0'>
                        <select class="form-control " id="standard_id_0" name="data[SupplySlip][standard_id][0]">
                          <option value="0">-</option>
                        </select>
                    </td>
                    <td colspan="2">
                        <input type="text" class="form-control" id="notax_price_0" name="data[SupplySlip][notax_price][0]" value='0' readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control" id="seri_no[0]" name="data[SupplySlip][seri_no][0]">
                    </td>
                </tr>
            </table>
            <br><br>

            <table class="total-table">
                <tr>
                    <th>8%対象額</th>
                    <th>8%税額</th>
                    <th>8%税込額</th>
                </tr>
                <tr>
                    <td><input type="tel" class="form-control" id="notax_sub_total_8" name="data[SupplySlip][notax_sub_total_8]" value='0' readonly></td>
                    <td><input type="tel" class="form-control" id="tax_total_8" name="data[SupplySlip][tax_total_8]" value='0' readonly></td>
                    <td><input type="tel" class="form-control" id="sub_total_8" name="data[SupplySlip][tax_total_8]" value='0' readonly></td>
                </tr>
                <tr>
                    <th>10%対象額</th>
                    <th>10%税額</th>
                    <th>10%税込額</th>
                </tr>
                <tr>
                    <td><input type="tel" class="form-control" id="notax_sub_total_10" name="data[SupplySlip][notax_sub_total_10]" value='0' readonly></td>
                    <td><input type="tel" class="form-control" id="tax_total_10" name="data[SupplySlip][tax_total_10]" value='0' readonly></td>
                    <td><input type="tel" class="form-control" id="sub_total_10" name="data[SupplySlip][tax_total_10]" value='0' readonly></td>
                </tr>
                <tr>
                    <th>税抜小計</th>
                    <th>税額</th>
                    <th>税込小計</th>
                </tr>
                <tr>
                    <td><input type="tel" class="form-control" id="notax_sub_total" name="data[SupplySlip][notax_sub_total]" value='0' readonly></td>
                    <td><input type="tel" class="form-control" id="tax_total" name="data[SupplySlip][tax_total]" value='0' readonly></td>
                    <td><input type="tel" class="form-control" id="sub_total" name="data[SupplySlip][tax_total]" value='0' readonly></td>
                </tr>
                <tr>
                    <th>調整額</th>
                    <th colspan="2">調整後税込額</th>
                </tr>
                <tr>
                    <td><input type="tel" class="form-control" id="adjust_price" name="data[SupplySlip][adjust_price]" value='0'></td>
                    <td colspan="2"><input type="tel" class="form-control" id="total" name="data[SupplySlip][total]" value='0' readonly></td>
                </tr>
            </table>
            <br><br>
            <div class="form-group">
                <label class="column-label" for="remarks">備考欄</label>
                <textarea id='remarks ' class="form-control" name="data[SupplySlip][remarks]" rows="4" cols="40"></textarea>
            </div>


            <br>
            <br>
            <button id="create-submit-btn" type="submit" class="btn btn-primary">確認登録画面へ</button>
            <input type='hidden ' name="submit_type" value="1">
        </form>
    </div>
</div>
@endsection
<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
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

        });
    })(jQuery);

    function priceNumChange(slip_num) {

        // 更新対象のproduct_idを取得
        var unit_price = $("#unit_price_" + slip_num).val();
        var unit_num = $("#unit_num_" + slip_num).val();

        if (!unit_price) unit_price = 0;
        if (!unit_num) unit_num = 0;

        var calc_price = unit_price * unit_num;

        // 仕入れ詳細行の価格欄に格納する
        $("#notax_price_" + slip_num).val(calc_price);

        // 税額を取得
        var tax_id = $("#tax_id_" + slip_num).val();

        if (tax_id == 1) { // 8%の場合

            // 計算値を算入
            notax_sub_total_8 += calc_price;
            // 税額計算
            tax_total_8 = Math.round(notax_sub_total_8 * 0.08);
            // 税込額計算
            sub_total_8 = notax_sub_total_8 + tax_total_8;

        } else if (tax_id == 2) {

            // 計算値を算入
            notax_sub_total_10 += calc_price;
            // 税額計算
            tax_total_10 = Math.round(notax_sub_total_10 * 0.1);
            // 税込額計算
            sub_total_10 = notax_sub_total_10 + tax_total_10;
        }

        // 計算値を算入
        notax_sub_total = notax_sub_total_8 + notax_sub_total_10;
        tax_total = tax_total_8 + tax_total_10;

        // 調整後金額を取得
        total = sub_total + adjust_price;

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

    function addSlip() {

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

            })
            .fail(function(XMLHttpRequest, textStatus, errorThrown) {
                alert(XMLHttpRequest);
                alert(textStatus);
                alert(errorThrown);
                // 送信失敗
                alert("失敗しました。");
            });
    }

    function removeSlip(remove_num) {

        // 削除
        $("#slip-upper-" + remove_num).remove();
        $("#slip-lower-" + remove_num).remove();

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

    .slip-table {
        width: 100%;
    }

    .row-double-contents {
        height: calc(4.38rem + 4px)!important;
    }

    .add-slip-btn-area {
        text-align: right;
        padding: 40px 0px 20px;
        margin-right: 5%;
    }

    .add-slip-btn {
        min-width: 100px;
        background-color: #e3342f!important;
        border-color: #e3342f!important;
    }

    .remove-slip-btn {
        height: calc(4.38rem + 4px)!important;
    }

    .total-table {
        width: 100%;
    }
</style>
