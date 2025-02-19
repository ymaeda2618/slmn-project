@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">企業情報</div>

        <!-- 表示エリア -->
        <div class="event-form">
            <div class="form-group">
                <label class="column-label" for="name">名前：</label>
                <input type="text" class="form-control" value="<?= $company_setting_data[0]->name ?>" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="postal_code">郵便番号：</label>
                <input type="text" class="form-control" value="<?= $company_setting_data[0]->postal_code ?>" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="address">住所：</label>
                <input type="text" class="form-control" value="<?= $company_setting_data[0]->address ?>" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_code">銀行コード：</label>
                <input type="text" class="form-control" value="<?= $company_setting_data[0]->bank_code ?>" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_name">銀行名：</label>
                <input type="text" class="form-control" value="<?= $company_setting_data[0]->bank_name ?>" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="branch_code">支店コード：</label>
                <input type="text" class="form-control" value="<?= $company_setting_data[0]->branch_code ?>" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="branch_name">支店名：</label>
                <input type="text" class="form-control" value="<?= $company_setting_data[0]->branch_name ?>" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_type">口座種別：</label>
                <input type="text" class="form-control" value="<?= $bank_type[$company_setting_data[0]->bank_type] ?>" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_account">口座番号：</label>
                <input type="text" class="form-control" value="<?= $company_setting_data[0]->bank_account ?>" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="office_tel">事務所TEL：</label>
                <input type="text" class="form-control" value="<?= $company_setting_data[0]->office_tel ?>" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="office_fax">事務所FAX：</label>
                <input type="text" class="form-control" value="<?= $company_setting_data[0]->office_fax ?>" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="shop_tel">店舗TEL：</label>
                <input type="text" class="form-control" value="<?= $company_setting_data[0]->shop_tel ?>" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="shop_fax">店舗FAX：</label>
                <input type="text" class="form-control" value="<?= $company_setting_data[0]->shop_fax ?>" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="invoice_form_id">適格請求書発行事業者登録番号：</label>
                <input type="text" class="form-control" value="<?= $company_setting_data[0]->invoice_form_id ?>" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="company_image">会社角印：</label>
                @if($company_setting_data[0]->company_image)
                    <img src="{{ asset('../storage/app/images/' . $company_setting_data[0]->company_image) }}" width="300px">
                @else
                    <p>登録された角印はありません。</p>
                @endif
            </div>
            <br>
            <div class="form-group">
                <a href="./CompanySettingEdit/" id="create-submit-btn" class="btn btn-primary">編集画面へ</a>
            </div>
        </div>

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
        max-width: 500px;
        margin: auto;
    }

    .form-control {
        display: block;
        height: calc(2.19rem + 2px);
        padding: .375rem .75rem;
        font-size: .9rem;
        line-height: 1.6;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    }

    .column-label {
        font-size: 0.9em;
        font-weight: bold;
        width: 100%;
        text-align: left;
    }
</style>