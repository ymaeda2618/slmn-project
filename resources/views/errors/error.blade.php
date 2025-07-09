@extends('layouts.app')

@section('content')
<div class="container">
    <div class="alert alert-danger">
        <h4>エラーが発生しました</h4>
        <p>本画面のスクリーンショットを開発担当者に送ってください。</p>
        <p>{{ $error_message }}</p>
    </div>
</div>
@endsection