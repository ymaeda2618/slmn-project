<div id='wrapper'>
    <div id='top-contents'>
        <div id='left-contents'>
            <div id='company-info'>
                <p>{{$depositList['company_info']['code']}}</p>
                <p>{{$depositList['company_info']['address']}}</p>
                <p>{{$depositList['company_info']['name']}} 御中</p>
            </div>
            <p>納品日 : {{$depositList['company_info']['sale_slip_delivery_date']}}</p>
            <p>下記のとおり、納品申し上げます。</p>
            <br>
            <br>
        </div>
        <div id='right-contents'>
            <h1>納品書</h1>
            <br>
            @if($companyInfo['company_image'])
                <img src={{ asset('../storage/app/images/' . $companyInfo['company_image']) }} class="company-image">
            @endempty
            <h2>{{$companyInfo['name']}}</h2>
            <p>〒{{$companyInfo['postal_code']}}　{{$companyInfo['address']}}</p>
            <p>売り場店舗 TEL：{{$companyInfo['shop_tel']}}　FAX：{{$companyInfo['shop_fax']}}</p>
            <p>事務所　TEL：{{$companyInfo['office_tel']}}　FAX：{{$companyInfo['office_fax']}}</p>
            <p>適格請求書発行事業者番号：T{{$companyInfo['invoice_form_id']}}</p>
        </div>
    </div>
    <br>
    <br>
    <div id='details'>
        <table id="detail-table" border='1'>
            <tr class='header'>
                <th class="width-7 td-space">納品日</th>
                <th class="width-23 td-space">商品名</th>
                <th class="width-12 td-space">産地</th>
                <th class="width-5 td-space">個数</th>
                <th class="width-9 td-space">単価</th>
                <th class="width-5 td-space">数量</th>
                <th class="width-5 td-space">単位</th>
                <th class="width-9 td-space">税抜金額</th>
                <th class="width-25 td-space">摘要</th>
            </tr>
            <?php $prev_date = ""; ?> @foreach ($depositList['detail'] as $detailDatas)
            <tr class='page'>
                <td class="center-cell td-space">
                    @if($prev_date != $detailDatas['date']) {{$detailDatas['date']}} @endif
                </td>
                <td class="td-space">{{$detailDatas['name']}}</td>
                <td class="td-space">{{$detailDatas['origin_name']}}</td>
                <td class="center-cell td-space">{{$detailDatas['inventory_unit_num']}}</td>
                @if ($detailDatas['unit_price'] === '')
                <td class="price-cell td-space brank-line">{{$detailDatas['unit_price']}}</td>
                @else
                <td class="price-cell td-space">{{number_format($detailDatas['unit_price'])}}</td>
                @endif
                <td class="price-cell td-space">{{$detailDatas['unit_num']}}</td>
                <td class="center-cell td-space">{{$detailDatas['unit_name']}}</td>
                @if ($detailDatas['notax_price'] === '')
                <td class="price-cell td-space brank-line">{{$detailDatas['notax_price']}}</td>
                @else
                <td class="price-cell td-space">{{number_format($detailDatas['notax_price'])}}</td>
                @endif
                <td class="td-space">{{$detailDatas['memo']}}</td>
            </tr>
            <?php $prev_date = $detailDatas['date']; ?> @endforeach
        </table>
        <br>
        <table id="total-table" border='1' class='page'>
            <tr>
                <td rowspan='7' class="width-70 text-left-top">備考<br><br>{{$depositList['company_info']['remarks']}}</td>
                <td class="width-15 td-space">8%課税対象額</td>
                <td class="width-15 text-right td-space">{{number_format($depositList['total']['notax_subtotal_8'])}}</td>
            </tr>
            <tr>
                <td class="td-space">10%課税対象額</td>
                <td class="text-right td-space">{{number_format($depositList['total']['notax_subtotal_10'])}}</td>
            </tr>
            <tr>
                <td class="td-space">8%消費税額</td>
                <td class="text-right td-space">{{number_format($depositList['total']['tax_8'])}}</td>
            </tr>
            <tr>
                <td class="td-space">10%消費税額</td>
                <td class="text-right td-space">{{number_format($depositList['total']['tax_10'])}}</td>
            </tr>
            @if (isset($depositList['detail']['adjust_price']['notax_price']))
            <tr>
                <td class="td-space">調整額</td>
                <td class="text-right td-space">{{number_format($depositList['detail']['adjust_price']['notax_price'])}}</td>
            </tr>
            @endif @if (isset($depositList['detail']['delivery_price']['notax_price']))
            <tr>
                <td class="td-space">配送額</td>
                <td class="text-right td-space">{{number_format($depositList['detail']['delivery_price']['notax_price'])}}</td>
            </tr>
            @endif
            <tr>
                <td class="td-space">合計</td>
                <td class="font-bold text-right td-space">{{number_format($depositList['total']['total'])}}</td>
            </tr>
        </table>
    </div>
</div>

<style>
    #wrapper {
        width: 100%;
        position: relative;
    }

    #top-contents {
        width: 100%;
        overflow: hidden;
    }

    #left-contents {
        width: 50%;
        float: left;
        overflow: hidden;
    }

    #right-contents {
        width: 50%;
        overflow: hidden;
    }

    #details {
        width: 100%;
    }

    #company-info {
        width: 80%;
        height: 11.5%;
        border: solid 1px #DDDDDD;
        padding: 4% 0 4% 5%;
        font-size: 16px;
    }

    #claim-table {
        width: 95%;
        border: solid 2px #999999;
        border-collapse: collapse;
        font-size: 22px;
    }

    #claim-table td {
        padding: 3%;
        text-align: center;
    }

    #bank-info {
        width: 95%;
        border: solid 2px #999999;
        text-align: center;
    }

    h1 {
        width: 80%;
        border-bottom: solid 2px #999999;
        letter-spacing: 1em;
        text-align: center;
    }

    #detail-table {
        width: 100%;
        border: solid 2px #999999;
        border-collapse: collapse;
    }

    .center-cell {
        text-align: center;
    }

    .price-cell {
        text-align: center;
    }

    #total-table {
        width: 100%;
        border: solid 2px #999999;
        border-collapse: collapse;
    }

    .font-bold {
        font-weight: bold;
    }

    .text-right {
        text-align: right;
    }

    .text-left-top {
        text-align: left;
        vertical-align: top;
    }

    .td-space {
        padding: 1%;
    }

    .brank-line {
        padding: 2%;
    }

    .width-70 {
        width: 70%;
    }

    .width-50 {
        width: 50%;
    }

    .width-40 {
        width: 40%;
    }

    .width-30 {
        width: 30%;
    }

    .width-25 {
        width: 25%;
    }

    .width-23 {
        width: 23%;
    }

    .width-20 {
        width: 20%;
    }

    .width-15 {
        width: 15%;
    }

    .width-12 {
        width: 12%;
    }

    .width-10 {
        width: 10%;
    }

    .width-9 {
        width: 9%;
    }

    .width-5 {
        width: 5%;
    }

    .width-7 {
        width: 7%;
    }

    .header {
        font-size: 16px;
    }

    .page {
        page-break-after: always;
        page-break-inside: avoid;
        font-size: 12px;
    }

    .page:last-child {
        page-break-after: auto;
    }

    .company-image {
        position: absolute;
        top: 60px;
        right: 100px;
        width: 160px;
        z-index: -1;
    }
</style>
