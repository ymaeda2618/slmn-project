@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">売上先店舗一覧</div>

        <!--検索エリア-->
        <div class='search-area'>
            <form id="index-search-form" method="post" action='{{$search_action}}' enctype="multipart/form-data">
                {{ csrf_field() }}
                <table>
                    <tbody>
                        <tr>
                            <td>
                                <div class="table-th-v2">売上先企業</div>
                                <div class="table-td-v2">
                                    <select class="form-control" id="sale_company_id" name="data[SaleShop][sale_company_id]">
                                        <option value="0" selected>-</option>
                                        @foreach ($SaleCompanyList as $SaleCompanies)
                                        <option value="{{$SaleCompanies->id}}">{{$SaleCompanies->name}}</option>
                                        @endforeach
                                    </select>
                                    <input type='hidden' id='sale_company_id_selected' value='{{$sale_company_id}}'>
                                </div>
                                <div class="table-th-v2">売上先店舗名</div>
                                <div class="table-td-v2">
                                    <input type="text" class="form-control" id="sale_shop_name" name="data[SaleShop][sale_shop_name]" value='{{$sale_shop_name}}'>
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
            <table class='index-table '>
                <tbody>
                    <tr>
                        <th>企業名</th>
                        <th>コード</th>
                        <th>店舗名</th>
                        <th>締め日</th>
                        @if (Home::authOwnerCheck()) <th>編集</th> @endif
                    </tr>
                    @foreach ($SaleShopList as $SaleShops)
                    <tr>
                        <td>{{$SaleShops->sale_company_name}}</td>
                        <td>{{$SaleShops->code}}</td>
                        <td>{{$SaleShops->sale_shop_name}}</td>
                        @if($SaleShops->closing_date == 99)
                        <td>月末</td>
                        @elseif($SaleShops->closing_date == 88)
                        <td>都度</td>
                        @else
                        <td>{{$SaleShops->closing_date}}日</td>
                        @endif
                        @if (Home::authOwnerCheck()) <td><a class='edit-btn' href='./SaleShopEdit/{{$SaleShops->sale_shop_id}}'>編集</a></td> @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center">
            {{ $SaleShopList->links() }}
        </div>

    </div>
</div>
@endsection
<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<script type="text/javascript">
    (function($) {
        jQuery(window).load(function() {

            // 検索されて選択状態の企業を取得
            var sale_company_id_selected = $("#sale_company_id_selected").val();
            // 検索条件で設定された企業を設定
            $('#sale_company_id').val(sale_company_id_selected);

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