@extends('layouts.app') @section('content')
<div class="container">
    <div class="row justify-content-center">

        <div class="top-title">請求書出力画面</div>

        <div class='search-area'>
            <form id="index-search-form" method="post" action='{{$search_action}}' enctype="multipart/form-data">
                {{ csrf_field() }}
                <table>
                    <tbody>
                        <tr>
                            <td>
                                <div class="table-th">対象日付</div>
                                <div class="table-td radio_box">
                                    <label class="radio-label"><input type="radio" name="data[InvoiceOutput][date_type]" value="1" {{$check_str_slip_date}}> 伝票日付</label>
                                    <label class="radio-label"><input type="radio" name="data[InvoiceOutput][date_type]" value="2" {{$check_str_invoiceOutput_date}}> 支払日付</label>
                                </div>
                                <div class="table-td">
                                    <input type="date" class="search-control" id="invoiceOutput_date_from" name="data[InvoiceOutput][invoiceOutput_date_from]" value="{{$condition_date_from}}" tabindex="1">
                                </div>
                                <div class="table-td">
                                    <input type="date" class="search-control" id="invoiceOutput_date_to" name="data[InvoiceOutput][invoiceOutput_date_to]" value="{{$condition_date_to}}" tabindex="2">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="table-th">支払企業</div>
                                <div class="table-td table-code-td">
                                    <input type="text" class="search-control invoiceOutput_company_code_input" id="invoiceOutput_company_code" name="data[InvoiceOutput][invoiceOutput_company_code]" value="{{$condition_company_code}}" tabindex="3">
                                    <input type="hidden" id="invoiceOutput_company_id" name="data[InvoiceOutput][invoiceOutput_company_id]" value="{{$condition_company_id}}">
                                </div>
                                <div class="table-td table-name-td">
                                    <input type="text" class="search-control" id="invoiceOutput_company_text" name="data[InvoiceOutput][invoiceOutput_company_text]" value="{{$condition_company_text}}" readonly>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="btn-area">
                    <div class='search-btn-area'>
                        <input type='submit' class='search-btn btn-primary' name='search-btn' id="search-btn" value='検索' tabindex="7">
                        <input type='submit' class='initial-btn' name='reset-btn' id="reset-btn" value='検索条件リセット' tabindex="8">
                    </div>
                </div>
            </form>
        </div>

        <form id="pdf-output-form" method="post" action='./InvoiceOutputOutput' enctype="multipart/form-data" onsubmit="return inputCheck();">
            {{ csrf_field() }}
            <div class='list-area'>
                <table class='index-table'>
                    <tbody>
                        <tr>
                            <th class="width-5 center">印刷</th>
                            <th class="width-15">伝票日付</th>
                            <th class="width-15">支払日付</th>
                            <th class="width-15">企業</th>
                            <th class="width-10">支払金額</th>
                        </tr>
                        @foreach ($depositList as $depositDatas)
                        <tr>
                            <td class='center'><input type='checkbox' id="output-{{$depositDatas->deposit_id}}" name="data['InvoiceOutput'][{{$depositDatas->deposit_id}}]['id']" value="{{$depositDatas->deposit_id}}" onchange="javascript:discardDepositId({{$depositDatas->deposit_id}})"></td>
                            <td>{{$depositDatas->deposit_date}}</td>
                            <td>{{$depositDatas->sale_from_date}}~{{$depositDatas->sale_to_date}}</td>
                            <td>{{$depositDatas->sale_company_name}}</td>
                            <td>{{number_format($depositDatas->amount)}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $depositList->links() }}
            </div>

            <br>
            <br>

            <div id="target-id-area"></div>
            <div class="output-btn-area">
                <input type='submit' class='output-btn btn btn-primary width-30' name='output-btn' id="output-btn" value='印刷' tabindex="9"> {{-- <button id="output-btn" class="output-btn btn btn-primary width-30" type="button" tabindex="9">印刷</button>                --}}
            </div>
        </form>

    </div>
