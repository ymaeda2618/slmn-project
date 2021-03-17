@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">登録製品一覧</div>

        <!--検索エリア-->
        <div class='search-area'>
            <form id="index-search-form" method="post" action='{{$search_action}}' enctype="multipart/form-data">
                {{ csrf_field() }}
                <table>
                    <tbody>
                        <tr>
                            <td>
                                <div class="table-th">製品種別</div>
                                <div class="table-td">
                                    <select class="form-control" id="product_type" name="data[Product][product_type]">
                                        <option value="0">-</option>
                                        <option value="1">魚</option>
                                        <option value="2">その他</option>
                                    </select>
                                    <input type='hidden' id='product_type_selected' value='{{$product_type}}'>
                                </div>
                                <div class="table-th">製品状態</div>
                                <div class="table-td">
                                    <select class="form-control" id="status_id" name="data[Product][status_id]">
                                        <option value="0">-</option>
                                        @foreach ($statusList as $statuses)
                                        <option value="{{$statuses->id}}">{{$statuses->name}}</option>
                                        @endforeach
                                    </select>
                                    <input type='hidden' id='status_id_selected' value='{{$status_id}}'>
                                </div>
                                <div class="table-th">税率</div>
                                <div class="table-td">
                                    <select class="form-control" id="tax_id" name="data[Product][tax_id]">
                                        <option value="0">-</option>
                                        @foreach ($taxList as $taxes)
                                        <option value="{{$taxes->id}}">{{$taxes->name}}</option>
                                        @endforeach
                                    </select>
                                    <input type='hidden' id='tax_id_selected' value='{{$tax_id}}'>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="table-th">製品名</div>
                                <div class="table-td">
                                    <input type="text" class="form-control" id="product_name" name="data[Product][product_name]" value='{{$product_name}}'>
                                </div>
                                <div class="table-th">単位</div>
                                <div class="table-td">
                                    <select class="form-control" id="unit_id" name="data[Product][unit_id]">
                                    <option value="0">-</option>
                                    @foreach ($unitList as $units)
                                    <option value="{{$units->id}}">{{$units->name}}</option>
                                    @endforeach
                                </select>
                                    <input type='hidden' id='unit_id_selected' value='{{$unit_id}}'>
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
                        <th>製品種別</th>
                        <th>製品状態</th>
                        <th>コード</th>
                        <th class="double-width" colspan="2">品名</th>
                        <th>税率</th>
                        <th>単位</th>
                        @if (Home::authOwnerCheck()) <th>編集</th> @endif
                    </tr>
                    @foreach ($productList as $products)
                    <tr>
                        <td>{{$products->product_type_name}}</td>
                        <td>{{$products->status_name}}</td>
                        <td>{{$products->product_code}}</td>
                        <td class="double-width" colspan="2 ">{{$products->product_name}}</td>
                        <td>{{$products->tax_name}}</td>
                        <td>{{$products->unit_name}}</td>
                        @if (Home::authOwnerCheck()) <td><a class='edit-btn' href='./ProductEdit/{{$products->product_id}}'>編集</a></td> @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center">
            {{ $productList->links() }}
        </div>

    </div>
</div>
@endsection
<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<script type="text/javascript">
    (function($) {
        jQuery(window).load(function() {

            // 検索されて選択状態の役職を取得
            var product_type_selected = $("#product_type_selected").val();
            // 検索条件で設定された役職を設定
            $('#product_type').val(product_type_selected);

            // 検索されて選択状態の役職を取得
            var status_id_selected = $("#status_id_selected").val();
            // 検索条件で設定された役職を設定
            $('#status_id').val(status_id_selected);

            // 検索されて選択状態の役職を取得
            var tax_id_selected = $("#tax_id_selected").val();
            // 検索条件で設定された役職を設定
            $('#tax_id').val(tax_id_selected);

            // 検索されて選択状態の役職を取得
            var unit_id_selected = $("#unit_id_selected").val();
            // 検索条件で設定された役職を設定
            $('#unit_id').val(unit_id_selected);

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
    
    .table-th {
        width: 10%;
        padding: 15px 0px 0px 10px;
        font-size: 10px;
        float: left;
        font-weight: bolder;
    }
    
    .table-td {
        width: 20%;
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