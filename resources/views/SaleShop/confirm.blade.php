@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">売上先企業内容 確認画面</div>

        <form class="event-form" id="event-create-form" method="post" action="{{$action_url}}" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group">
                <label class="column-label" for="sale_company_name">売上先企業名</label>
                <input type="text" class="form-control" id="sale_company_name" name="data[SaleShop][sale_company_name]" value="{{$sale_company_list->name}}" readonly>
                <input type='hidden' name="data[SaleShop][sale_company_id]" value="{{$request->data['SaleShop']['sale_company_id']}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="sale_shop_name">売上先店舗名</label>
                <input type="text" class="form-control" id="sale_shop_name" name="data[SaleShop][sale_shop_name]" value="{{$request->data['SaleShop']['sale_shop_name']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="postal_code">郵便番号※ハイフンなし数字のみ</label>
                <input type="text" class="form-control" id="postal_code" name="data[SaleShop][postal_code]" value="{{$request->data['SaleShop']['postal_code']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="address">住所</label>
                <input type="text" class="form-control" id="address" name="data[SaleShop][address]" value="{{$request->data['SaleShop']['address']}}" readonly>
            </div>
            <br>
            <button id="create-submit-btn" type="submit" class="btn btn-primary">確認登録画面へ</button>
            <input type='hidden' name="data[SaleShop][sale_shop_id]" value="{{isset($request->data['SaleShop']['sale_shop_id']) ? $request->data['SaleShop']['sale_shop_id'] : 0}}">
        </form>

    </div>
</div>
@endsection

<style>
    /* 共通 */

    .top-title {
        font-size: 1.4em;
        font-weight: bold;
        width: 100%;
        text-align: center;
        padding: 25px 0px;
    }

    .confirm-title {
        font-size: 0.9em;
        font-weight: bold;
        color: red;
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
</style>