</div>
@endsection
<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript">
    (function($) {
        jQuery(window).load(function() {

            // 検索されて選択状態の企業を取得
            var supply_submit_type_selected = $("#supply_submit_type_selected").val();
            // 検索条件で設定された企業を設定
            $('#supply_submit_type').val(supply_submit_type_selected);

            //-------------------------------------
            // Enterと-を押したときにタブ移動する処理
            //-------------------------------------
            $(document).on("keyup", "input", function(event) {

                if (event.keyCode === 13) { // Enterが押された時

                    var this_id = $(this).attr('id');

                    var tabindex = parseInt($(this).attr('tabindex'), 10);
                    if (isNaN(tabindex) && this_id == "search-btn") {
                        $('#index-search-form').submit();
                        return;
                    } else if (isNaN(tabindex) && this_id == "output-btn") {
                        $('#pdf-output-form').submit();
                        return;
                    } else if (isNaN(tabindex)) return false;

                    tabindex += 1;

                    if ($('input[tabindex="' + tabindex + '"]').length) {

                        var this_val = $('input[tabindex="' + tabindex + '"]').val();
                        $('input[tabindex="' + tabindex + '"]').val("");
                        $('input[tabindex="' + tabindex + '"]').focus();
                        $('input[tabindex="' + tabindex + '"]').val(this_val);

                    } else {

                        $('#search-btn').focus();
                    }

                    return false;

                } else if (event.keyCode === 111) { // スラッシュが押された時

                    var this_id = $(this).attr('id');

                    // 文字列の最後の文字を削除
                    $(this).val($(this).val().slice(0, -1));

                    if (this_id == "search-btn") { // 検索ボタンの場合

                        var this_val = $('input[tabindex="3"]').val();
                        $('input[tabindex="3"]').val("");
                        $('input[tabindex="3"]').focus();
                        $('input[tabindex="3"]').val(this_val);

                    } else {

                        var tabindex = parseInt($(this).attr('tabindex'), 10);
                        if (isNaN(tabindex)) return false;

                        tabindex -= 1;

                        if ($('input[tabindex="' + tabindex + '"]').length) {
                            var this_val = $('input[tabindex="' + tabindex + '"]').val();
                            $('input[tabindex="' + tabindex + '"]').val("");
                            $('input[tabindex="' + tabindex + '"]').focus();
                            $('input[tabindex="' + tabindex + '"]').val(this_val);
                        }

                    }

                    return false;
                }

            });

            //-------------------------------------
            // autocomplete処理 仕入企業ID
            //-------------------------------------
            $(".invoiceOutput_company_code_input").autocomplete({
                source: function(req, resp) {
                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./AjaxAutoCompleteSaleCompany",
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
            // フォーカスアウトしたときの処理
            //-------------------------------------
            $(document).on("blur", "input", function(event) {

                var tabindex = parseInt($(this).attr('tabindex'), 10);
                var set_val = $(this).val();
                var selector_code = $(this).attr('id');
                var selector_id = selector_code.replace('_code', '_id');
                var selector_text = selector_code.replace('_code', '_text');

                var fd = new FormData();
                fd.append("inputText", set_val);

                if (selector_code.match(/invoiceOutput_company/)) { // 仕入先企業

                    $.ajax({
                            headers: {
                                "X-CSRF-TOKEN": $("[name='_token']").val()
                            },
                            url: "./AjaxSetSaleCompany",
                            type: "POST",
                            dataType: "JSON",
                            data: fd,
                            processData: false,
                            contentType: false
                        })
                        .done(function(data) {

                            $("#" + selector_code).val(data[0]);
                            $("#" + selector_id).val(data[1]);
                            $("#" + selector_text).val(data[2]);
                        });

                }
            });


        });
    })(jQuery);

    // -------------------
    // deposit_idの取得
    // -------------------
    function discardDepositId(id) {

        // 対象IDのチェック状態を取得
        var isCheck = $('#output-' + id).prop('checked');

        if (isCheck) {
            if (!($('#output-detail-id-' + id).length)) {
                // チェックしたら詳細エリアにIDを追加
                $('#target-id-area').append('<input type="hidden" id="output-detail-id-' + id + '" name="data[InvoiceOutput][deposit_ids][]" value="' + id + '">');
            }
        } else {
            if ($('#output-detail-id-' + id).length) {
                // チェック外れたらIDを詳細エリアから外す
                $('#output-detail-id-' + id).remove();
            }
        }
    }

    // --------------
    // 印刷時のチェック
    // --------------
    function inputCheck() {

        var checkCnt = $('.list-area :checked').length;

        if (checkCnt == 0) {
            alert('印刷対象を選択してください。');
            return false;
        }
    }
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

    .search-control[readonly] {
        background-color: #e9ecef;
        opacity: 1;
    }

    .top-title {
        max-width: 1300px;
        font-size: 1.4em;
        font-weight: bold;
        width: 90%;
        padding: 25px 0px 25px 20px;
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
        width: 10%;
        padding: 15px 0px 0px 10px;
        font-size: 10px;
        float: left;
        font-weight: bolder;
    }

    .table-td {
        width: 20%;
        padding: 10px;
        font-size: 10px;
        float: left;
    }

    .table-code-td {
        padding-right: 0px;
    }

    .table-name-td {
        padding-left: 0px;
    }

    .radio_box {
        padding-top: 15px;
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

    .center {
        text-align: center;
    }

    .width-5 {
        width: 5%!important;
    }

    .width-10 {
        width: 10%!important;
    }

    .width-15 {
        width: 15%!important;
    }

    .width-20 {
        width: 20%!important;
    }

    .width-30 {
        width: 30%!important;
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

    .output-btn-area {
        width: 100%;
        text-align: center;
    }

    #pdf-output-form {
        width: 100%;
    }
</style>
