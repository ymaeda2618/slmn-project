@extends('layouts.app')

@section('content')
<div class="container">
    <h2>銀行口座 一覧</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-3">
        <a href="{{ route('bank_accounts.create') }}" class="btn btn-success">新規登録</a>
    </div>

    @foreach ($bankAccounts as $account)
        <div class="card mb-3 p-3">
            <p><strong>銀行コード：</strong> {{ $account->bank_code }}</p>
            <p><strong>銀行名：</strong> {{ $account->bank_name }}</p>
            <p><strong>支店コード：</strong> {{ $account->branch_code }}</p>
            <p><strong>支店名：</strong> {{ $account->branch_name }}</p>
            <p><strong>口座種別：</strong>
                @if ($account->account_type == 1) 普通
                @elseif ($account->account_type == 2) 当座
                @else 不明
                @endif
            </p>
            <p><strong>口座番号：</strong> {{ $account->account_number }}</p>

            <a href="{{ route('bank_accounts.edit', $account->id) }}" class="btn btn-primary">編集</a>
        </div>
    @endforeach
</div>
@endsection
