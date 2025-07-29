@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>銀行口座 一覧</h2>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <a href="{{ route('bank_accounts.create') }}" class="btn btn-success mb-3">新規登録</a>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>銀行コード</th>
                    <th>銀行名</th>
                    <th>支店コード</th>
                    <th>支店名</th>
                    <th>口座種別</th>
                    <th>口座番号</th>
                    <th>編集</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bankAccounts as $account)
                    <tr>
                        <td>{{ $account->bank_code }}</td>
                        <td>{{ $account->bank_name }}</td>
                        <td>{{ $account->branch_code }}</td>
                        <td>{{ $account->branch_name }}</td>
                        <td>
                            @if ($account->account_type == 1)
                                普通
                            @elseif ($account->account_type == 2)
                                当座
                            @else
                                不明
                            @endif
                        </td>
                        <td>{{ $account->account_number }}</td>
                        <td>
                            <a href="{{ route('bank_accounts.edit', $account->id) }}" class="btn btn-sm btn-primary">編集</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
