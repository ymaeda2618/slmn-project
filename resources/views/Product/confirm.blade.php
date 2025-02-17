@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">製品登録内容 確認画面</div>

        <form class="event-form" id="event-create-form" method="post" action="{{$action_url}}" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group">
                <label class="column-label" for="code">コード番号</label>
                <input type="text" class="form-control" name="data[Product][code]" value="{{$request->data['Product']['code']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="product_type">製品種別</label>
                <input type="text" class="form-control" value="{{$request->product_type_name}}" readonly>
                <input type="hidden" class="form-control" name="data[Product][product_type]" value="{{$request->data['Product']['product_type']}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="status_id">製品状態</label>
                <input type="text" class="form-control" value="{{$request->status_name}}" readonly>
                <input type="hidden" name="data[Product][status_id]" value="{{$request->data['Product']['status_id']}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="tax_id">税率</label>
                <input type="text" class="form-control" value="{{$request->tax_name}}" readonly>
                <input type="hidden" name="data[Product][tax_id]" value="{{$request->data['Product']['tax_id']}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="product_name">品名</label>
                <input type="text" class="form-control" name="data[Product][product_name]" value="{{$request->data['Product']['product_name']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="yomi">ヨミガナ</label>
                <input type="text" class="form-control" name="data[Product][yomi]" value="{{$request->data['Product']['yomi']}}" readonly>
            </div>
            <div class="form-group">
                <label class="column-label" for="unit_id">単位</label>
                <input type="text" class="form-control" value="{{$request->unit_name}}" readonly>
                <input type="hidden" name="data[Product][unit_id]" value="{{$request->data['Product']['unit_id']}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="inventory_unit_id">仕入単位</label>
                <input type="text" class="form-control" value="{{$request->inventory_unit_name}}" readonly>
                <input type="hidden" name="data[Product][inventory_unit_id]" value="{{$request->data['Product']['inventory_unit_id']}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="display_flg">サジェスト表示</label>
                <input type="text" class="form-control" value="{{$request->display_flg_name}}" readonly>
                <input type="hidden" name="data[Product][display_flg]" value="{{$request->data['Product']['display_flg']}}">
            </div>
            <br>
            <button id="create-submit-btn" type="submit" class="btn btn-primary">登録完了画面へ</button>
            <input type='hidden' name="data[Product][product_id]" value="{{isset($request->data['Product']['product_id']) ? $request->data['Product']['product_id'] : 0}}">
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