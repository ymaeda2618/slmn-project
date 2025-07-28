@extends('layouts.app')

@section('content')
<div class="container">
    <h2>銀行口座 編集</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('bank_accounts.update', $account->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- 各入力項目 -->
        <div class="form-group">
            <label>銀行コード</label>
            <input type="text" name="bank_code" class="form-control" value="{{ old('bank_code', $account->bank_code) }}">
        </div>
        <div class="form-group">
            <label>銀行名 *</label>
            <input type="text" name="bank_name" class="form-control" required value="{{ old('bank_name', $account->bank_name) }}">
        </div>
        <div class="form-group">
            <label>支店コード</label>
            <input type="text" name="branch_code" class="form-control" value="{{ old('branch_code', $account->branch_code) }}">
        </div>
        <div class="form-group">
            <label>支店名 *</label>
            <input type="text" name="branch_name" class="form-control" required value="{{ old('branch_name', $account->branch_name) }}">
        </div>
        <div class="form-group">
            <label>口座種別 *</label>
            <select name="account_type" class="form-control" required>
                <option value="">選択してください</option>
                <option value="1" {{ $account->account_type == 1 ? 'selected' : '' }}>普通</option>
                <option value="2" {{ $account->account_type == 2 ? 'selected' : '' }}>当座</option>
            </select>
        </div>
        <div class="form-group">
            <label>口座番号（7桁） *</label>
            <input type="text" name="account_number" class="form-control" maxlength="7" pattern="\d{7}" required value="{{ old('account_number', $account->account_number) }}">
        </div>

        <button type="submit" class="btn btn-primary">更新</button>
    </form>

    <form action="{{ route('bank_accounts.destroy', $account->id) }}" method="POST" onsubmit="return confirm('削除してよろしいですか？');" class="mt-3">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">削除</button>
    </form>
</div>
@endsection
