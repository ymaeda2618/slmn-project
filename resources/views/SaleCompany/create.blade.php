@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">売上先店舗 新規成画面</div>

        @if(!empty($error_message))
        <div class="error-alert">{{$error_message}}</div>
        @endif

        <form class="smn-form" id="event-create-form" method="post" action="./SaleCompanyConfirm" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group">
                <label class="column-label" for="code">コード番号<font color="red">※任意</font></label>
                <input type="tel" class="form-control" id="code" name="data[SaleCompany][code]">
            </div>
            <div class="form-group">
                <label class="column-label" for="sale_company_name">売上先店舗名</label>
                <input type="text" class="form-control" id="sale_company_name" name="data[SaleCompany][sale_company_name]">
            </div>
            <div class="form-group">
                <label class="column-label" for="yomi">ヨミガナ</label>
                <input type="text" class="form-control" id="yomi" name="data[SaleCompany][yomi]">
            </div>
            <div class="form-group">
                <label class="column-label" for="tax_calc_type">消費税計算区分</label>
                <select class="form-control" id="tax_calc_type" name="data[SaleCompany][tax_calc_type]">
                    <option value="0" selected>伝票ごとに計算</option>
                    <option value="1">請求書ごとに計算</option>
                </select>
            </div>
            <div class="form-group">
                <label class="column-label" for="closing_date">締め日</label>
                <select class="form-control" id="closing_date" name="data[SaleCompany][closing_date]">
                    <option value="99">月末</option>
                    <option value="88">都度</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                    <option value="9">9</option>
                    <option value="10">10</option>
                    <option value="11">11</option>
                    <option value="12">12</option>
                    <option value="13">13</option>
                    <option value="14">14</option>
                    <option value="15">15</option>
                    <option value="16">16</option>
                    <option value="17">17</option>
                    <option value="18">18</option>
                    <option value="19">19</option>
                    <option value="20">20</option>
                    <option value="21">21</option>
                    <option value="22">22</option>
                    <option value="23">23</option>
                    <option value="24">24</option>
                    <option value="25">25</option>
                    <option value="26">26</option>
                    <option value="27">27</option>
                    <option value="28">28</option>
                    <option value="29">29</option>
                    <option value="30">30</option>
                    <option value="31">31</option>
                </select>
            </div>
            <div class="form-group">
                <label class="column-label" for="postal_code">郵便番号※ハイフンなし数字のみ<font color="red">※任意</font></label>
                <input type="text" class="form-control" id="postal_code" name="data[SaleCompany][postal_code]">
            </div>
            <div class="form-group">
                <label class="column-label" for="address">住所<font color="red">※任意</font></label>
                <input type="text" class="form-control" id="address" name="data[SaleCompany][address]">
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_code">金融機関コード<font color="red">※任意</font></label>
                <input type="text" class="form-control" id="bank_code" name="data[SaleCompany][bank_code]">
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_name">銀行名<font color="red">※任意</font></label>
                <input type="text" class="form-control" id="bank_name" name="data[SaleCompany][bank_name]">
            </div>
            <div class="form-group">
                <label class="column-label" for="branch_code">支店コード<font color="red">※任意</font></label>
                <input type="text" class="form-control" id="branch_code" name="data[SaleCompany][branch_code]">
            </div>
            <div class="form-group">
                <label class="column-label" for="branch_name">支店名<font color="red">※任意</font></label>
                <input type="text" class="form-control" id="branch_name" name="data[SaleCompany][branch_name]">
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_type">口座種別<font color="red">※任意</font></label>
                <select class="file-control" id="bank_type" name="data[SaleCompany][bank_type]">
                    <option value="0">-</option>
                    <option value="1">普通</option>
                    <option value="2">当座</option>
                    <option value="3">その他</option>
                </select>
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_account">口座番号<font color="red">※任意</font></label>
                <input type="text" class="form-control" id="bank_account" name="data[SaleCompany][bank_account]">
            </div>
            <div class="form-group">
                <label class="column-label" for="invoice_display_flg">請求書表示フラグ<font color="red">※任意</font></label>
                <select class="file-control" id="invoice_display_flg" name="data[SaleCompany][invoice_display_flg]">
                    <option value="0">無効</option>
                    <option value="1">有効</option>
                </select>
            </div>
            <div class="form-group">
                <label class="column-label" for="invoice_display_name">請求書表示名<font color="red">※任意</font></label>
                <input type="text" class="form-control" id="invoice_display_name" name="data[SaleCompany][invoice_display_name]">
            </div>
            <div class="form-group">
                <label class="column-label" for="invoice_display_postal_code">請求書表示郵便番号※ハイフンなし数字のみ<font color="red">※任意</font></label>
                <input type="text" class="form-control" id="invoice_display_postal_code" name="data[SaleCompany][invoice_display_postal_code]">
            </div>
            <div class="form-group">
                <label class="column-label" for="invoice_display_address">請求書表示住所<font color="red">※任意</font></label>
                <input type="text" class="form-control" id="invoice_display_address" name="data[SaleCompany][invoice_display_address]">
            </div>
            <br>
            <button id="create-submit-btn" type="submit" class="btn btn-primary">確認登録画面へ</button>
            <input type='hidden' name="submit_type" value="1">
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