<div id='wrapper'>
    <div id='top-contents'>
        <div id='left-contents'>
            <div id='company-info'>
                <p>{{$depositList['company_info']['code']}}</p>
                <p>{{$depositList['company_info']['address']}}</p>
                <p>{{$depositList['company_info']['name']}} 御中</p>
            </div>
            <p>下記のとおり、御請求申し上げます。</p>
            <br>
            <br>
            <table id='claim-table' border='1'>
                <tr>
                    <td>合計金額</td>
                    <td>{{number_format($depositList['total']['total'])}}</td>
                </tr>
                <tr>
                    <td>お支払期限</td>
                    <td>{{$depositList['company_info']['payment_date']}}</td>
                </tr>
            </table>
        </div>
        <div id='right-contents'>
            <h1>御請求書</h1>
            <br>
            <h2>株式会社水長水産</h2>
            <p>〒135-6001　東京都江東区豊洲6-5-1</p>
            <p>　　　　　　6街区口棟103~105</p>
            <p>売り場店舗 TEL：03-6633-5320　FAX：03-6633-4320</p>
            <p>事務所　TEL：047-464-1638　FAX：047-464-1626</p>
            <div id='bank-info'>
                <p>[振込先]　東京東信用金庫 三咲支店</p>
                <p>口座番号/(当座)0701724</p>
            </div>
        </div>
    </div>
    <br>
    <br>
    <div id='details'>
        <table id="detail-table" border='1'>
            <tr>
                <th class="width-40 td-space">商品名</th>
                <th class="width-10 td-space">単価</th>
                <th class="width-10 td-space">数量</th>
                <th class="width-15 td-space">税抜金額</th>
                <th class="width-25 td-space">摘要</th>
            </tr>
            @foreach ($depositList['detail'] as $detailDatas)
            <tr class='page'>
                <td class="td-space">{{$detailDatas['name']}}</td>
                @if ($detailDatas['unit_price'] === '')
                <td class="price-cell td-space brank-line">{{$detailDatas['unit_price']}}</td>
                @else
                <td class="price-cell td-space">{{number_format($detailDatas['unit_price'])}}</td>
                @endif
                <td class="price-cell td-space">{{$detailDatas['unit_num']}}</td>
                @if ($detailDatas['notax_price'] === '')
                <td class="price-cell td-space brank-line">{{$detailDatas['notax_price']}}</td>
                @else
                <td class="price-cell td-space">{{number_format($detailDatas['notax_price'])}}</td>
                @endif
                <td class="td-space">{{$detailDatas['memo']}}</td>
            </tr>
            @endforeach
        </table>
        <br>
        <table id="total-table" border='1' class='page'>
            <tr>
                <td rowspan='7' class="width-70 text-left-top">備考</td>
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
            <tr>
                <td class="td-space">調整額</td>
                <td class="text-right td-space">{{number_format($depositList['detail']['adjust_price']['notax_price'])}}</td>
            </tr>
            <tr>
                <td class="td-space">配送額</td>
                <td class="text-right td-space">{{number_format($depositList['detail']['delivery_price']['notax_price'])}}</td>
            </tr>
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
        font-size: 20px;
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
    
    .width-20 {
        width: 20%;
    }
    
    .width-15 {
        width: 15%;
    }
    
    .width-10 {
        width: 10%;
    }
    
    .page {
        page-break-after: always;
        page-break-inside: avoid;
    }
    
    .page:last-child {
        page-break-after: auto;
    }
</style>