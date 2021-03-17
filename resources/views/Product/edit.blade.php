@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">製品編集画面</div>

        @if(!empty($error_message))
        <div class="error-alert">{{$error_message}}</div>
        @endif

        <form class="event-form" id="event-create-form" method="post" action="../ProductConfirm" enctype="multipart/form-data" onsubmit="return inputCheck();">
            {{ csrf_field() }}
            <div class="form-group ">
                <label class="column-label " for="code ">コード番号</label>
                <input type="tel " class="form-control " id="code " name="data[Product][code] " value="{{$editProduct->product_code}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="product_type">製品種別</label>
                <select class="file-control" id="product_type" name="data[Product][product_type]">
                    <option value="1">魚</option>
                    <option value="2">その他</option>
                </select>
                <input type='hidden' id='product_type_selected' value='{{$editProduct-> product_type}}'>
            </div>
            <div class="form-group">
                <label class="column-label" for="standard_id">規格</label>
                <table id='standart_list_area'>
                    @foreach ($standardList as $standards)
                    <tr id='standart_list_{{ $standards->status_sort }}' class='standard_list'>
                        <td>
                            <input type="text" class="form-control" id="standard_id" name="data[standard][standard_name][{{ $standards->standard_id }}]" value='{{ $standards->standard_name }}'>
                        </td>
                    </tr>
                    @endforeach
                </table>
                <input type='hidden' name="standard_count" id="standard_count" value="{{ count($standardList) }}">
                <div class="attention-title">規格の数は修正できません。※名称のみ変更可能</div>
            </div>
            <div class="form-group">
                <label class="column-label" for="status_id">製品状態</label>
                <select class="file-control" id="status_id" name="data[Product][status_id]">
                    @foreach ($statusList as $statuses)
                    <option value="{{$statuses->id}}">{{$statuses->name}}</option>
                    @endforeach
                </select>
                <input type='hidden' id='status_id_selected' value='{{$editProduct-> status_id}}'>
            </div>
            <div class="form-group">
                <label class="column-label" for="tax_id">税率</label>
                <select class="file-control" id="tax_id" name="data[Product][tax_id]">
                    @foreach ($taxList as $taxes)
                    <option value="{{$taxes->id}}">{{$taxes->name}}</option>
                    @endforeach
                </select>
                <input type='hidden' id='tax_id_selected' value='{{$editProduct-> tax_id}}'>
            </div>
            <div class="form-group">
                <label class="column-label" for="product_name">品名</label>
                <input type="text" class="form-control" id="product_name" name="data[Product][product_name]" value="{{$editProduct->product_name}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="yomi">ヨミガナ</label>
                <input type="text" class="form-control" id="yomi" name="data[Product][yomi]" value="{{$editProduct->yomi}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="unit_id">単位</label>
                <select class="file-control" id="unit_id" name="data[Product][unit_id]">
                    @foreach ($unitList as $units)
                    <option value="{{$units->id}}">{{$units->name}}</option>
                    @endforeach
                </select>
                <input type='hidden' id='unit_id_selected' value='{{$editProduct->unit_id}}'>
            </div>
            <div class="form-group">
                <label class="column-label" for="inventory_unit_id">仕入単位</label>
                <select class="file-control" id="inventory_unit_id" name="data[Product][inventory_unit_id]">
                    @foreach ($unitList as $units)
                    <option value="{{$units->id}}">{{$units->name}}</option>
                    @endforeach
                </select>
                <input type='hidden' id='inventory_unit_id_selected' value='{{$editProduct->inventory_unit_id}}'>
            </div>
            <br>
            <button id="create-submit-btn" type="submit" class="btn btn-primary">編集確認画面へ</button>
            <input type='hidden' name="data[Product][product_id]" value="{{$editProduct->product_id}}">
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

            // 選択された値を取得
            var inventory_unit_id_selected = $("#inventory_unit_id_selected").val();
            // 検索条件で設定された値を設定
            $('#inventory_unit_id').val(inventory_unit_id_selected);
        });

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
</style>
