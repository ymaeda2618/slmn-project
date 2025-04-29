@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">本部企業 編集画面</div>

        @if(!empty($error_message))
        <div class="error-alert">{{$error_message}}</div>
        @endif

        <form class="event-form" id="event-create-form" method="post" action="../OwnerCompanyConfirm" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group">
                <label class="column-label" for="code">コード番号<font color="red">※任意</font></label>
                <input type="tel" class="form-control" id="code" name="data[OwnerCompany][code]" value="{{$editOwnerCompany->code}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="owner_company_name">本部企業名</label>
                <input type="text" class="form-control" id="owner_company_name" name="data[OwnerCompany][owner_company_name]" value="{{$editOwnerCompany->owner_company_name}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="yomi">ヨミガナ</label>
                <input type="text" class="form-control" id="yomi" name="data[OwnerCompany][yomi]" value="{{$editOwnerCompany->yomi}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="tax_calc_type">消費税計算区分</label>
                <select class="form-control" id="tax_calc_type" name="data[OwnerCompany][tax_calc_type]">
                    <option value="0" selected>伝票ごとに計算</option>
                    <option value="1">請求書ごとに計算</option>
                </select>
                <input type='hidden' id="tax_calc_type_selected" value="{{$editOwnerCompany->tax_calc_type}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="closing_date">締め日</label>
                <select class="form-control" id="closing_date" name="data[OwnerCompany][closing_date]">
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
                <input type='hidden' id="closing_date_selected" value="{{$editOwnerCompany->closing_date}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="postal_code">郵便番号※ハイフンなし数字のみ<font color="red">※任意</font></label>
                <input type="text" class="form-control" id="postal_code" name="data[OwnerCompany][postal_code]" value="{{$editOwnerCompany->postal_code}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="address">住所<font color="red">※任意</font></label>
                <input type="text" class="form-control" id="address" name="data[OwnerCompany][address]" value="{{$editOwnerCompany->address}}">
            </div>

            <div class="accordion">
                <div class="accordion-header">
                    <span class="accordion-arrow">▼</span>
                    <span>銀行情報</span>
                </div>
                <div class="accordion-content">
                    <div class="form-group">
                        <label class="column-label" for="bank_code">金融機関コード<font color="red">※任意</font></label>
                        <input type="text" class="form-control" id="bank_code" name="data[OwnerCompany][bank_code]" value="{{$editOwnerCompany->bank_code}}">
                    </div>
                    <div class="form-group">
                        <label class="column-label" for="bank_name">銀行名<font color="red">※任意</font></label>
                        <input type="text" class="form-control" id="bank_name" name="data[OwnerCompany][bank_name]" value="{{$editOwnerCompany->bank_name}}">
                    </div>
                    <div class="form-group">
                        <label class="column-label" for="branch_code">支店コード<font color="red">※任意</font></label>
                        <input type="text" class="form-control" id="branch_code" name="data[OwnerCompany][branch_code]" value="{{$editOwnerCompany->branch_code}}">
                    </div>
                    <div class="form-group">
                        <label class="column-label" for="branch_name">支店名<font color="red">※任意</font></label>
                        <input type="text" class="form-control" id="branch_name" name="data[OwnerCompany][branch_name]" value="{{$editOwnerCompany->branch_name}}">
                    </div>
                    <div class="form-group">
                        <label class="column-label" for="bank_type">口座種別<font color="red">※任意</font></label>
                        <select class="file-control" id="bank_type" name="data[OwnerCompany][bank_type]">
                            <option value="0">-</option>
                            <option value="1">普通</option>
                            <option value="2">当座</option>
                            <option value="3">その他</option>
                        </select>
                        <input type='hidden' id="bank_type_selected" value="{{$editOwnerCompany->bank_type}}">
                    </div>
                    <div class="form-group">
                        <label class="column-label" for="bank_account">口座番号<font color="red">※任意</font></label>
                        <input type="text" class="form-control" id="bank_account" name="data[OwnerCompany][bank_account]" value="{{$editOwnerCompany->bank_account}}">
                    </div>
                </div>
            </div>

            <div class="accordion">
                <div class="accordion-header">
                    <span class="accordion-arrow">▼</span>
                    <span>請求書情報</span>
                </div>
                <div class="accordion-content">
                    <div class="form-group">
                        <label class="column-label" for="invoice_output_type">請求書出力タイプ<font color="red">※任意</font></label>
                        <select class="file-control" id="invoice_output_type" name="data[OwnerCompany][invoice_output_type]">
                            <option value="0">本部企業毎</option>
                            <option value="1">店舗毎</option>
                        </select>
                        <input type='hidden' id="invoice_output_type_selected" value="{{$editOwnerCompany->invoice_output_type}}">
                    </div>
                    <div class="form-group">
                        <label class="column-label" for="invoice_display_flg">請求書表示フラグ<font color="red">※任意</font></label>
                        <select class="file-control" id="invoice_display_flg" name="data[OwnerCompany][invoice_display_flg]">
                            <option value="0">無効</option>
                            <option value="1">有効</option>
                        </select>
                        <input type='hidden' id="invoice_display_flg_selected" value="{{$editOwnerCompany->invoice_display_flg}}">
                    </div>
                    <div class="form-group">
                        <label class="column-label" for="invoice_display_name">請求書表示名<font color="red">※任意</font></label>
                        <input type="text" class="form-control" id="invoice_display_name" name="data[OwnerCompany][invoice_display_name]" value="{{$editOwnerCompany->invoice_display_name}}">
                    </div>
                    <div class="form-group">
                        <label class="column-label" for="invoice_display_postal_code">郵便番号※ハイフンなし数字のみ<font color="red">※任意</font></label>
                        <input type="text" class="form-control" id="invoice_display_postal_code" name="data[OwnerCompany][invoice_display_postal_code]" value="{{$editOwnerCompany->invoice_display_postal_code}}">
                    </div>
                    <div class="form-group">
                        <label class="column-label" for="invoice_display_address">住所<font color="red">※任意</font></label>
                        <input type="text" class="form-control" id="invoice_display_address" name="data[OwnerCompany][invoice_display_address]" value="{{$editOwnerCompany->invoice_display_address}}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="column-label">店舗選択</label><br>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#shopSelectModal">
                    店舗を選択
                </button>
                <span id="selectedShopCount" class="ml-3 font-weight-bold text-primary">選択中：0件</span>
                <input type="hidden" name="data[OwnerCompany][selected_shop_ids]" id="selectedShopIds">
            </div>
            <br>
            <button id="create-submit-btn" type="submit" class="btn btn-primary">編集確認画面へ</button>
            <input type='hidden' name="data[OwnerCompany][owner_company_id]" value="{{$editOwnerCompany->owner_company_id}}">
            <input type='hidden' name="submit_type" value="2">
        </form>

    </div>
