@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">スタッフ新規作成画面</div>

        @if(!empty($error_message))
        <div class="error-alert">{{$error_message}}</div>
        @endif

        <form class="event-form" id="event-create-form" method="post" action="./StaffConfirm" enctype="multipart/form-data" onsubmit="return inputCheck();">
            {{ csrf_field() }}
            <div class="form-group">
                <label class="column-label" for="code">コード</label>
                <input type="number" class="form-control" id="code" name="code">
            </div>
            <div class="form-group">
                <label class="column-label" for="staff_name_sei">スタッフ-姓</label>
                <input type="text" class="form-control" id="staff_name_sei" name="staff_name_sei">
            </div>
            <div class="form-group">
                <label class="column-label" for="staff_name_mei">スタッフ-名</label>
                <input type="text" class="form-control" id="staff_name_mei" name="staff_name_mei">
            </div>
            <div class="form-group">
                <label class="column-label" for="staff_yomi_sei">スタッフ-ヨミガナ-姓</label>
                <input type="text" class="form-control" id="staff_yomi_sei" name="staff_yomi_sei">
            </div>
            <div class="form-group">
                <label class="column-label" for="staff_yomi_mei">スタッフ-ヨミガナ-名</label>
                <input type="text" class="form-control" id="staff_yomi_mei" name="staff_yomi_mei">
            </div>
            <div class="form-group">
                <label class="column-label" for="staff_position">スタッフ役職</label>
                <select class="file-control" id="staff_position" name="staff_position">
                @foreach ($staffPositionList as $positions)
                    <option value="{{$positions->id}}">{{$positions->name}}</option>
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
    //------------
    // 入力チェック
    //------------
    function inputCheck() {

        var staff_name_sei = $('#staff_name_sei').val();

        if (staff_name_sei == "") {
            alert('スタッフ-姓が入力されておりません。');
            return false;
        }

        var staff_name_mei = $('#staff_name_mei').val();

        if (staff_name_mei == "") {
            alert('スタッフ-名が入力されておりません。');
            return false;
        }

        var staff_yomi_sei = $('#staff_yomi_sei').val();

        if (staff_yomi_sei == "") {
            alert('スタッフ-ヨミガナ-姓が入力されておりません。');
            return false;
        }
        if (!staff_yomi_sei.match(/^[ァ-ヶー]+$/)) {
            alert('スタッフ-ヨミガナ-姓は全角カタカナで入力してください。');
            return false;
        }

        var staff_yomi_mei = $('#staff_yomi_mei').val();

        if (staff_yomi_mei == "") {
            alert('スタッフ-ヨミガナ-名が入力されておりません。');
            return false;
        }
        if (!staff_yomi_mei.match(/^[ァ-ヶー]+$/)) {
            alert('スタッフ-ヨミガナ-名は全角カタカナで入力してください。');
            return false;
        }

        return true;
    }
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
</style>