@extends('layouts.app') @section('content')
<div class="container">
    <div class="row justify-content-center">

        <form class="smn-form" method="post" enctype="multipart/form-data" action="../InventoryAdjustmentEdit">
            <div class="title-area">
                <div class="top-title">在庫詳細画面</div>

                <div class="button-area">
                    @if (Home::authClerkCheck()) <button class="inventory-btn btn btn-primary" type="submit">在庫調整</button> @endif
                </div>
            </div>

            {{ csrf_field() }}
            {{--  伝票詳細エリア  --}}
            <div class="detail-area">
                <h4>伝票詳細一覧</h4>
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th style="width: 4%;">伝票ID</th>
                            <th style="width: 7%;">伝票日付</th>
                            <th style="width: 7%;">納品日</th>
                            <th class="width-10">取引先コード</th>
                            <th class="width-15">取引先名</th>
                            <th class="width-5">製品ID</th>
                            <th class="width-15">製品</th>
                            <th class="width-5">規格</th>
                            <th class="width-5">単価</th>
                            <th style="width: 7%;">スタッフ名</th>
                            <th style="width: 6%;">在庫残数</th>
                            <th class="width-5">単位</th>
                            <th style="width: 6%;">残金合計</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($supplySlipList as $supplySlips)
                        @if ($supplySlips->remaining_quantity > 0)
                        <tr>
                            <input type="hidden" class="supply_slip_detail" name="data[SupplySlipDetail][{{$supplySlips->supply_slip_detail_id}}][id]" value="{{$supplySlips->supply_slip_detail_id}}">
                            <td class="text-right">{{$supplySlips->supply_slip_id}}</td>
                            <td class="text-center">{{$supplySlips->supply_slip_date}}</td>
                            <td class="text-center">{{$supplySlips->delivery_date}}</td>
                            <td class="text-right">{{$supplySlips->company_code}}</td>
                            <td class="text-center">{{$supplySlips->company_name}}</td>
                            <td class="text-right">{{$supplySlips->product_code}}</td>
                            <td class="text-center">{{$supplySlips->product_name}}</td>
                            <td class="text-center">{{$supplySlips->standard_name}}</td>
                            <td class="text-right">{{number_format($supplySlips->unit_price)}}</td>
                            <td class="text-center">{{$supplySlips->staff_name}}</td>
                            <td class="text-right">{{$supplySlips->remaining_quantity}}</td>
                            <td class="text-center">{{$supplySlips->unit_name}}</td>
                            <td class="text-right">{{number_format($supplySlips->balanced_amount)}}</td>
                        </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{--  履歴エリア  --}}
            <div class="history-area">
                <h4>在庫取引一覧</h4>
                <table class="history-table">
                    <tr>
                        <th style="width: 5%;">種別</th>
                        <th style="width: 7%;">伝票日付</th>
                        <th style="width: 7%;">納品日</th>
                        <th class="width-10">取引先コード</th>
                        <th style="width: 12%;">取引先名</th>
                        <th class="width-5">製品ID</th>
                        <th class="width-15">製品</th>
                        <th class="width-5">規格</th>
                        <th class="width-5">単価</th>
                        <th style="width: 7%;">スタッフ名</th>
                        <th style="width: 6%;">取引数</th>
                        <th style="width: 6%;">在庫残数</th>
                        <th style="width: 4%;">単位</th>
                        <th style="width: 7%;">残金合計</th>
                    <tr>
                    @foreach ($inventoryManageList as $inventoryManageDatas)
                        <tr>
                            @if ($inventoryManageDatas->inventory_type == 1)
                                <td class="text-center regis-supply">仕入</td>
                                <td class="text-center">{{$inventoryManageDatas->supply_slip_date}}</td>
                                <td class="text-center">{{$inventoryManageDatas->supply_delivery_date}}</td>
                                <td class="text-right">{{$inventoryManageDatas->supply_company_code}}</td>
                                <td class="text-center">{{$inventoryManageDatas->supply_company_name}}</td>
                                <td class="text-right">{{$inventoryManageDatas->product_code}}</td>
                                <td class="text-center">{{$inventoryManageDatas->product_name}}</td>
                                <td class="text-center">{{$inventoryManageDatas->standard_name}}</td>
                                <td class="text-right">{{number_format($inventoryManageDatas->supply_unit_price)}}</td>
                                <td class="text-center">{{$inventoryManageDatas->supply_staff_name}}</td>
                                <td class="text-right">{{$inventoryManageDatas->inventory_unit_num}}</td>
                                <td class="text-right">{{$inventoryManageDatas->total_inventory_num}}</td>
                                <td class="text-center">{{$inventoryManageDatas->supply_unit_name}}</td>
                                <td class="text-right">{{number_format($inventoryManageDatas->total_inventory_price)}}</td>
                            @elseif ($inventoryManageDatas->inventory_type == 2)
                                <td class="text-center regis-sale">売上</td>
                                <td class="text-center">{{$inventoryManageDatas->sale_slip_date}}</td>
                                <td class="text-center">{{$inventoryManageDatas->sale_delivery_date}}</td>
                                <td class="text-right">{{$inventoryManageDatas->sale_company_code}}</td>
                                <td class="text-center">{{$inventoryManageDatas->sale_company_name}}</td>
                                <td class="text-right">{{$inventoryManageDatas->product_code}}</td>
                                <td class="text-center">{{$inventoryManageDatas->product_name}}</td>
                                <td class="text-center">{{$inventoryManageDatas->standard_name}}</td>
                                <td class="text-right">{{number_format($inventoryManageDatas->sale_unit_price)}}</td>
                                <td class="text-center">{{$inventoryManageDatas->sale_staff_name}}</td>
                                <td class="text-right">{{$inventoryManageDatas->inventory_unit_num}}</td>
                                <td class="text-right">{{$inventoryManageDatas->total_inventory_num}}</td>
                                <td class="text-center">{{$inventoryManageDatas->sale_unit_name}}</td>
                                <td class="text-right">{{number_format($inventoryManageDatas->total_inventory_price)}}</td>
                            @else
                                <td class="text-center regis-inventory">在庫調整</td>
                                <td class="text-center">伝票日付</td>
                                <td class="text-center">納品日</td>
                                <td class="text-right">取引先コード</td>
                                <td class="text-center">取引先名</td>
                                <td class="text-right">{{$inventoryManageDatas->product_code}}</td>
                                <td class="text-center">{{$inventoryManageDatas->product_name}}</td>
                                <td class="text-center">{{$inventoryManageDatas->standard_name}}</td>
                                <td class="text-right">{{number_format($inventoryManageDatas->supply_unit_price)}}</td>
                                <td class="text-center">スタッフ名</td>
                                <td class="text-right">{{$inventoryManageDatas->inventory_unit_num}}</td>
                                <td class="text-right">{{$inventoryManageDatas->total_inventory_num}}</td>
                                <td class="text-center">{{$inventoryManageDatas->supply_unit_name}}</td>
                                <td class="text-right">{{number_format($inventoryManageDatas->total_inventory_price)}}</td>
                            @endif
                        </tr>
                    @endforeach
                </table>
            </div>

        </form>

    </div>
