@extends('layouts.app') @section('content')
<div class="container">

    <div class="row justify-content-center">

        <div class="top-title">納品一覧</div>

        <!--日付指定エリア-->
        <div class='search-area'>
            <form id="index-search-form" method="get" action='' enctype="multipart/form-data">
                {{ csrf_field() }}
                <table>
                    <tbody>
                        <tr>
                            <td>
                                <div class="table-th">対象日付</div>
                                <div class="table-td">
                                    <input type="date" class="search-control" id="delivery_date" name="delivery_date" value="{{$delivery_date}}">
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="btn-area ">
                    <div class='search-btn-area'>
                        <input type='submit' class='search-btn btn-primary' id="search-btn" value='検索'>
                    </div>
                </div>
            </form>
        </div>

        <!--一覧表示エリア-->
        <div class='list-area'>
            <table class='index-table'>
                <tbody>
                    <tr>
                        <th colspan="2">企業名</th>
                        <th>商品件数</th>
                    </tr>
                    <tr>
                        <th>製品名</th>
                        <th>数量</th>
                        <th>単位</th>
                    </tr>
                </tbody>
            </table>

            @if (empty($delivery_date))

            <table class='index-table'>
                <div class='no-slip-message'>
                    日付が選択されておりません。<br> 対象日付より選択し、検索を押してください。
                </div>
            </table>

            @elseif (empty($sale_slip_detail_count_arr))

            <table class='index-table'>
                <div class='no-slip-message'>本日の納品商品は未登録です。</div>
            </table>

            @else @foreach ($sale_slip_detail_count_arr as $sale_slip_detail_count)
            <table class='index-table'>
                <tbody>
                    <tr class="company-val-area">
                        <td colspan="2">
                            <!--企業名-->{{$sale_slip_detail_count['company_name']}}
                        </td>
                        <td>
                            <!--商品件数-->{{$sale_slip_detail_count['company_count']}}件
                        </td>
                    </tr>
                    @foreach ($sale_slip_detail_arr[$sale_slip_detail_count['sale_company_id']] as $sale_slip_detail)
                    <tr>
                        <td>
                            <!--製品名-->{{$sale_slip_detail['product_name']}}
                        </td>
                        @if (!empty($sale_slip_detail['inventory_unit_num']))
                        <td>
                            <!--数量-->{{$sale_slip_detail['inventory_unit_num']}}
                        </td>
                        <td>
                            <!--単位-->{{$sale_slip_detail['inventory_unit_name']}}
                        </td>
                        @else
                        <td>
                            <!--数量-->{{$sale_slip_detail['unit_num']}}
                        </td>
                        <td>
                            <!--単位-->{{$sale_slip_detail['unit_name']}}
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endforeach @endif
        </div>

        <div class="d-flex justify-content-center pagenate-btn-area">
            {{ $saleSlipList->links() }}
        </div>
    </div>
</div>
@endsection
<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript">
    (function($) {
        jQuery(window).load(function() {

            // 検索されて選択状態の企業を取得
            var sale_submit_type_selected = $("#sale_submit_type_selected").val();
            // 検索条件で設定された企業を設定
            $('#sale_submit_type').val(sale_submit_type_selected);

        });
    })(jQuery);
</script>

<style>
    /* 共通 */

    .top-title {
        width: 100%;
        font-size: 14px;
        margin-bottom: 30px;
        text-align: center;
    }

    .search-control {
        display: block;
        width: 100%;
        height: 30px;
        padding: 5px;
        font-size: 10px;
        line-height: 1.6;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    }

    .search-control[readonly] {
        background-color: #e9ecef;
        opacity: 1;
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

    .no-slip-message {
        text-align: center;
        margin-top: 30px;
    }

    .table-th {
        width: 100%;
        padding: 15px 0px 0px 10px;
        font-size: 12px;
        text-align: center;
        font-weight: bolder;
    }

    .table-td {
        width: 80%;
        padding: 10px;
        font-size: 10px;
        margin: auto;
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
    /*伝票表示エリア*/

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

    .index-table td:nth-of-type(1) {
        width: 60%;
    }

    .index-table td:nth-of-type(2),
    .index-table td:nth-of-type(3) {
        width: 20%;
    }

    .company-val-area td {
        font-weight: bold;
        font-size: 12px;
        background-color: #fff4cc;
    }

    .pagenate-btn-area {
        width: 100%;
    }
</style>
