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
                            <td colspan="2">
                                <div class="table-th-create-date">登録日付</div>
                                <div class="table-td-date">
                                    <input type="date" class="search-control" id="sale_date_from" name="data[product][date_from]" value="{{$condition_date_from}}" tabindex="1">
                                </div>
                                <div class="table-td-date">
                                    <input type="date" class="search-control" id="sale_date_to" name="data[product][date_to]" value="{{$condition_date_to}}" tabindex="2">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="table-th">製品種別</div>
                                <div class="table-td table-code-td">
                                    <select class="search-control" id="product_type" name="data[product][product_type]">
                                        @foreach ($productTypeList as $product_types)
                                            <option value="{{ $product_types->id }}" {{ $product_types->id == $condition_product_type ? 'selected' : '' }}>
                                                {{ $product_types->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="product_type_selected" value="{{ $condition_product_type }}">
                                </div>
                            </td>
                            <td>
                                <div class="table-th-right">製品状態</div>
                                <div class="table-td table-code-td">
                                    <select class="search-control" id="status_id" name="data[product][status_id]">
                                        @foreach ($statusList as $statuses)
                                        <option value="{{ $statuses->id }}" {{ $statuses->id == $condition_status_id ? 'selected' : '' }}>
                                            {{ $statuses->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="status_id_selected" value="{{ $condition_status_id }}">
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td class="table-td-left">
                                <div class="table-th">文字列部分一致</div>
                                <div class="table-td table-code-td">
                                    <input type="text" class="search-control" name="data[product][search_text]" value="{{$condition_search_text}}" tabindex="3">
                                </div>
                            </td>
                            <td class="table-td-right">
                                <div class="table-th-right">コード</div>
                                <div class="table-td-code table-code-td">
                                    <input type="text" class="search-control product_code_input" id="product_code" name="data[product][product_code]" value="{{$condition_product_code}}" tabindex="4">
                                    <input type="hidden" id="product_id" name="data[product][product_id]" value="{{$condition_product_id}}">
                                </div>
                                <div class="table-td-text table-name-td">
                                    <input type="text" class="search-control" id="product_text" name="data[product][product_text]" value="{{$condition_product_text}}" readonly>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class='search-btn-area'>
                    <input type='submit' class='search-btn btn-primary' name='search-btn' id="1" tabindex="5" value='検索'>
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
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
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

            // CSVダウンロードメニュー
            $(".accordion-header").click(function() {
                $(".accordion-content").slideToggle(); // アニメーション付きで開閉
                $(".accordion-arrow").toggleClass("rotate"); // 矢印を回転
            });

            //-------------------------------------
            // autocomplete処理 製品ID
            //-------------------------------------
            $(".product_code_input").autocomplete({
                source: function(req, resp) {
                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./AjaxAutoCompleteProduct",
                        type: "POST",
                        cache: false,
                        dataType: "json",
                        data: {
                            inputText: req.term
                        },
                        success: function(o) {
                            resp(o);
                        },
                        error: function(xhr, ts, err) {
                            resp(['']);
                        }
                    });
                }
            });

            //-------------------------------------
            // Enterと-を押したときにタブ移動する処理
            //-------------------------------------
            $(document).on("keydown", "input", function(event) {

                if (event.keyCode === 13) { // Enterが押された時

                    var this_id = $(this).attr('id');

                    var tabindex = parseInt($(this).attr('tabindex'), 10);
                    if (this_id == "1") {
                        $('#index-search-form').submit();
                        return;
                    } else if (isNaN(tabindex)) return false;

                    tabindex += 1;

                    if ($('input[tabindex="' + tabindex + '"]').length) {

                        var this_val = $('input[tabindex="' + tabindex + '"]').val();
                        $('input[tabindex="' + tabindex + '"]').val("");
                        $('input[tabindex="' + tabindex + '"]').focus();
                        $('input[tabindex="' + tabindex + '"]').val(this_val);

                    } else {

                        var this_val = $('#search-btn').val();
                        $('#search-btn').val("");
                        $('#search-btn').focus();
                        $('#search-btn').val(this_val);
                    }

                    return false;

                }

            });

            //-------------------------------------
            // フォーカスアウトしたときの処理
            //-------------------------------------
            $(document).on("blur", "input", function(event) {

                var tabindex = parseInt($(this).attr('tabindex'), 10);
                var set_val = $(this).val();
                // 全角数字を半角に変換
                set_val = set_val.replace(/[０-９]/g, function(s) {
                    return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
                });
                $(this).val(set_val);
                var selector_code = $(this).attr('id');
                var selector_id = selector_code.replace('_code', '_id');
                var selector_text = selector_code.replace('_code', '_text');

                var fd = new FormData();
                fd.append("inputText", set_val);

                if (selector_code.match(/product/)) { // 製品IDの部分

                    $.ajax({
                            headers: {
                                "X-CSRF-TOKEN": $("[name='_token']").val()
                            },
                            url: "./AjaxSetProduct",
                            type: "POST",
                            dataType: "JSON",
                            data: fd,
                            processData: false,
                            contentType: false
                        })
                        .done(function(data) {

                            var before_product_id = $("#" + selector_id).val();

                            $("#" + selector_code).val(data[0]);
                            $("#" + selector_id).val(data[1]);
                            $("#" + selector_text).val(data[2]);

                        });
                }
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
        padding: 10px 25px 0px 0px;
        border: 1px solid #bcbcbc;
        border-radius: 5px;
    }

    .search-area table {
        margin: auto;
        width: 100%;
    }

    .table-td-left {
        width: 45%;
    }

    .table-td-right {
        width: 55%;
    }

    .table-th {
        width: 40%;
        padding: 15px 50px 0px 0px;
        font-size: 14px;
        float: left;
        font-weight: bolder;
        text-align: right;
    }

    .table-th-create-date {
        width: 18%;
        padding: 15px 50px 0px 0px;
        font-size: 14px;
        float: left;
        font-weight: bolder;
        text-align: right;
    }

    .table-th-right {
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
        width: 60%;
    }

    .table-td-code {
        font-size: 12px;
        float: left;
        padding: 10px 0px;
        width: 35%;
    }

    .table-td-text {
        font-size: 12px;
        float: left;
        padding: 10px 0px;
        width: 35%;
    }

    .table-td-date {
        font-size: 12px;
        float: left;
        padding: 10px 0px;
        width: 30%;
        padding-right: 30px;
    }

    .table-td select {
        width: 100%;
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