</div>
@endsection
<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript">
</script>
<style>
    .title-area {
        max-width: 1300px;
        width: 100%;
        overflow: hidden;
    }

    .top-title {
        max-width: 1300px;
        font-size: 1.4em;
        font-weight: bold;
        width: 45%;
        padding: 25px 0px 25px 100px;
        float: left;
    }

    .button-area {
        width: 45%;
        text-align: center;
        margin-top: 25px;
        margin-left: 90px;
        float: left;
    }

    .smn-form {
        max-width: 1300px;
        width: 100%;
        margin: auto;
    }

    .detail-area {
        width: 100%;
        overflow: hidden;
    }

    .detail-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #bbb;
    }

    .history-area {
        width: 100%;
        margin-top: 5%;
    }

    .history-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #bbb;
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

    .regis-supply {
        background-color: #D2F0F0;
        font-weight: bold;
        border-left: 3px solid #0099CB!important;
        text-align: center;
    }

    .regis-sale {
        background-color: #f0d2d2;
        font-weight: bold;
        border-left: 3px solid #cb0000!important;
        text-align: center;
    }

    .regis-inventory {
        background-color: #DBFFDB;
        font-weight: bold;
        border-left: 3px solid #90ee90!important;
        text-align: center;
    }

    .inventory-btn {
        width: 50%;
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