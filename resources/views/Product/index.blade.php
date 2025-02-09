@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">登録製品一覧</div>

        <!--検索エリア-->
        <div class='search-area'>
            <form id="index-search-form" method="post" action='{{$search_action}}' enctype="multipart/form-data">
                {{ csrf_field() }}
                <table>
                    <tbody>
                        <tr>
                            <td>
                                <div class="table-th">検索対象</div>
                                <div class="table-td radio_box">
                                    <label class="radio-label"><input type="radio" name="data[Product][product_search_type]" value="1" {{$product_search_type_name}}> 製品名</label>
                                    <label class="radio-label"><input type="radio" name="data[Product][product_search_type]" value="2" {{$product_search_type_code}}> コード</label>
                                </div>
                                <div class="table-td">
                                    <input type="text" class="search-control" name="data[Product][product_search_text]" value="{{$product_search_text}}" tabindex="1">
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class='search-btn-area'>
                    <input type='submit' class='search-btn btn-primary' name='search-btn' id="1" value='検索'>
                    <input type='submit' class='initial-btn' name='reset-btn' id="2" value='検索条件リセット'>
                </div>
            </form>
        </div>
        <div class="accordion">
            <div class="accordion-header">
                <span class="accordion-arrow">▼</span>
                <span>CSV出力メニュー</span>
            </div>
            <div class="accordion-content">
                <table class="csv-type-table-area">
                    <tr>
                        <th>データ種別</th>
                        <td>
                            <select class="file-control" name="data_type_val" id="data_type_val">
                                @foreach($csv_type_arr as $csv_type_key => $csv_type_val)
                                <option value="{{$csv_type_key}}">{{$csv_type_val}}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                </table>
                <div class="csv-btn-area">
                    <a href="./Product/csv-downloag">
                        <input type='button' class='search-btn btn-primary' name='search-btn' id="1" value='CSVダウンロード'>
                    </a>
                </div>
            </div>
        </div>

        <!--一覧表示エリア-->
        <div class='list-area'>
            <table class='index-table '>
                <tbody>
                    <tr>
                        <th>製品種別</th>
                        <th>製品状態</th>
                        <th>コード</th>
                        <th class="double-width" colspan="2">品名</th>
                        <th>税率</th>
                        <th>単位</th>
                        @if (Home::authOwnerCheck())
                        <th>編集</th> @endif
                    </tr>
                    @foreach ($productList as $products)
                    <tr>
                        <td>{{$products->product_type_name}}</td>
                        <td>{{$products->status_name}}</td>
                        <td>{{$products->product_code}}</td>
                        <td class="double-width" colspan="2 ">{{$products->product_name}}</td>
                        <td>{{$products->tax_name}}</td>
                        <td>{{$products->unit_name}}</td>
                        @if (Home::authOwnerCheck())
                        <td><a class='edit-btn' href='./ProductEdit/{{$products->product_id}}'>編集</a></td> @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center">
            {{ $productList->links() }}
        </div>

    </div>
</div>
@endsection
<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<script type="text/javascript">
    (function($) {
        jQuery(window).load(function() {

            // 検索されて選択状態の役職を取得
            var product_type_selected = $("#product_type_selected").val();
            // 検索条件で設定された役職を設定
            $('#product_type').val(product_type_selected);

            // 検索されて選択状態の役職を取得
            var status_id_selected = $("#status_id_selected").val();
            // 検索条件で設定された役職を設定
            $('#status_id').val(status_id_selected);

            // 検索されて選択状態の役職を取得
            var tax_id_selected = $("#tax_id_selected").val();
            // 検索条件で設定された役職を設定
            $('#tax_id').val(tax_id_selected);

            // 検索されて選択状態の役職を取得
            var unit_id_selected = $("#unit_id_selected").val();
            // 検索条件で設定された役職を設定
            $('#unit_id').val(unit_id_selected);

            // CSVダウンロードメニュー
            $(".accordion-header").click(function() {
                $(".accordion-content").slideToggle(); // アニメーション付きで開閉
                $(".accordion-arrow").toggleClass("rotate"); // 矢印を回転
            });

        });
    })(jQuery);
</script>

<style>
    /* 共通 */
    
    .search-control {
        display: block;
        width: 100%;
        height: 30px;
        padding: 5px;
        font-size: 10px;
        line-height: 1.6;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    }
    
    .top-title {
        max-width: 1300px;
        font-size: 1.4em;
        font-weight: bold;
        width: 90%;
        padding: 25px 0px 25px 20px;
    }
    
    .search-control[readonly] {
        background-color: #e9ecef;
        opacity: 1;
    }
    
    .radio-label {
        margin-bottom: initial!important;
        font-weight: bolder;
        margin-right: 10px;
    }
    
    .search-area {
        max-width: 1300px;
        width: 90%;
        padding: 10px 0px 0px;
        border: 1px solid #bcbcbc;
        border-radius: 5px;
    }
    
    .search-area table {
        margin: auto;
        width: 100%;
    }
    
    .table-th {
        width: 30%;
        padding: 15px 50px 0px 0px;
        font-size: 14px;
        float: left;
        font-weight: bolder;
        text-align: right;
    }
    
    .table-td {
        font-size: 12px;
        float: left;
        padding: 10px 0px;
        width: 30%;
    }
    
    .radio_box {
        width: 20%;
        padding: 17px 10px;
        font-size: 12px;
        float: left;
    }
    
    .search-btn-area {
        text-align: center;
        margin: 10px auto 10px;
        width: 100%;
        display: inline-block;
    }
    
    .search-btn {
        width: 80%;
        font-size: 10px;
        max-width: 150px;
        height: 30px;
        border-radius: 10px;
        margin-right: 2%;
    }
    
    .initial-btn {
        width: 80%;
        font-size: 10px;
        max-width: 150px;
        height: 30px;
        border-radius: 10px;
        margin-left: 2%;
    }
    
    .list-area {
        max-width: 1300px;
        width: 90%;
        margin: 25px auto 50px;
    }
    
    .index-table {
        width: 100%;
        letter-spacing: 2px;
        border-top: solid 1px #ccc;
        border-bottom: solid 2px #ccc;
        margin: 5px 0px;
    }
    
    .index-table th {
        width: 10%;
        padding: 10px;
        padding-left: 10px;
        background-color: #57595b;
        font-weight: bold;
        color: white;
        font-size: 10px;
        letter-spacing: 1px;
        border: 1px solid #bcbcbc;
    }
    
    .index-table td {
        font-size: 10px;
        padding-left: 20px;
        padding: 8px;
        border: 1px solid #bcbcbc;
        width: 10%;
    }
    
    .double-width {
        width: 20%!important;
    }
    
    .edit-btn {
        border-radius: 5px;
        color: #fff;
        background-color: #72be92;
        width: 80%;
        margin: auto;
        display: block;
        text-align: center;
        padding: 10px;
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
        width: 90%;
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
    
    .csv-type-table-area th {
        font-size: 12px;
    }
    
    .csv-type-table-area td select {
        font-size: 12px;
        border-radius: 5px;
        margin-left: 20px;
    }
    
    .csv-btn-area {
        margin-top: 20px;
    }
    
    .csv-btn-area input {
        max-width: 120px;
    }
</style>