@extends('layouts.app')

@section('content')
<div class="container">
    <h2>銀行口座 登録</h2>

    {{-- 登録上限エラーの個別表示 --}}
    @if ($errors->has('max_limit'))
        <div class="alert alert-danger">
            <ul>
                <li>{{ $errors->first('max_limit') }}</li>
            </ul>
        </div>
    @endif

    {{-- 通常のバリデーションエラー --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    {{-- max_limitは重複させない --}}
                    @if ($error !== $errors->first('max_limit'))
                        <li>{{ $error }}</li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('bank_accounts.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>銀行コード</label>
            <input type="text" name="bank_code" class="form-control">
        </div>
        <div class="form-group">
            <label>銀行名 *</label>
            <input type="text" name="bank_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>支店コード</label>
            <input type="text" name="branch_code" class="form-control">
        </div>
        <div class="form-group">
            <label>支店名 *</label>
            <input type="text" name="branch_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>口座種別 *</label>
            <select name="account_type" class="form-control" required>
                <option value="">選択してください</option>
                <option value="1">普通</option>
                <option value="2">当座</option>
            </select>
        </div>
        <div class="form-group">
            <label>口座番号（7桁） *</label>
            <input type="text" name="account_number" class="form-control" maxlength="7" pattern="\d{7}" required>
        </div>
        <button type="submit" class="btn btn-primary">登録</button>
    </form>
</div>
@endsection
