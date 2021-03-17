@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">仕入先企業一覧</div>

        <!--検索エリア-->
        <div class='search-area'>
            <form id="index-search-form" method="post" action='{{$search_action}}' enctype="multipart/form-data">
                {{ csrf_field() }}
                <table>
                    <tbody>
                        <tr>
                            <td>
                                <div class="table-th-v2">仕入先企業名</div>
                                <div class="table-td-v2">
                                    <input type="text" class="form-control" id="supply_company_name" name="data[SupplyCompany][supply_company_name]" value='{{$supply_company_name}}'>
                                </div>
                                <div class="table-th-v2">締め日</div>
                                <div class="table-td-v2">
                                    <select class="form-control" id="closing_date" name="data[SupplyCompany][closing_date]">
                                        <option value="0" selected>-</option>
                                        <option value="99">月末</option>
                                        <option value="88">都度</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                        <option value="8">8</option>
                                        <option value="9">9</option>
                                        <option value="10">10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                        <option value="13">13</option>
                                        <option value="14">14</option>
                                        <option value="15">15</option>
                                        <option value="16">16</option>
                                        <option value="17">17</option>
                                        <option value="18">18</option>
                                        <option value="19">19</option>
                                        <option value="20">20</option>
                                        <option value="21">21</option>
                                        <option value="22">22</option>
                                        <option value="23">23</option>
                                        <option value="24">24</option>
                                        <option value="25">25</option>
                                        <option value="26">26</option>
                                        <option value="27">27</option>
                                        <option value="28">28</option>
                                    </select>
                                    <input type='hidden' id='closing_date_selected' value='{{$closing_date}}'>
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
                        <th>企業名</th>
                        <th>締め日</th>
                        @if (Home::authOwnerCheck()) <th>編集</th> @endif
                    </tr>
                    @foreach ($supplyCompanyList as $supplyCompanies)
                    <tr>
                        <td>{{$supplyCompanies->code}}</td>
                        <td>{{$supplyCompanies->supply_company_name}}</td>
                        @if($supplyCompanies->closing_date == 99)
                        <td>月末</td>
                        @elseif($supplyCompanies->closing_date == 88)
                        <td>都度</td>
                        @else
                        <td>{{$supplyCompanies->closing_date}}日</td>
                        @endif
                        @if (Home::authOwnerCheck()) <td><a class='edit-btn' href='./SupplyCompanyEdit/{{$supplyCompanies->supply_company_id}}'>編集</a></td> @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center">
            {{ $supplyCompanyList->links() }}
        </div>

    </div>
</div>
@endsection
<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<script type="text/javascript">
    (function($) {
        jQuery(window).load(function() {

            // 検索されて選択状態の役職を取得
            var closing_date_selected = $("#closing_date_selected").val();
            // 検索条件で設定された役職を設定
            $('#closing_date').val(closing_date_selected);

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
    
    .table-th-v2 {
        width: 15%;
        padding: 20px 0px 0px 40px;
        font-size: 10px;
        float: left;
        font-weight: bolder;
    }
    
    .table-td-v2 {
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