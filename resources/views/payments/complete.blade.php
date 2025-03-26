@extends('layouts.app') @section('content')
<div class="container">
    <h2>入金完了</h2>
    <p>入金情報を登録しました。</p>
    <a href="{{ route('payments.input') }}" class="btn btn-primary">もう一度入力する</a>
</div>
@endsection
