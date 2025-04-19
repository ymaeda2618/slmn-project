@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">本部企業内容 確認画面</div>

        <form class="event-form" id="event-create-form" method="post" action="{{$action_url}}" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group">
                <label class="column-label" for="code">コード番号<font color="red">※任意</font></label>
                <input type="tel" class="form-control" id="code" name="data[OwnerCompany][code]" value="{{$request->data['OwnerCompany']['code']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="owner_company_name">本部企業名</label>
                <input type="text" class="form-control" id="owner_company_name" name="data[OwnerCompany][owner_company_name]" value="{{$request->data['OwnerCompany']['owner_company_name']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="yomi">ヨミガナ</label>
                <input type="text" class="form-control" id="yomi" name="data[OwnerCompany][yomi]" value="{{$request->data['OwnerCompany']['yomi']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="tax_calc_type">消費税計算区分</label> @if($request->data['OwnerCompany']['tax_calc_type'] == 0)
                <input type="text" class="form-control" id="tax_calc_type_text" name="data[OwnerCompany][tax_calc_type_text]" value="伝票ごとに計算" readonly> @elseif($request->data['OwnerCompany']['tax_calc_type'] == 1)
                <input type="text" class="form-control" id="tax_calc_type_text" name="data[OwnerCompany][tax_calc_type_text]" value="請求書ごとに計算" readonly> @endif
                <input type='hidden' name="data[OwnerCompany][tax_calc_type]" value="{{$request->data['OwnerCompany']['tax_calc_type']}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="closing_date_text">締め日<font color="red">※任意</font></label> @if($request->data['OwnerCompany']['closing_date'] == 99)
                <input type="text" class="form-control" id="closing_date_text" name="data[OwnerCompany][closing_date_text]" value="月末" readonly> @elseif($request->data['OwnerCompany']['closing_date'] == 88)
                <input type="text" class="form-control" id="closing_date_text" name="data[OwnerCompany][closing_date_text]" value="都度" readonly> @else
                <input type="text" class="form-control" id="closing_date_text" name="data[OwnerCompany][closing_date_text]" value="{{$request->data['OwnerCompany']['closing_date']}}日" readonly> @endif
                <input type='hidden' name="data[OwnerCompany][closing_date]" value="{{$request->data['OwnerCompany']['closing_date']}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="postal_code">郵便番号※ハイフンなし数字のみ<font color="red">※任意</font></label>
                <input type="text" class="form-control" id="postal_code" name="data[OwnerCompany][postal_code]" value="{{$request->data['OwnerCompany']['postal_code']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="address">住所<font color="red">※任意</font></label>
                <input type="text" class="form-control" id="address" name="data[OwnerCompany][address]" value="{{$request->data['OwnerCompany']['address']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_code">金融機関コード<font color="red">※任意</font></label>
                <input type="text" class="form-control" id="bank_code" name="data[OwnerCompany][bank_code]" value="{{$request->data['OwnerCompany']['bank_code']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_name">銀行名<<font color="red">※任意</font></label>
                <input type="text" class="form-control" id="bank_name" name="data[OwnerCompany][bank_name]" value="{{$request->data['OwnerCompany']['bank_name']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="branch_code">支店コード<font color="red">※任意</font></label>
                <input type="text" class="form-control" id="branch_code" name="data[OwnerCompany][branch_code]" value="{{$request->data['OwnerCompany']['branch_code']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="branch_name">支店名<font color="red">※任意</font></label>
                <input type="text" class="form-control" id="branch_name" name="data[OwnerCompany][branch_name]" value="{{$request->data['OwnerCompany']['branch_name']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_type">口座種別<font color="red">※任意</font></label> @if($request->data['OwnerCompany']['bank_type'] == 0)
                <input type="text" class="form-control" id="bank_type_text" name="data[OwnerCompany][bank_type_text]" value="-" readonly> @elseif($request->data['OwnerCompany']['bank_type'] == 1)
                <input type="text" class="form-control" id="bank_type_text" name="data[OwnerCompany][bank_type_text]" value="普通" readonly> @elseif($request->data['OwnerCompany']['bank_type'] == 2)
                <input type="text" class="form-control" id="bank_type_text" name="data[OwnerCompany][bank_type_text]" value="当座" readonly> @else
                <input type="text" class="form-control" id="bank_type_text" name="data[OwnerCompany][bank_type_text]" value="その他" readonly> @endif
                <input type='hidden' name="data[OwnerCompany][bank_type]" value="{{$request->data['OwnerCompany']['bank_type']}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_account">口座番号<font color="red">※任意</font></label>
                <input type="text" class="form-control" id="bank_account" name="data[OwnerCompany][bank_account]" value="{{$request->data['OwnerCompany']['bank_account']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="invoice_output_type">請求書出力タイプ<font color="red">※任意</font></label> @if($request->data['OwnerCompany']['invoice_output_type'] == 0)
                <input type="text" class="form-control" id="invoice_output_type_text" name="data[OwnerCompany][invoice_output_type_text]" value="本部企業毎" readonly> @else
                <input type="text" class="form-control" id="invoice_output_type_text" name="data[OwnerCompany][invoice_output_type_text]" value="店舗毎" readonly> @endif
                <input type='hidden' name="data[OwnerCompany][invoice_output_type]" value="{{$request->data['OwnerCompany']['invoice_output_type']}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="invoice_display_flg">請求書表示フラグ<font color="red">※任意</font></label> @if($request->data['OwnerCompany']['invoice_display_flg'] == 0)
                <input type="text" class="form-control" id="invoice_display_flg_text" name="data[OwnerCompany][invoice_display_flg_text]" value="無効" readonly> @else
                <input type="text" class="form-control" id="invoice_display_flg_text" name="data[OwnerCompany][invoice_display_flg_text]" value="有効" readonly> @endif
                <input type='hidden' name="data[OwnerCompany][invoice_display_flg]" value="{{$request->data['OwnerCompany']['invoice_display_flg']}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="invoice_display_name">請求書表示名<font color="red">※任意</font></label>
                <input type="text" class="form-control" id="invoice_display_name" name="data[OwnerCompany][invoice_display_name]" value="{{$request->data['OwnerCompany']['invoice_display_name']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="invoice_display_postal_code">郵便番号※ハイフンなし数字のみ<font color="red">※任意</font></label>
                <input type="text" class="form-control" id="invoice_display_postal_code" name="data[OwnerCompany][invoice_display_postal_code]" value="{{$request->data['OwnerCompany']['invoice_display_postal_code']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="invoice_display_address">住所<font color="red">※任意</font></label>
                <input type="text" class="form-control" id="invoice_display_address" name="data[OwnerCompany][invoice_display_address]" value="{{$request->data['OwnerCompany']['invoice_display_address']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label">選択された店舗一覧</label>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>コード</th>
                            <th>店舗名</th>
                            <th>種別</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($saleShops as $shop)
                            <tr>
                                <td>{{ $shop->code }}</td>
                                <td>{{ $shop->name }}</td>
                                <td>売上</td>
                            </tr>
                        @endforeach
                        @foreach ($supplyShops as $shop)
                            <tr>
                                <td>{{ $shop->code }}</td>
                                <td>{{ $shop->name }}</td>
                                <td>仕入</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- hiddenで再送信 --}}
                <input type="hidden" name="data[OwnerCompany][selected_shop_ids]" value="{{ $request->data['OwnerCompany']['selected_shop_ids'] }}">
            </div>
            <br>
            <button id="create-submit-btn" type="submit" class="btn btn-primary">登録完了画面へ</button>
            <input type='hidden' name="data[OwnerCompany][owner_company_id]" value="{{isset($request->data['OwnerCompany']['owner_company_id']) ? $request->data['OwnerCompany']['owner_company_id'] : 0}}">
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