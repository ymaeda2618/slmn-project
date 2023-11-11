<div id='wrapper'>
    <div id='top-contents'>
        <div id='left-contents'>
            <div id='company-info'>
                <p>{{$depositList['company_info']['code']}}</p>
                <p>{{$depositList['company_info']['address']}}</p>
                <p>{{$depositList['company_info']['name']}} 御中</p>
            </div>
            <p>請求期間<br> {{$depositList['company_info']['sale_from_to_date']}}</p>
            <p>下記のとおり、御請求申し上げます。</p>
            <table id='claim-table' border='1'>
                <tr>
                    <td class="billing-amount-area">御請求額</td>
                    <td>8%課税対象額</td>
                    <td>{{number_format($depositList['total']['notax_subtotal_8'])}}</td>
                </tr>
                <tr>
                    <td class="billing-amount-area" rowspan='3'>{{number_format($depositList['total']['total'])}}</td>
                    <td>10%課税対象額</td>
                    <td>{{number_format($depositList['total']['notax_subtotal_10'])}}</td>
                </tr>
                <tr>
                    <td>8%消費税</td>
                    <td>{{number_format($depositList['total']['tax_8'])}}</td>
                </tr>
                <tr>
                    <td>10%消費税</td>
                    <td>{{number_format($depositList['total']['tax_10'])}}</td>
                </tr>
            </table>
        </div>
        <div id='right-contents'>
            <h1>御請求書</h1>
            <br>
            <h2>株式会社水長水産</h2>
            <p>〒135-0061　東京都江東区豊洲6-5-1</p>
            <p>売り場店舗 TEL：03-6633-5320　FAX：03-6633-4320</p>
            <p>事務所　TEL：047-464-1638　FAX：047-464-1626</p>
            <p>適格請求書発行事業者番号：T1010001147111</p>
            <br>
            <div id='bank-info'>

                <p>お支払期限：{{$depositList['company_info']['payment_date']}}</p>
                <p>[振込先]　東京東信用金庫 三咲支店</p>
                <p>口座番号/(当座)0701724</p>
            </div>
        </div>
    </div>
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
        <div class='attention-area'>
            <p>*は軽減税率対象商品</p>
        </div>
        <p>【備考】</p>
        <table id="remark-table" class='page'>
            <tr>
                <td>
                    {{$depositList['company_info']['remarks']}}
                </td>
            </tr>
        </table>
    </div>
</div>

<style>
    #wrapper {
        width: 100%;
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
        font-size: 12px;
        margin-top: 25px;
    }
    
    #claim-table td {
        padding-top: 10px;
        padding-bottom: 10px;
        text-align: center;
        border: none;
        width: 20%;
        border: solid 1px #999999;
    }
    
    #claim-table .billing-amount-area {
        width: 40%;
        font-size: 20px;
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
    
    #remark-table {
        width: 100%;
        border: solid 2px #999999;
        border-collapse: collapse;
        height: 150px;
    }
    
    #remark-table td {
        vertical-align: top;
        padding: 10px;
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
</style>