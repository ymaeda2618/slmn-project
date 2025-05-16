@extends('layouts.app') @section('content')
<div class="container">
    <h2>入金編集</h2>

    <form method="POST" action="{{ route('payments.edit.confirm', $payment->id) }}">
        {{ csrf_field() }}

        <div class="form-group">
            <label>店舗名</label>
            <input type="text" class="form-control" value="{{ $payment->company_name }}" readonly>
        </div>

        <div class="form-group">
            <label>入金日</label>
            <input type="date" name="payment_date" class="form-control" value="{{ $payment->payment_date }}" required>
        </div>

        <div class="form-group">
            <label>入金額</label>
            <input type="number" name="amount" class="form-control" value="{{ $payment->amount }}" step="0.01" min="0" required>
        </div>

        <button type="submit" class="btn btn-primary">確認画面へ</button>
        <a href="{{ route('payments.list') }}" class="btn btn-secondary">戻る</a>
    </form>
</div>
@endsection