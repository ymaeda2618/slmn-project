<?php 
    $today = date('Y-m-d');
    $today = '2020-11-01';
?>
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">

        {{--  当日営業情報  --}}
        <div class="today-info">
            <div class="today-info-header">本日の営業情報</div>

            <div class="today-info-body">
                <table class="today-info-table">
                    <tr>
                        <th>仕入額</th>
                        <th>売上額</th>
                    </tr>
                    <tr>
                        <td>{{isset($supplySlipInfoList[$today]) ? number_format($supplySlipInfoList[$today]) : 0}}円</td>
                        <td>{{isset($saleSlipInfoList[$today]) ? number_format($saleSlipInfoList[$today]) : 0}}円</td>
                    </tr>

                    <tr>
                        <th>入金額</th>
                        <th>出金額</th>
                    </tr>
                    <tr>
                        <td>{{isset($depositsInfoList[$today]) ? number_format($depositsInfoList[$today]) : 0}}円</td>
                        <td>{{isset($withdrawalsInfoList[$today]) ? number_format($withdrawalsInfoList[$today]) : 0}}円</td>
                    </tr>
                </table>
            </div>
        </div>  {{--  today-info END  --}}

        {{--  当月営業情報  --}}
        <div class="this-month-info">
            <div class="this-month-info-header">当月の営業情報</div>

            <div class="this-month-info-body">
                <table class="this-month-info-table">
                    <tr>
                        <th>仕入額</th>
                        <th>売上額</th>
                    </tr>
                    <tr>
                        <td>{{isset($supplySlipInfoList['month_total']) ? number_format($supplySlipInfoList['month_total']) : 0}}円</td>
                        <td>{{isset($saleSlipInfoList['month_total']) ? number_format($saleSlipInfoList['month_total']) : 0}}円</td>
                    </tr>

                    <tr>
                        <th>入金額</th>
                        <th>出金額</th>
                    </tr>
                    <tr>
                        <td>{{isset($depositsInfoList['month_total']) ? number_format($depositsInfoList['month_total']) : 0}}円</td>
                        <td>{{isset($withdrawalsInfoList['month_total']) ? number_format($withdrawalsInfoList['month_total']) : 0}}円</td>
                    </tr>
                </table>
            </div>
        </div> {{--  this-month-info END  --}}

        {{--  本日のお知らせ  --}}
        <div class="today-announcement">
            <div class="today-announcement-header">本日のお知らせ</div>

            <div class="today-announcement-body">
                <ul class="today-announcement-list">
                    @if (!empty($todayAnnouncement))
                        <li><a href="{{ asset('/') }}SaleSlipIndex?sale_submit_type=3">仕入伝票が未設定の売上が{{$todayAnnouncement['notSetSlipCnt']}}件あります。</a></li>
                    @else
                        <li>本日のお知らせ情報はありません。</li>
                    @endif
                <ul class="today-announcement-list">
            </div>

        </div> {{--  today-announcement END  --}}

    </div>
</div>
@endsection
<style>
    .today-info, .this-month-info, .today-announcement {
        font-size: 1.4em;
        font-weight: bold;
        width: 100%;
        padding: 25px 0px;
    }
    .today-info-header, .this-month-info-header, .today-announcement-header {
        padding: .5em .75em;
        border: 1px solid #ccc;
        border-radius: 4px 4px, 0, 0;
        background-color: #f6f6f6;
    }
    .today-info-body, .this-month-info-body, .today-announcement-body {
        padding: .5em .75em;
        border: 1px solid #ccc;
    }
    table {
        border-top: 1px solid #ccc;
        width: 100%;
        border-collapse: collapse;
        font-size:14px;
    }
    table tr {
        border-bottom: 1px solid #ccc;
    }

    table td {
        border: none;
        text-align: center;
        vertical-align: middle;
        padding: 16px 6%;
    }

    table th {
        text-align: center;
        padding: 16px;
        width: 22%;
        font-weight: normal;
        background-color:#e9e9e9; 
    }

    .today-announcement-list,
    .today-announcement-list li {
        padding:0px;
        margin:0px;
    }

    .today-announcement-list li{
        list-style-type:none !important;
        list-style-image:none !important;
        margin: 5px 0px 15px 0px !important;
        position:relative;
        padding-left:20px;
        font-size:14px;
        font-weight: normal;
    }

    .today-announcement-list li:before{
        content:''; 
        height:0px; 
        width: 100%;
        display:block; 
        position:absolute; 
        top:30px; 
        left:0px; 
        border-bottom: 1px dashed #aaa;
    }

    .today-announcement-list li:after{
        content:'';
        display:block; 
        position:absolute; 
        background:#aaa;
        width:5px;
        height:5px; 
        top:8px; 
        left:5px; 
        border-radius: 5px;
    }

    .today-announcement-list a {
        color: black !important;
        text-decoration: none !important;
    }

</style>