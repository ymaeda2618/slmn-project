@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">企業情報 確認画面</div>

        <form class="event-form" id="event-create-form" method="post" action="{{$action_url}}" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group">
                <label class="column-label" for="name">名前：</label>
                <input type="text" class="form-control" id="name" name="data[CompanySetting][name]" value="{{$request['data']['CompanySetting']['name']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="postal_code">郵便番号：</label>
                <input type="text" class="form-control" id="postal_code" name="data[CompanySetting][postal_code]" value="{{$request['data']['CompanySetting']['postal_code']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="address">住所：</label>
                <input type="text" class="form-control" id="address" name="data[CompanySetting][address]" value="{{$request['data']['CompanySetting']['address']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_code">銀行コード：</label>
                <input type="text" class="form-control" id="bank_code" name="data[CompanySetting][bank_code]" value="{{$request['data']['CompanySetting']['bank_code']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_name">銀行名：</label>
                <input type="text" class="form-control" id="bank_name" name="data[CompanySetting][bank_name]" value="{{$request['data']['CompanySetting']['bank_name']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="branch_code">支店コード：</label>
                <input type="text" class="form-control" id="branch_code" name="data[CompanySetting][branch_code]" value="{{$request['data']['CompanySetting']['branch_code']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="branch_name">支店名：</label>
                <input type="text" class="form-control" id="branch_name" name="data[CompanySetting][branch_name]" value="{{$request['data']['CompanySetting']['branch_name']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_type">口座種別：</label>@if($request['data']['CompanySetting']['bank_type'] == 0)
                <input type="text" class="form-control" id="bank_type_text" name="data[CompanySetting][bank_type_text]" value="-" readonly> @elseif($request['data']['CompanySetting']['bank_type'] == 1)
                <input type="text" class="form-control" id="bank_type_text" name="data[CompanySetting][bank_type_text]" value="普通" readonly> @elseif($request['data']['CompanySetting']['bank_type'] == 2)
                <input type="text" class="form-control" id="bank_type_text" name="data[CompanySetting][bank_type_text]" value="当座" readonly> @else
                <input type="text" class="form-control" id="bank_type_text" name="data[CompanySetting][bank_type_text]" value="その他" readonly> @endif
                <input type='hidden' name="data[CompanySetting][bank_type]" value="{{$request['data']['CompanySetting']['bank_type']}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_account">口座番号：</label>
                <input type="text" class="form-control" id="bank_account" name="data[CompanySetting][bank_account]" value="{{$request['data']['CompanySetting']['bank_account']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="office_tel">事務所TEL：</label>
                <input type="text" class="form-control" id="office_tel" name="data[CompanySetting][office_tel]" value="{{$request['data']['CompanySetting']['office_tel']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="office_fax">事務所FAX：</label>
                <input type="text" class="form-control" id="office_fax" name="data[CompanySetting][office_fax]" value="{{$request['data']['CompanySetting']['office_fax']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="shop_tel">店舗TEL：</label>
                <input type="text" class="form-control" id="shop_tel" name="data[CompanySetting][shop_tel]" value="{{$request['data']['CompanySetting']['shop_tel']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="shop_fax">店舗FAX：</label>
                <input type="text" class="form-control" id="shop_fax" name="data[CompanySetting][shop_fax]" value="{{$request['data']['CompanySetting']['shop_fax']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="invoice_form_id">適格請求書発行事業者登録番号：</label>
                <input type="text" class="form-control" id="invoice_form_id" name="data[CompanySetting][invoice_form_id]" value="{{$request['data']['CompanySetting']['invoice_form_id']}}" readonly>
            </div>
            <br>
            <button id="create-submit-btn" type="submit" class="btn btn-primary">登録完了画面へ</button>
            <input type='hidden' name="data[CompanySetting][id]" value="{{isset($request['data']['CompanySetting']['id']) ? $request['data']['CompanySetting']['id'] : 0}}">
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