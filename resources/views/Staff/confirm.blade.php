@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">スタッフ新規作成-確認画面</div>

        <div class="confirm-title">作成したいスタッフは下記でお間違いないですか？</div>

        <form class="event-form" id="event-create-form" method="post" action="{{$action_url}}" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group">
                <label class="column-label" for="code">コード</label>
                <input type="text" class="form-control" name="code" value="{{$request->code}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="staff_name_sei">スタッフ-姓</label>
                <input type="text" class="form-control" name="staff_name_sei" value="{{$request->staff_name_sei}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="staff_name_mei">スタッフ-名</label>
                <input type="text" class="form-control" name="staff_name_mei" value="{{$request->staff_name_mei}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="staff_yomi_sei">スタッフ-ヨミガナ-姓</label>
                <input type="text" class="form-control" id="staff_yomi_sei" name="staff_yomi_sei" value="{{$request->staff_yomi_sei}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="staff_yomi_mei">スタッフ-ヨミガナ-名</label>
                <input type="text" class="form-control" id="staff_yomi_mei" name="staff_yomi_mei" value="{{$request->staff_yomi_mei}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="staff_position_name">スタッフ役職</label>
                <input type="text" class="form-control" name="staff_position_name" value="{{$request->staff_position_name}}" readonly>
                <input type="hidden" name="staff_position" value="{{$request->staff_position}}">
            </div>
            <br>
            <button id="create-submit-btn" type="submit" class="btn btn-primary">登録処理</button>
            <input type='hidden' name="staff_id" value="{{isset($request->staff_id) ? $request->staff_id : 0 }}">
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
    
    .confirm-title {
        font-size: 0.9em;
        font-weight: bold;
        color: red;
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
</style>