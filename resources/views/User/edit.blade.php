@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">ユーザー編集画面</div>

        <form class="event-form" id="event-create-form" method="post" action="./../UserEditRegister" enctype="multipart/form-data" onsubmit="return inputCheck();">
            {{ csrf_field() }}
            <div class="form-group">
                <label class="column-label" for="name">ユーザー名</label>
                <input type="text" class="form-control" id="name" name="data[User][name]" value="{{$editUser->user_name}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="email">メールアドレス</label>
                <input type="email" class="form-control" id="email" name="data[User][email]" value="{{$editUser->user_email}}">
            </div>
            <div class="form-group">
                <label class="column-label" for="password">パスワード</label>
                <input type="password" class="form-control" id="password" name="data[User][password]">
            </div>
            <div class="form-group">
                <label class="column-label" for="password-confirm">確認用パスワード</label>
                <input type="password" class="form-control" id="password-confirm" name="data[User][passwordConfirm]">
            </div>
            <div class="form-group">
                <label class="column-label" for="authority">権限</label>
                <select class="file-control" id="authority" name="data[User][authority]">
                @foreach ($userAuthoritys as $authorityId => $authorityName)
                    @if ($editUser->user_authority == $authorityId)
                    <option value="{{$authorityId}}" selected>{{$authorityName}}</option>
                    @else
                    <option value="{{$authorityId}}">{{$authorityName}}</option>
                    @endif
                @endforeach
                </select>
            </div>
            <button id="create-submit-btn" type="submit" class="btn btn-primary">編集</button>
            <input type="hidden" id="id" name="data[User][id]" value="{{$editUser->user_id}}">
        </form>

    </div>
</div>
@endsection

<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<script type="text/javascript">
    //------------
    // 入力チェック
    //------------
    function inputCheck() {

        var name = $('#name').val();

        if (name == "") {
            alert('ユーザー名が入力されておりません。');
            return false;
        }

        var email = $('#email').val();

        if (email == "") {
            alert('メールアドレスが入力されておりません。');
            return false;
        }

        var password = $('#password').val();

        if (password == "") {
            alert('パスワードが入力されておりません。');
            return false;
        }

        var password_confirm = $('#password-confirm').val();

        if (password_confirm == "") {
            alert('確認用パスワードが入力されておりません。');
            return false;
        }

        if (password !== password_confirm) {
            alert('パスワードと確認用パスワードが一致しません。');
            return false;
        }

        return true;
    }
</script>
<style>
    /* 共通 */

    .top-title {
        font-size: 1.4em;
        font-weight: bold;
        width: 100%;
        text-align: center;
        padding: 25px 0px;
    }

    .error-alert {
        color: red;
        font-weight: bold;
    }

    .event-form {
        max-width: 1300px;
        width: 90%;
        margin: auto;
    }

    .form-group {
        margin-bottom: 3rem !important;
    }

    .file-control {
        width: 100%;
        height: calc(1.6em + 0.75rem + 2px);
        padding: 0.375rem 0.75rem;
    }

    .column-label {
        font-size: 0.9em;
        font-weight: bold;
    }
</style>