</div>
@endsection
<div class="modal fade" id="shopSelectModal" tabindex="-1" role="dialog" aria-labelledby="shopModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl custom-modal-wide" role="document">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title">店舗一覧から選択</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="閉じる">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <input type="text" id="shopSearchInput" class="form-control" placeholder="コードまたは店舗名、ヨミガナで検索">
                </div>
                <div class="modal-footer mb-2">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
                    <button type="button" class="btn btn-primary" id="confirmShopSelection">選択を確定</button>
                </div>
                <table class="table table-bordered table-hover shop-select-table">
                    <thead>
                    <tr>
                        <th>選択</th>
                        <th>コード</th>
                        <th>店舗名</th>
                        <th>ヨミガナ</th>
                        <th>種別</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($saleCompanies as $shop)
                        <tr>
                        <td>
                            <input type="checkbox" class="shop-checkbox" value="sale:{{ $shop->id }}"
                                {{ $shop->owner_company_id == $editOwnerCompany->owner_company_id ? 'checked' : '' }}>
                        </td>
                        <td>{{ $shop->code }}</td>
                        <td>{{ $shop->name }}</td>
                        <td>{{ $shop->yomi ? $shop->yomi : '-' }}</td>
                        <td>売上</td>
                        </tr>
                    @endforeach
                    @foreach ($supplyCompanies as $shop)
                        <tr>
                        <td>
                            <input type="checkbox" class="shop-checkbox" value="supply:{{ $shop->id }}"
                                {{ $shop->owner_company_id == $editOwnerCompany->owner_company_id ? 'checked' : '' }}>
                        </td>
                        <td>{{ $shop->code }}</td>
                        <td>{{ $shop->name }}</td>
                        <td>{{ $shop->yomi ? $shop->yomi : '-' }}</td>
                        <td>仕入</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<script type="text/javascript">
    (function($) {
        jQuery(window).load(function() {

            // 検索されて選択状態の消費税計算区分を取得
            var tax_calc_type_selected = $("#tax_calc_type_selected").val();
            // 検索条件で設定された消費税計算区分を設定
            $('#tax_calc_type').val(tax_calc_type_selected);

            // 検索されて選択状態の締め日を取得
            var closing_date_selected = $("#closing_date_selected").val();
            // 検索条件で設定された締め日を設定
            $('#closing_date').val(closing_date_selected);

            // 検索されて選択状態の口座種別を取得
            var bank_type_selected = $("#bank_type_selected").val();
            // 検索条件で設定された口座種別を設定
            $('#bank_type').val(bank_type_selected);

            // 検索されて選択状態の請求表示フラグを取得
            var invoice_display_flg_selected = $("#invoice_display_flg_selected").val();
            // 検索条件で設定された請求表示フラグを設定
            $('#invoice_display_flg').val(invoice_display_flg_selected);
        });

    })(jQuery);
    $(document).ready(function () {
        // 初期状態でチェックされている店舗をhiddenと件数表示に反映
        const preselected = [];
        $('.shop-checkbox:checked').each(function () {
            preselected.push($(this).val());
        });
        $('#selectedShopIds').val(preselected.join(','));
        $('#selectedShopCount').text(`選択中：${preselected.length}件`);

        // 検索処理
        $('#shopSearchInput').on('keyup', function () {
            const keyword = $(this).val().toLowerCase();

            $('#shopSelectModal tbody tr').each(function () {
                const code = $(this).find('td').eq(1).text().toLowerCase();  // コード
                const name = $(this).find('td').eq(2).text().toLowerCase();  // 店舗名
                const yomi = $(this).find('td').eq(3).text().toLowerCase();  // ヨミガナ

                if (code.includes(keyword) || name.includes(keyword) || yomi.includes(keyword)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        $('#confirmShopSelection').on('click', function () {
            const selected = [];

            $('.shop-checkbox:checked').each(function () {
                selected.push($(this).val());
            });

            $('#selectedShopIds').val(selected.join(','));

            // 件数をカウントして表示
            $('#selectedShopCount').text(`選択中：${selected.length}件`);

            $('#shopSelectModal').modal('hide');
        });

        $(".accordion-header").click(function() {
            $(this).next(".accordion-content").slideToggle(); // 押したやつだけ開閉
            $(this).find(".accordion-arrow").toggleClass("rotate"); // 押したやつだけ矢印回転
        });
    });
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

    .custom-modal-wide {
        max-width: 65% !important;
    }

    .shop-select-table th,
    .shop-select-table td {
        white-space: nowrap; /* 折り返さないようにする */
        font-size: 13px;     /* 文字が大きければ小さく調整 */
        vertical-align: middle;
    }

    .shop-select-table th:nth-child(1), /* 選択 */
    .shop-select-table td:nth-child(1) {
        width: 60px;
        text-align: center;
    }

    .shop-select-table th:nth-child(5), /* 種別 */
    .shop-select-table td:nth-child(5) {
        width: 80px;
        text-align: center;
    }

    /* アコーディオン全体のデザイン */

    .accordion {
        max-width: 1300px;
        width: 90%;
        margin: 20px auto;
        border: 1px solid #ccc;
        border-radius: 5px;
        overflow: hidden;
    }
    /* アコーディオン全体 */

    .accordion {
        max-width: 1300px;
        width: 100%;
        margin: 20px auto;
        border: 1px solid #ccc;
        border-radius: 5px;
        overflow: hidden;
    }
    /* ヘッダー部分（ボタン） */

    .accordion-header {
        padding: 5px;
        cursor: pointer;
        font-size: 12px;
        display: flex;
        align-items: center;
    }

    .accordion-header span {
        margin-left: 10px;
    }
    /* 矢印アイコン */

    .accordion-arrow {
        font-size: 12px;
        transition: transform 0.3s ease;
    }
    /* アコーディオンの内容（最初は非表示） */

    .accordion-content {
        display: none;
        padding: 15px;
        background: #f9f9f9;
        border-top: 1px solid #ccc;
    }
    /* 矢印が回転するクラス */

    .rotate {
        transform: rotate(180deg);
    }
</style>