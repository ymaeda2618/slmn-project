@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">スタッフ新規作成-完了画面</div>

        <div class="confirm-title">スタッフ作成処理が完了しました。</div>

        <div class='action-index-area'>
            <a href="./StaffIndex" class='action-index-btn'>スタッフ一覧に戻る</a>
        </div>

    </div>
</div>
@endsection


<style>
    /* 共通 */

    .top-title {
        font-size: 1.4em;
        font-weight: bold;
        width: 100%;
        text-align: center;
        padding: 25px 0px;
    }

    .confirm-title {
        font-size: 0.9em;
        font-weight: bold;
        color: red;
        width: 100%;
        text-align: center;
        padding: 25px 0px;
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

    .action-index-area {
        text-align: center;
        margin: 35px auto 10px;
    }

    .action-index-btn {
        width: 60%;
        max-width: 150px;
        height: 35px;
        border-radius: 5px;
    }
</style>
