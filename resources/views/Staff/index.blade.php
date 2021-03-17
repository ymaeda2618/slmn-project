@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">登録スタッフ一覧</div>

        <!--検索エリア-->
        <div class='search-area'>
            <form id="index-search-form" method="post" action='{{$search_action}}' enctype="multipart/form-data">
                {{ csrf_field() }}
                <table>
                    <tbody>
                        <tr>
                            <td>
                                <div class="staff-table-th">スタッフ名</div>
                                <div class="staff-table-td"><input type="text" class="form-control" id="staff_name" name="data[Staff][staff_name]" value='{{$condition_name}}'></div>
                                <div class="staff-table-th">役職</div>
                                <div class="staff-table-td">
                                    <select class="form-control" id="staff_position" name="data[Staff][staff_position]">
                                        <option value="0">-</option>
                                        @foreach ($positionList as $positions)
                                        <option value="{{$positions->id}}">{{$positions->name}}</option>
                                        @endforeach
                                    </select>
                                    <input type='hidden' id='staff_position_selected' value='{{$condition_position}}'>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class='search-btn-area'>
                    <input type='submit' class='search-btn btn-primary' name='search-btn' id="1" value='検索'>
                    <input type='submit' class='initial-btn' name='reset-btn' id="2" value='検索条件リセット'>
                </div>
            </form>
        </div>

        <!--一覧表示エリア-->
        <div class='list-area'>
            <table class='index-table'>
                <tbody>
                    <tr>
                        <th>コード</th>
                        <th>スタッフ名</th>
                        <th>役職</th>
                        @if (Home::authOwnerCheck()) <th>編集</th> @endif
                    </tr>
                    @foreach ($staffList as $staffs)
                    <tr>
                        <td>{{$staffs->code}}</td>
                        <td>{{$staffs->staff_name}}</td>
                        <td>{{$staffs->staff_position_name}}</td>
                        @if (Home::authOwnerCheck()) <td><a class='edit-btn' href='./StaffEdit/{{$staffs->staff_id}}'>編集</a></td> @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center">
            {{ $staffList->links() }}
        </div>

    </div>
</div>
@endsection
<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<script type="text/javascript">
    (function($) {
        jQuery(window).load(function() {

            // 検索されて選択状態の役職を取得
            var staff_position_selected = $("#staff_position_selected").val();

            // 検索条件で設定された役職を設定
            $('#staff_position').val(staff_position_selected);

        });
    })(jQuery);
</script>

<style>
    /* 共通 */
    
    .top-title {
        max-width: 1300px;
        font-size: 1.4em;
        font-weight: bold;
        width: 90%;
        padding: 25px 0px 25px 20px;
    }
    
    .search-area {
        max-width: 1300px;
        width: 90%;
        padding: 10px 0px 0px;
        border: 1px solid #bcbcbc;
        border-radius: 5px;
    }
    
    .search-area table {
        margin: auto;
        width: 100%;
    }
    
    .staff-table-th {
        width: 15%;
        padding: 20px 0px 0px 40px;
        font-size: 10px;
        float: left;
        font-weight: bolder;
    }
    
    .staff-table-td {
        width: 30%;
        padding: 10px;
        font-size: 10px;
        float: left;
    }
    
    .search-btn-area {
        text-align: center;
        margin: 10px auto 10px;
        width: 100%;
        display: inline-block;
    }
    
    .search-btn {
        width: 80%;
        font-size: 10px;
        max-width: 150px;
        height: 30px;
        border-radius: 10px;
        margin-right: 2%;
    }
    
    .initial-btn {
        width: 80%;
        font-size: 10px;
        max-width: 150px;
        height: 30px;
        border-radius: 10px;
        margin-left: 2%;
    }
    
    .list-area {
        max-width: 1300px;
        width: 90%;
        margin: 25px auto 50px;
    }
    
    .index-table {
        width: 100%;
        letter-spacing: 2px;
        border-top: solid 1px #ccc;
        border-bottom: solid 2px #ccc;
        margin: 5px 0px;
    }
    
    .index-table th {
        width: 10%;
        padding: 10px;
        padding-left: 10px;
        background-color: #57595b;
        font-weight: bold;
        color: white;
        font-size: 10px;
        letter-spacing: 1px;
        border: 1px solid #bcbcbc;
    }
    
    .index-table td {
        font-size: 10px;
        padding-left: 20px;
        padding: 8px;
        border: 1px solid #bcbcbc;
        width: 10%;
    }
    
    .double-width {
        width: 20%!important;
    }
    
    .edit-btn {
        border-radius: 5px;
        color: #fff;
        background-color: #72be92;
        width: 80%;
        margin: auto;
        display: block;
        text-align: center;
        padding: 10px;
    }
</style>