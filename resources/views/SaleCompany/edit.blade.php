@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">売上先企業 編集画面</div>

        <form class="event-form" id="event-create-form" method="post" action="../SaleCompanyConfirm" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group">
                <label class="column-label" for="sale_company_name">売上先企業名</label>
                <input type="text" class="form-control" id="sale_company_name" name="data[SaleCompany][sale_company_name]" value="{{$editSaleCompany->sale_company_name}}">
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
                </select>
                <input type='hidden' id="closing_date_selected" value="{{$editSaleCompany->closing_date}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="postal_code">郵便番号※ハイフンなし数字のみ</label>
                <input type="text" class="form-control" id="postal_code" name="data[SaleCompany][postal_code]" value="{{$editSaleCompany->postal_code}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="address">住所</label>
                <input type="text" class="form-control" id="address" name="data[SaleCompany][address]" value="{{$editSaleCompany->address}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_code">金融機関コード</label>
                <input type="text" class="form-control" id="bank_code" name="data[SaleCompany][bank_code]" value="{{$editSaleCompany->bank_code}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_name">銀行名</label>
                <input type="text" class="form-control" id="bank_name" name="data[SaleCompany][bank_name]" value="{{$editSaleCompany->bank_name}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="branch_code">支店コード</label>
                <input type="text" class="form-control" id="branch_code" name="data[SaleCompany][branch_code]" value="{{$editSaleCompany->branch_code}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="branch_name">支店名</label>
                <input type="text" class="form-control" id="branch_name" name="data[SaleCompany][branch_name]" value="{{$editSaleCompany->branch_name}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_type">口座種別</label>
                <select class="file-control" id="bank_type" name="data[SaleCompany][bank_type]">
                    <option value="0">-</option>
                    <option value="1">普通</option>
                    <option value="2">当座</option>
                    <option value="3">その他</option>
                </select>
                <input type='hidden' id="bank_type_selected" value="{{$editSaleCompany->bank_type}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="bank_account">口座番号</label>
                <input type="text" class="form-control" id="bank_account" name="data[SaleCompany][bank_account]" value="{{$editSaleCompany->bank_account}}">
            </div>
            <br>
            <button id="create-submit-btn" type="submit" class="btn btn-primary">編集確認画面へ</button>
            <input type='hidden' name="data[SaleCompany][sale_company_id]" value="{{$editSaleCompany->sale_company_id}}">
            <input type='hidden' name="submit_type" value="2">
        </form>

    </div>
</div>
@endsection

<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<script type="text/javascript">
    (function($) {
        jQuery(window).load(function() {

            // 検索されて選択状態の役職を取得
            var closing_date_selected = $("#closing_date_selected").val();
            // 検索条件で設定された役職を設定
            $('#closing_date').val(closing_date_selected);

            // 検索されて選択状態の役職を取得
            var bank_type_selected = $("#bank_type_selected").val();
            // 検索条件で設定された役職を設定
            $('#bank_type').val(bank_type_selected);
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
