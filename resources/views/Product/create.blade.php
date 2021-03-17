@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">製品新規作成画面</div>

        @if(!empty($error_message))
        <div class="error-alert">{{$error_message}}</div>
        @endif

        <form class="event-form" id="product-create-form" method="post" action="./ProductConfirm" enctype="multipart/form-data" onsubmit="return inputCheck();">
            {{ csrf_field() }}
            <div class="form-group">
                <label class="column-label" for="code">コード番号</label>
                <input type="tel" class="form-control" id="code" name="data[Product][code]">
            </div>
            <div class="form-group">
                <label class="column-label" for="product_type">製品種別</label>
                <select class="file-control" id="product_type" name="data[Product][product_type]">
                    <option value="1">魚</option>
                    <option value="2">その他</option>
                </select>
            </div>
            <div class="form-group">
                <label class="column-label" for="standard_id">規格</label>
                <table id='standart_list_area'>
                    <tr id='standart_list_0' class='standard_list'>
                        <td>
                            <input type="text" class="form-control" id="standard_id" name="data[standard][standard_name][0]" value='規格0'>
                        </td>
                        <td>-</td>
                    </tr>
                </table>
                <input type='hidden' name="standard_count" id="standard_count" value="1">
                <button id="standard_add_btn" type="button" class="btn standard_del_btn" onclick='javascript:addStandardList()'>規格追加</button>
            </div>
            <div class="form-group">
                <label class="column-label" for="status_id">製品状態</label>
                <select class="file-control" id="status_id" name="data[Product][status_id]">
                    @foreach ($statusList as $statuses)
                    <option value="{{$statuses->id}}">{{$statuses->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="column-label" for="tax_id">税率</label>
                <select class="file-control" id="tax_id" name="data[Product][tax_id]">
                    @foreach ($taxList as $taxes)
                    <option value="{{$taxes->id}}">{{$taxes->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="column-label" for="product_name">品名</label>
                <input type="text" class="form-control" id="product_name" name="data[Product][product_name]">
            </div>
            <div class="form-group">
                <label class="column-label" for="yomi">ヨミガナ</label>
                <input type="text" class="form-control" id="yomi" name="data[Product][yomi]">
            </div>
            <div class="form-group">
                <label class="column-label" for="unit_id">単位</label>
                <select class="file-control" id="unit_id" name="data[Product][unit_id]">
                    @foreach ($unitList as $units)
                    <option value="{{$units->id}}">{{$units->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="column-label" for="inventory_unit_id">仕入単位</label>
                <select class="file-control" id="inventory_unit_id" name="data[Product][inventory_unit_id]">
                    @foreach ($unitList as $units)
                    <option value="{{$units->id}}">{{$units->name}}</option>
                    @endforeach
                </select>
            </div>
            <br>
            <button id="create-submit-btn" type="submit" class="btn btn-primary">確認登録画面へ</button>
            <input type='hidden' name="submit_type" value="1">
        </form>

    </div>
</div>
@endsection

<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<script type="text/javascript">
    (function($) {
        jQuery(window).load(function() {

            // 検索されて選択状態の役職を取得
            var staff_position_selected = $("#staff_position_selected").val();

            // 検索条件で設定された役職を設定
            $(' #staff_position ').val(staff_position_selected);
        });

    })(jQuery);

    function addStandardList() {

        // 現在のページ番号を取得
        var standard_count = $('#standard_count').val();

        var fd = new FormData();
        fd.append('standard_count', standard_count); // 押されたボタンID

        $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('[name="_token"]').val()
                },
                url: "./ProductAjaxAddStandard",
                type: 'POST',
                dataType: "JSON",
                data: fd,
                processData: false,
                contentType: false
            })
            .done(function(data) {

                // 規格の数を取得
                $('#standard_count').val(data[0]);
                // 規格リスト追加
                $("#standart_list_area").append(data[1]);

            })
            .fail(function(XMLHttpRequest, textStatus, errorThrown) {
                alert(XMLHttpRequest);
                alert(textStatus);
                alert(errorThrown);
                // 送信失敗
                alert("失敗しました。");
            });
    }

    function removeStandardList(remove_num) {

        // 現在のページ番号を取得
        var standard_count = $('#standard_count').val();
        $('#standard_count').val(standard_count - 1);

        // 削除
        $('#standart_list_' + remove_num).remove();

    }

    //------------
    // 入力チェック
    //------------
    function inputCheck() {

        var product_name = $('#product_name').val();

        if (product_name == "") {
            alert('品名が入力されておりません。');
            return false;
        }

        var yomi = $('#yomi').val();

        if (yomi == "") {
            alert('ヨミガナが入力されておりません。');
            return false;
        }
        if (!yomi.match(/^[ァ-ヶー]+$/)) {
            alert('ヨミガナは全角カタカナで入力してください。');
            return false;
        }

        return true;
    }
</script>
<style>
    /* 共通 */

    .container {
        max-width: 800px!important;
    }

    .form-control {
        font-size: 10px!important;
    }

    .btn {
        font-size: 10px!important;
    }

    .top-title {
        font-size: 14px;
        font-weight: bold;
        width: 100%;
        text-align: center;
        padding: 15px 0px;
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

    .file-control {
        font-size: 10px;
        display: block;
        width: 100%;
        height: calc(2.19rem + 2px);
        padding: .375rem .75rem;
        line-height: 1.6;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        transition: border-color .15s;
    }

    .column-label {
        font-size: 10px;
        font-weight: bold;
    }

    #standard_add_btn {
        margin: 10px auto 0px;
        font-size: 10px;
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
