@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">売上先店舗 新規成画面</div>

        @if(!empty($error_message))
        <div class="error-alert">{{$error_message}}</div>
        @endif

        <form class="smn-form" id="event-create-form" method="post" action="./SaleShopConfirm" enctype="multipart/form-data" onsubmit="return inputCheck();">
            {{ csrf_field() }}
            <div class="form-group">
                <label class="column-label" for="code">コード<font color="red">※任意</font></label>
                <input type="number" class="form-control" id="code" name="data[SaleShop][code]">
            </div>
            <div class="form-group">
                <label class="column-label" for="sale_company_name">売上先企業</label>
                <select class="form-control" id="sale_company_id" name="data[SaleShop][sale_company_id]">
                    @foreach ($SaleCompanyList as $SaleCompanys)
                    <option value="{{$SaleCompanys->id}}">{{$SaleCompanys->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="column-label" for="sale_shop_name">売上先店舗名</label>
                <input type="text" class="form-control" id="sale_shop_name" name="data[SaleShop][sale_shop_name]">
            </div>
            <div class="form-group">
                <label class="column-label" for="yomi">ヨミガナ</label>
                <input type="text" class="form-control" id="yomi" name="data[SaleShop][yomi]">
            </div>
            <div class="form-group">
                <label class="column-label" for="postal_code">郵便番号※ハイフンなし数字のみ</label>
                <input type="text" class="form-control" id="postal_code" name="data[SaleShop][postal_code]">
            </div>
            <div class="form-group">
                <label class="column-label" for="address">住所</label>
                <input type="text" class="form-control" id="address" name="data[SaleShop][address]">
            </div>
            <br>
            <button id="create-submit-btn" type="submit" class="btn btn-primary">確認登録画面へ</button>
            <input type='hidden' name="submit_type" value="1">
        </form>
    </div>
</div>
@endsection
<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<script type="text/javascript">
    //------------
    // 入力チェック
    //------------
    function inputCheck() {

        var sale_shop_name = $('#sale_shop_name').val();

        if (sale_shop_name == "") {
            alert('売上先店舗名が入力されておりません。');
            return false;
        }

        var yomi = $('#yomi').val();

        if (yomi == "") {
            alert('ヨミガナが入力されておりません。');
            return false;
        }
        if (!yomi.match(/^[ァ-ヶー]+$/)) {
            alert('ヨミガナは全角カタカナで入力してください。');
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

    .error-alert {
        color: red;
        font-weight: bold;
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
</style>
