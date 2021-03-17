@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">仕入先店舗 編集画面</div>

        <form class="event-form" id="event-create-form" method="post" action="../SupplyShopConfirm" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group">
                <label class="column-label" for="supply_shop_name">仕入先企業名</label>
                <select class="form-control" id="supply_company_id" name="data[SupplyShop][supply_company_id]">
                    @foreach ($SupplyCompanyList as $SupplyCompanys)
                    <option value="{{$SupplyCompanys->id}}">{{$SupplyCompanys->name}}</option>
                    @endforeach
                </select>
                <input type='hidden' id='supply_company_id_selected' value='{{$editSupplyShop->supply_company_id}}'>
            </div>
            <div class="form-group">
                <label class="column-label" for="supply_shop_name">仕入先店舗名</label>
                <input type="text" class="form-control" id="supply_shop_name" name="data[SupplyShop][supply_shop_name]" value="{{$editSupplyShop->supply_shop_name}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="postal_code">郵便番号※ハイフンなし数字のみ</label>
                <input type="text" class="form-control" id="postal_code" name="data[SupplyShop][postal_code]" value="{{$editSupplyShop->postal_code}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="address">住所</label>
                <input type="text" class="form-control" id="address" name="data[SupplyShop][address]" value="{{$editSupplyShop->address}}">
            </div>
            <br>
            <button id="create-submit-btn" type="submit" class="btn btn-primary">編集確認画面へ</button>
            <input type='hidden' name="data[SupplyShop][supply_shop_id]" value="{{$editSupplyShop->supply_shop_id}}">
            <input type='hidden' name="submit_type" value="2">
        </form>

    </div>
</div>
@endsection

<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<script type="text/javascript">
    (function($) {
        jQuery(window).load(function() {

            // 検索されて選択状態の企業を取得
            var supply_company_id_selected = $("#supply_company_id_selected").val();
            // 検索条件で設定された企業を設定
            $('#supply_company_id').val(supply_company_id_selected);
        });

    })(jQuery);
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

    .event-form {
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

    .attention-title {
        font-size: 0.9em;
        font-weight: bold;
        color: red;
        width: 100%;
        text-align: left;
        padding: 25px 0px;
    }
</style>
