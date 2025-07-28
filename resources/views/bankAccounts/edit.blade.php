@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>銀行口座 編集</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('bank_accounts.update', $account->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>銀行コード</label>
                <input type="text" name="bank_code" class="form-control" value="{{ old('bank_code', $account->bank_code) }}">
            </div>

            <div class="form-group">
                <label>銀行名 *</label>
                <input type="text" name="bank_name" class="form-control"
                    value="{{ old('bank_name', $account->bank_name) }}" required>
            </div>

            <div class="form-group">
                <label>支店コード</label>
                <input type="text" name="branch_code" class="form-control"
                    value="{{ old('branch_code', $account->branch_code) }}">
            </div>

            <div class="form-group">
                <label>支店名 *</label>
                <input type="text" name="branch_name" class="form-control"
                    value="{{ old('branch_name', $account->branch_name) }}" required>
            </div>

            <div class="form-group">
                <label>口座種別 *</label>
                @php
                    // 直前の old 値があればそれを、なければ $account->account_type を使う
                    $selected = old('account_type', $account->account_type);
                @endphp
                <select name="account_type" class="form-control" required>
                    <option value="">選択してください</option>
                    <option value="1" {{ (string) $selected === '1' ? 'selected' : '' }}>普通</option>
                    <option value="2" {{ (string) $selected === '2' ? 'selected' : '' }}>当座</option>
                </select>
            </div>



            <div class="form-group">
                <label>口座番号（7桁） *</label>
                <input type="text" name="account_number" class="form-control" maxlength="7" pattern="\d{7}"
                    value="{{ old('account_number', $account->account_number) }}" required>
            </div>

            <button type="submit" class="btn btn-success">更新</button>
        </form>

        <form action="{{ route('bank_accounts.destroy', $account->id) }}" method="POST" class="mt-3"
            onsubmit="return confirm('本当に削除しますか？');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">削除</button>
        </form>
    </div>
@endsection
