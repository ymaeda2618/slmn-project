@extends('layouts.app') @section('content')
<div class="container">
    <h2>入金内容確認</h2>

    <form method="POST" action="{{ route('payments.store') }}">
        {{ csrf_field() }}

        <p>入金日：{{ $payment_date }}</p>
        <input type="hidden" name="payment_date" value="{{ $payment_date }}">

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>店舗コード</th>
                    <th>店舗名</th>
                    <th>入金額</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $i => $payment)
                <tr>
                    <td>{{ $payment['company_code'] }}</td>
                    <td>{{ $payment['company_name'] }}</td>
                    <td>{{ $payment['amount'] }}</td>
                </tr>
                @foreach($payment as $key => $val)
                <input type="hidden" name="payments[{{ $i }}][{{ $key }}]" value="{{ $val }}"> @endforeach @endforeach
            </tbody>
        </table>

        <button type="submit" class="btn btn-success">登録</button>
        <a href="{{ route('payments.input') }}" class="btn btn-secondary">戻る</a>
    </form>
</div>
@endsection