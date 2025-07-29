@extends('layouts.app') @section('content')
<div class="container">
    <div class="row justify-content-center">

        <div class="top-title">企業情報 編集画面</div>

        @if(!empty($error_message))
        <div class="error-alert">{{$error_message}}</div>
        @endif

        <form class="event-form" id="event-create-form" method="post" action="./../CompanySettingConfirm" enctype="multipart/form-data" onsubmit="return inputCheck();">
            {{ csrf_field() }}
            <div class="form-group">
                <label class="column-label" for="name">名前：</label>
                <input type="text" class="form-control" id="name" name="data[CompanySetting][name]" value="{{$company_setting_data[0]->name}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="postal_code">郵便番号 (※半角数字で入力してください)：</label>
                @error('data.CompanySetting.postal_code')
                <div class="text-red-500">{{ $message }}</div>
                @enderror
                <input type="text" class="form-control" id="postal_code" name="data[CompanySetting][postal_code]" value="{{old('data.CompanySetting.postal_code', $company_setting_data[0]->postal_code)}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="address">住所：</label>
                <input type="text" class="form-control" id="address" name="data[CompanySetting][address]" value="{{$company_setting_data[0]->address}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="office_tel">事務所TEL (※半角数字で入力してください)：</label>
                @error('data.CompanySetting.office_tel')
                <div class="text-red-500">{{ $message }}</div>
                @enderror
                <input type="text" class="form-control" id="office_tel" name="data[CompanySetting][office_tel]" value="{{old('data.CompanySetting.office_tel', $company_setting_data[0]->office_tel)}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="office_fax">事務所FAX (※半角数字で入力してください)：</label>
                @error('data.CompanySetting.office_fax')
                <div class="text-red-500">{{ $message }}</div>
                @enderror
                <input type="text" class="form-control" id="office_fax" name="data[CompanySetting][office_fax]" value="{{old('data.CompanySetting.office_fax', $company_setting_data[0]->office_fax)}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="shop_tel">店舗TEL (※半角数字で入力してください)：</label>
                @error('data.CompanySetting.shop_tel')
                <div class="text-red-500">{{ $message }}</div>
                @enderror
                <input type="text" class="form-control" id="shop_tel" name="data[CompanySetting][shop_tel]" value="{{old('data.CompanySetting.shop_tel', $company_setting_data[0]->shop_tel)}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="shop_fax">店舗FAX (※半角数字で入力してください)：</label>
                @error('data.CompanySetting.shop_fax')
                <div class="text-red-500">{{ $message }}</div>
                @enderror
                <input type="text" class="form-control" id="shop_fax" name="data[CompanySetting][shop_fax]" value="{{old('data.CompanySetting.shop_fax', $company_setting_data[0]->shop_fax)}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="invoice_form_id">適格請求書発行事業者登録番号 (※「T」を除く半角数字で入力してください)：</label>
                @error('data.CompanySetting.invoice_form_id')
                <div class="text-red-500">{{ $message }}</div>
                @enderror
                <input type="text" class="form-control" id="invoice_form_id" name="data[CompanySetting][invoice_form_id]" value="{{old('data.CompanySetting.invoice_form_id', $company_setting_data[0]->invoice_form_id)}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="company_image">会社角印：</label>
                @if($company_setting_data[0]->company_image)
                    <p>現在登録されている画像：</p>
                    <img src="{{ asset('../storage/app/images/' . $company_setting_data[0]->company_image) }}" width="300px"><br>
                @endif
                @error('data.CompanySetting.company_image')
                <div class="text-red-500">{{ $message }}</div>
                @enderror
                <input type="file" class="" id="company_image" name="data[CompanySetting][company_image]"><br>
                <small>※新しい画像を選択すると上書きされます</small>
            </div>
            <br>
            <button id="create-submit-btn" type="submit" class="btn btn-primary">編集確認画面へ</button>
            <input type='hidden' name="data[CompanySetting][id]" value="{{$company_setting_data[0]->id}}">
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

        // 半角数字のみの正規表現
        const regex = /^\d*$/;

        var postal_code = $('#postal_code').val();
        if (postal_code != "" && !postal_code.match(regex)) {
            alert('郵便番号は半角数字のみで入力してください。');
            return false;
        }

        var bank_code = $('#bank_code').val();
        if (bank_code != "" && !bank_code.match(regex)) {
            alert('銀行コードは半角数字のみで入力してください。');
            return false;
        }

        var branch_code = $('#branch_code').val();
        if (branch_code != "" && !branch_code.match(regex)) {
            alert('支店コードは半角数字のみで入力してください。');
            return false;
        }

        var bank_account = $('#bank_account').val();
        if (bank_account != "" && !bank_account.match(regex)) {
            alert('口座番号は半角数字のみで入力してください。');
            return false;
        }

        var office_tel = $('#office_tel').val();
        if (office_tel != "" && !office_tel.match(regex)) {
            alert('事務所TELは半角数字のみで入力してください。');
            return false;
        }

        var office_fax = $('#office_fax').val();
        if (office_fax != "" && !office_fax.match(regex)) {
            alert('事務所FAXは半角数字のみで入力してください。');
            return false;
        }

        var shop_tel = $('#shop_tel').val();
        if (shop_tel != "" && !shop_tel.match(regex)) {
            alert('店舗TELは半角数字のみで入力してください。');
            return false;
        }

        var shop_fax = $('#shop_fax').val();
        if (shop_fax != "" && !shop_fax.match(regex)) {
            alert('店舗FAXは半角数字のみで入力してください。');
            return false;
        }

        var invoice_form_id = $('#invoice_form_id').val();
        if (invoice_form_id != "" && !invoice_form_id.match(regex)) {
            alert('適格請求書発行事業者登録番号は半角数字のみで入力してください。');
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

    .text-red-500 {
        color: red;
        font-weight: bold;
    }
</style>
