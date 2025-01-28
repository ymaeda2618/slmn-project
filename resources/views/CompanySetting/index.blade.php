@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">企業情報</div>

        <!-- 表示エリア -->
        <div class="form-area">
            <div class="form-group">
                <label class="column-label" for="name">名前：</label>
                <p><?= $company_setting_data[0]->name ?></p>
            </div>
            <div class="form-group">
                <label class="column-label" for="postal_code">郵便番号：</label>
                <p><?= $company_setting_data[0]->postal_code ?></p>
            </div>
            <div class="form-group">
                <label class="column-label" for="address">住所：</label>
                <p><?= $company_setting_data[0]->address ?></p>
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_code">銀行コード：</label>
                <p><?= $company_setting_data[0]->bank_code ?></p>
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_name">銀行名：</label>
                <p><?= $company_setting_data[0]->bank_name ?></p>
            </div>
            <div class="form-group">
                <label class="column-label" for="branch_code">支店コード：</label>
                <p><?= $company_setting_data[0]->branch_code ?></p>
            </div>
            <div class="form-group">
                <label class="column-label" for="branch_name">支店名：</label>
                <p><?= $company_setting_data[0]->branch_name ?></p>
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_type">口座種別：</label>
                <p><?= $bank_type[$company_setting_data[0]->bank_type] ?></p>
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_account">口座番号：</label>
                <p><?= $company_setting_data[0]->bank_account ?></p>
            </div>
            <div class="form-group">
                <label class="column-label" for="office_tel">事務所TEL：</label>
                <p><?= $company_setting_data[0]->office_tel ?></p>
            </div>
            <div class="form-group">
                <label class="column-label" for="office_fax">事務所FAX：</label>
                <p><?= $company_setting_data[0]->office_fax ?></p>
            </div>
            <div class="form-group">
                <label class="column-label" for="shop_tel">店舗TEL：</label>
                <p><?= $company_setting_data[0]->shop_tel ?></p>
            </div>
            <div class="form-group">
                <label class="column-label" for="shop_fax">店舗FAX：</label>
                <p><?= $company_setting_data[0]->shop_fax ?></p>
            </div>
            <div class="form-group">
                <label class="column-label" for="invoice_form_id">適格請求書発行事業者登録番号：</label>
                <p><?= $company_setting_data[0]->invoice_form_id ?></p>
            </div>
            <br>
            <a href="./CompanySettingEdit/" id="create-submit-btn" class="btn btn-primary">編集画面へ</a>
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

    .form-area {
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