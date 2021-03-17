@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">仕入先企業内容 確認画面</div>

        <form class="event-form" id="event-create-form" method="post" action="{{$action_url}}" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group">
                <label class="column-label" for="code">コード</label>
                <input type="text" class="form-control" id="yomi" name="data[SupplyCompany][code]" value="{{$request->data['SupplyCompany']['code']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="supply_company_name">仕入先企業名</label>
                <input type="text" class="form-control" id="supply_company_name" name="data[SupplyCompany][supply_company_name]" value="{{$request->data['SupplyCompany']['supply_company_name']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="yomi">ヨミガナ</label>
                <input type="text" class="form-control" id="yomi" name="data[SupplyCompany][yomi]" value="{{$request->data['SupplyCompany']['yomi']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="closing_date_text">締め日</label> @if($request->data['SupplyCompany']['closing_date'] == 99)
                <input type="text" class="form-control" id="closing_date_text" name="data[SupplyCompany][closing_date_text]" value="月末" readonly> @elseif($request->data['SupplyCompany']['closing_date'] == 88)
                <input type="text" class="form-control" id="closing_date_text" name="data[SupplyCompany][closing_date_text]" value="都度" readonly> @else
                <input type="text" class="form-control" id="closing_date_text" name="data[SupplyCompany][closing_date_text]" value="{{$request->data['SupplyCompany']['closing_date']}}日" readonly> @endif
                <input type='hidden' name="data[SupplyCompany][closing_date]" value="{{$request->data['SupplyCompany']['closing_date']}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="postal_code">郵便番号※ハイフンなし数字のみ</label>
                <input type="text" class="form-control" id="postal_code" name="data[SupplyCompany][postal_code]" value="{{$request->data['SupplyCompany']['postal_code']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="address">住所</label>
                <input type="text" class="form-control" id="address" name="data[SupplyCompany][address]" value="{{$request->data['SupplyCompany']['address']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_code">金融機関コード</label>
                <input type="text" class="form-control" id="bank_code" name="data[SupplyCompany][bank_code]" value="{{$request->data['SupplyCompany']['bank_code']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_name">銀行名</label>
                <input type="text" class="form-control" id="bank_name" name="data[SupplyCompany][bank_name]" value="{{$request->data['SupplyCompany']['bank_name']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="branch_code">支店コード</label>
                <input type="text" class="form-control" id="branch_code" name="data[SupplyCompany][branch_code]" value="{{$request->data['SupplyCompany']['branch_code']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="branch_name">支店名</label>
                <input type="text" class="form-control" id="branch_name" name="data[SupplyCompany][branch_name]" value="{{$request->data['SupplyCompany']['branch_name']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_type">口座種別</label> @if($request->data['SupplyCompany']['bank_type'] == 0)
                <input type="text" class="form-control" id="bank_type_text" name="data[SupplyCompany][bank_type_text]" value="-" readonly> @elseif($request->data['SupplyCompany']['bank_type'] == 1)
                <input type="text" class="form-control" id="bank_type_text" name="data[SupplyCompany][bank_type_text]" value="普通" readonly> @elseif($request->data['SupplyCompany']['bank_type'] == 2)
                <input type="text" class="form-control" id="bank_type_text" name="data[SupplyCompany][bank_type_text]" value="当座" readonly> @else
                <input type="text" class="form-control" id="bank_type_text" name="data[SupplyCompany][bank_type_text]" value="その他" readonly> @endif
                <input type='hidden' name="data[SupplyCompany][bank_type]" value="{{$request->data['SupplyCompany']['bank_type']}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_account">口座番号</label>
                <input type="text" class="form-control" id="bank_account" name="data[SupplyCompany][bank_account]" value="{{$request->data['SupplyCompany']['bank_account']}}" readonly>
            </div>
            <br>
            <button id="create-submit-btn" type="submit" class="btn btn-primary">確認登録画面へ</button>
            <input type='hidden' name="data[SupplyCompany][supply_company_id]" value="{{isset($request->data['SupplyCompany']['supply_company_id']) ? $request->data['SupplyCompany']['supply_company_id'] : 0}}">
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