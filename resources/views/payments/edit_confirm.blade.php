@extends('layouts.app') @section('content')
<div class="container">
    <h2>編集確認画面</h2>

    <form method="POST" action="{{ route('payments.update', $payment_id) }}">
        {{ csrf_field() }}

        <div class="form-group">
            <label>企業名</label>
            <input type="text" class="form-control" value="{{ $company_name }}" readonly>
        </div>

        <div class="form-group">
            <label>入金日</label>
            <input type="text" class="form-control" value="{{ $payment_date }}" readonly>
            <input type="hidden" name="payment_date" value="{{ $payment_date }}">
        </div>

        <div class="form-group">
            <label>入金額</label>
            <input type="text" class="form-control" value="{{ number_format($amount, 2) }}" readonly>
            <input type="hidden" name="amount" value="{{ $amount }}">
        </div>

        <button type="submit" class="btn btn-success">更新する</button>
        <a href="{{ route('payments.edit', $payment_id) }}" class="btn btn-secondary">戻る</a>
    </form>
</div>
@endsection