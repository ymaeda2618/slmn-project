@extends('layouts.app') @section('content')
<div class="container">
    <div class="row justify-content-center">

        <div class="top-title">請求一覧</div>

        <div class='search-area'>
            <form id="index-search-form" method="post" action='{{$search_action}}' enctype="multipart/form-data">
                {{ csrf_field() }}
                <table>
                    <tbody>
                        <tr>
                            <td>
                                <div class="table-th">対象日付</div>
                                <div class="table-td radio_box">
                                    <label class="radio-label"><input type="radio" name="data[Deposit][date_type]" value="1" {{$check_str_slip_date}}> 伝票日付</label>
                                    <label class="radio-label"><input type="radio" name="data[Deposit][date_type]" value="2" {{$check_str_deposit_date}}> 入金日付</label>
                                </div>
                                <div class="table-td">
                                    <input type="date" class="search-control" id="deposit_date_from" name="data[Deposit][deposit_date_from]" value="{{$condition_date_from}}" tabindex="1">
                                </div>
                                <div class="table-td">
                                    <input type="date" class="search-control" id="deposit_date_to" name="data[Deposit][deposit_date_to]" value="{{$condition_date_to}}" tabindex="2">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="table-th">伝票NO</div>
                                <div class="table-td table-code-td slip-td">
                                    <input type="number" class="search-control" id="deposit_id" name="data[Deposit][id]" value="{{$condition_id}}" tabindex="3">
                                </div>
                                <div class="table-th">取引先店舗</div>
                                <div class="table-td table-code-td">
                                    <input type="text" class="search-control deposit_company_code_input" id="deposit_company_code" name="data[Deposit][deposit_company_code]" value="{{$condition_company_code}}" tabindex="4">
                                    <input type="hidden" id="deposit_company_id" name="data[Deposit][deposit_company_id]" value="{{$condition_company_id}}">
                                </div>
                                <div class="table-td table-name-td">
                                    <input type="text" class="search-control" id="deposit_company_text" name="data[Deposit][deposit_company_text]" value="{{$condition_company_text}}" readonly>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="table-th">取引先本部企業</div>
                                <div class="table-td table-code-td">
                                    <input type="text" class="search-control deposit_owner_code_input" id="deposit_owner_company_code" name="data[Deposit][deposit_owner_company_code]" value="{{$condition_owner_company_code}}" tabindex="5">
                                    <input type="hidden" id="deposit_owner_company_id" name="data[Deposit][deposit_owner_company_id]" value="{{$condition_owner_company_id}}">
                                </div>
                                <div class="table-td table-name-td">
                                    <input type="text" class="search-control" id="deposit_owner_company_text" name="data[Deposit][deposit_owner_company_text]" value="{{$condition_owner_company_text}}" readonly>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="btn-area ">
                    <div class='search-btn-area'>
                        <input type='submit' class='search-btn btn-primary' name='search-btn' id="search-btn" value='検索' tabindex="6">
                        <input type='submit' class='initial-btn' name='reset-btn' id="reset-btn" value='検索条件リセット' tabindex="7">
                    </div>
                </div>
            </form>
        </div>

        <form id="pdf-output-form" method="post" action='./invoiceOutput' enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class='list-area'>
                <table class='index-table'>
                    <thead>
                        <tr>
                            <th class="width-10">種別</th>
                            <th class="width-10">伝票NO</th>
                            <th>入金日付</th>
                            <th class="width-20">伝票日付</th>
                            <th class="width-15">企業名・店舗名</th>
                            <th>入金金額</th>
                            @if (Home::authClerkCheck())
                            <th>編集</th>
                            @endif
                            <th>印刷</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($depositList as $deposit)
                        <tr>
                            <td class="width-10 {{ $deposit->owner_company_id ? 'type-honbu' : 'type-uriage' }}">
                                {{ $deposit->owner_company_id ? '本部' : '売上先店舗' }}
                            </td>
                            <td class="width-10">{{ $deposit->deposit_id }}</td>
                            <td>{{ $deposit->deposit_date }}</td>
                            <td class="width-20">{{ $deposit->sale_from_date }}~{{ $deposit->sale_to_date }}</td>
                            <td class="width-15">{{ $deposit->owner_company_id ? $deposit->owner_company_name : $deposit->sale_company_name }}</td>
                            <td>{{ number_format($deposit->amount) }}</td>
                            @if (Home::authClerkCheck())
                            <td><a class='edit-btn' href='./DepositEdit/{{ $deposit->deposit_id }}'>編集</a></td>
                            @endif
                            <td><a class='output-btn btn btn-primary' href='./invoiceOutput/{{ $deposit->deposit_id }}' target='_blank'>印刷</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>

        <div class="d-flex justify-content-center">
            {{ $depositList->links() }}
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

            // 検索されて選択状態の企業を取得
            var supply_submit_type_selected = $("#supply_submit_type_selected").val();
            // 検索条件で設定された企業を設定
            $('#supply_submit_type').val(supply_submit_type_selected);

            //-------------------------------------
            // Enterと-を押したときにタブ移動する処理
            //-------------------------------------
            $(document).on("keydown", "input", function(event) {

                if (event.keyCode === 13) { // Enterが押された時

                    var this_id = $(this).attr('id');

                    var tabindex = parseInt($(this).attr('tabindex'), 10);
                    if (isNaN(tabindex) && this_id == "search-btn") {
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
            $(".deposit_company_code_input").autocomplete({
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
            // autocomplete処理 本部企業ID
            //-------------------------------------
            $(".deposit_owner_code_input").autocomplete({
                source: function(req, resp) {
                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./AjaxAutoCompleteOwnerCompany",
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

                if (selector_code.match(/deposit_company/)) { // 仕入先企業

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

                } else if (selector_code.match(/deposit_owner_company/)) { // 本部企業

                    $.ajax({
                        headers: {
                            "X-CSRF-TOKEN": $("[name='_token']").val()
                        },
                        url: "./AjaxSetOwnerCompany",
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

    .table-double-td {
        width: 40%;
        padding: 10px;
        font-size: 10px;
        float: left;
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

    .initial-btn-area {
        text-align: center;
        margin: 20px auto 10px;
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

    .output-btn {
        border-radius: 5px!important;
        color: #fff!important;
        background-color: #e3342fa6!important;
        width: 80%!important;
        margin: auto!important;
        display: block!important;
        text-align: center!important;
        padding: 9px!important;
        font-size: 10px!important;
        border: #e3342fa6!important;
    }

    #pdf-output-form {
        width: 100%;
    }

    .slip-td {
        margin-right: 20%;
    }

    .regis-complete {
        background-color: #D2F0F0;
        font-weight: bold;
        border-left: 3px solid #0099CB!important;
        text-align: center;
    }

    .regis-temp {
        background-color: #f0d2d2;
        font-weight: bold;
        border-left: 3px solid #cb0000!important;
        text-align: center;
    }

    .regis-carry {
        background-color: #d5f0d2;
        font-weight: bold;
        border-left: 3px solid #18cb00!important;
        text-align: center;
    }

    .type-honbu {
        background-color: #d2f0d2;
        font-weight: bold;
        text-align: center;
    }
    .type-uriage {
        background-color: #f0d2d2;
        font-weight: bold;
        text-align: center;
    }
</style>