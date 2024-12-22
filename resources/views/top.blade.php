<?php
    $today = date('Y-m-d');
?> @extends('layouts.app') @section('content')
    <div class="container">
        <div class="row justify-content-center">
            <!--営業情報を出力-->
            <div class="achievements-title-area">
                <p>入力実績</p>
            </div>
            <div class="achievements-area">
                @foreach ($achievementsArray as $date => $achievements)
                <div class="achievements-detail">
                    <div class="achievements-detail-header">{{$achievements['date']}}のデータ</div>
                    <div class="achievements-detail-body">
                        <div class="achievements">
                            <div class="achievements-detail-list">
                                <p>仕入伝票 {{number_format($achievements['supply']['count'])}}件</p>
                                <p>仕入税抜 {{number_format($achievements['supply']['notax_amount'])}}円</p>
                            </div>
                            <div class="achievements-form-btn">
                                <form method="post" action='{{$supply_index_action}}' enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="data[SupplySlip][date_type]" value="1">
                                    <input type="hidden" name="data[SupplySlip][supply_date_from]" value="{{$date}}">
                                    <input type="hidden" name="data[SupplySlip][supply_date_to]" value="{{$date}}">
                                    <input type='submit' class='supply-index-btn btn-primary' name='search-btn' id="search-btn" value='一覧'>
                                </form>
                            </div>
                        </div>
                        <div class="achievements">
                            <div class="achievements-detail-list">
                                <p>売上伝票 {{number_format($achievements['sale']['count'])}}件</p>
                                <p>売上税抜 {{number_format($achievements['sale']['notax_amount'])}}円</p>
                            </div>
                            <div class="achievements-form-btn">
                                <form method="post" action='{{$sale_index_action}}' enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="data[SaleSlip][date_type]" value="1">
                                    <input type="hidden" name="data[SaleSlip][sale_date_from]" value="{{$date}}">
                                    <input type="hidden" name="data[SaleSlip][sale_date_to]" value="{{$date}}">
                                    <input type='submit' class='sale-index-btn btn-primary' name='search-btn' id="search-btn" value='一覧'>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <!--メニューボタン出力-->
            <div class="menu-title-area">
                <p>メニュー</p>
            </div>
            <div class="menu-btn-area">
                <ul>
                    <li class="btn-title">
                        <p>取引登録</p>
                        <ul>
                            <li>
                                <a href="./SupplySlipIndex" class="edit-btn">仕入一覧</a>
                            </li>
                            <li>
                                <a href="./SupplySlipCreate" class="edit-btn">仕入入力</a>
                            </li>
                            <li>
                                <a href="./SaleSlipIndex" class="edit-btn">売上一覧</a>
                            </li>
                            <li>
                                <a href="./SaleSlipCreate" class="edit-btn">売上入力</a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <ul>
                    <li class="btn-title">
                        <p>請求/支払登録</p>
                        <ul>
                            <li>
                                <a href="./WithdrawalIndex" class="edit-btn">支払一覧</a>
                            </li>
                            <li>
                                <a href="./WithdrawalCreate" class="edit-btn">支払登録</a>
                            </li>
                            <li>
                                <a href="./DepositIndex" class="edit-btn">請求一覧</a>
                            </li>
                            <li>
                                <a href="./DepositCreate" class="edit-btn">請求登録</a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <ul>
                    <li class="btn-title">
                        <p>データ入力/出力</p>
                        <ul>
                            <li>
                                <a href="./DailyPerformanceIndex" class="edit-btn">日別一覧</a>
                            </li>
                            <li>
                                <a href="./PeriodPerformanceIndex" class="edit-btn">期間実績一覧</a>
                            </li>
                            <li>
                                <a href="./csvUpload" class="edit-btn">CSV登録</a>
                            </li>
                            <li>
                                <a href="" onclick="alert('この機能は実装されておりません。'); return false;" class="edit-btn">CSV出力</a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <ul>
                    <li class="btn-title">
                        <p>仕入/売上企業登録</p>
                        <ul>
                            <li>
                                <a href="./SupplyShopIndex" class="edit-btn">仕入先企業一覧</a>
                            </li>
                            <li>
                                <a href="./SupplyShopCreate" class="edit-btn">仕入先企業登録</a>
                            </li>
                            <li>
                                <a href="./SaleCompanyIndex" class="edit-btn">売上先企業一覧</a>
                            </li>
                            <li>
                                <a href="./SaleCompanyCreate" class="edit-btn">売上先企業登録</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>


            <div class="menu-btn-area">
                <ul>
                    <li class="btn-title">
                        <p>製品登録</p>
                        <ul>
                            <li>
                                <a href="./ProductIndex" class="edit-btn">製品一覧</a>
                            </li>
                            <li>
                                <a href="./ProductCreate" class="edit-btn">製品登録</a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <ul>
                    <li class="btn-title">
                        <p>ユーザー/担当者登録</p>
                        <ul>
                            <li>
                                <a href="./UserIndex" class="edit-btn">ユーザー一覧</a>
                            </li>
                            <li>
                                <a href="./UserCreate" class="edit-btn">ユーザー登録</a>
                            </li>
                            <li>
                                <a href="./StaffIndex" class="edit-btn">担当者一覧</a>
                            </li>
                            <li>
                                <a href="./StaffCreate" class="edit-btn">担当者登録</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    @endsection
    <style>
        .achievements-title-area {
            width: 100%;
            font-size: 20px;
            font-weight: bold;
            padding-left: 30px;
        }

        .achievements-title-area p {
            margin-bottom: 0;
        }

        .achievements-area {
            display: flex;
            width: 100%;
            margin: 0px auto 30px;
        }

        .achievements-detail {
            font-weight: bold;
            width: 100%;
            margin: 10px;
        }

        .achievements-detail-header {
            padding: .5em 1.5em;
            border: 2px solid #ccc;
            border-radius: 4px 4px, 0, 0;
            background-color: #f6f6f6;
            border-radius: 10px 10px 0px 0px;
        }

        .achievements-detail:nth-child(1) .achievements-detail-header {
            background-color: #f19a9a;
        }

        .achievements-detail:nth-child(2) .achievements-detail-header {
            background-color: #9aedf1;
        }

        .achievements-detail:nth-child(3) .achievements-detail-header {
            background-color: #bff19a;
        }

        .achievements-detail-body {
            padding: .5em .75em;
            border: 2px solid #ccc;
            border-top: none;
            border-radius: 0px 0px 10px 10px;
            display: flex;
            padding-top: 15px;
            padding-left: 10px;
            font-size: 13px;
        }

        .achievements {
            width: 50%;
        }

        .achievements-detail-list,
        .achievements-detail-list div {
            padding: 0px;
            margin: 0px;
        }

        .achievements-detail-list div {
            list-style-type: none !important;
            list-style-image: none !important;
            margin: 5px 0px 15px 0px !important;
            position: relative;
            padding-left: 20px;
            font-size: 14px;
            font-weight: normal;
        }

        .achievements-detail-list p {
            letter-spacing: 1px;
            margin-left: 10px;
        }

        .achievements-form-btn {
            text-align: left;
            padding-left: 10px;
        }

        .supply-index-btn,
        .sale-index-btn {
            border-radius: 20px;
            width: 100px;
        }
        /* ボタンエリア */

        .menu-title-area {
            width: 100%;
            font-size: 20px;
            font-weight: bold;
            padding-left: 30px;
        }

        .menu-btn-area {
            display: flex;
            width: 100%;
        }

        .menu-btn-area ul {
            width: 100%;
            list-style: none;
            padding-left: 0;
            padding-top: 10px;
        }

        .menu-btn-area li {
            list-style: none;
            padding-left: 0;
        }

        .menu-btn-area .btn-title {
            padding-left: 30px;
        }

        .menu-btn-area .btn-title p {
            font-size: 16px;
            font-weight: bold;
            background-color: #5ab2b6;
            color: white;
            padding-left: 10px;
            padding: 10px 10px 5px 10px;
            border-radius: 5px;
            margin-bottom: 0px;
        }

        .btn-title li {
            padding-left: 30px;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .btn-title li ::before {
            content: "»";
            color: #46b0f0;
            font-weight: bolder;
            padding-right: 5px;
        }

        .menu-btn-category-area {
            width: 100%;
            margin: 10px;
            font-size: 20px;
            display: grid;
        }

        .menu-btn-category-area a {
            width: 70%;
            margin: 10px auto;
            text-align: center;
            background-color: #dbd2d2;
            border-radius: 10px;
            padding: 20px;
        }
    </style>
