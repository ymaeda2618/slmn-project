<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/home') }}">
                    Mizucho
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </li>
                        @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                        @endif @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    取引登録<span class="caret"></span>
                                </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                @if (Home::authClerkCheck()) <a href="{{ asset('/') }}SupplySlipCreate" class="dropdown-item">仕入登録</a> @endif
                                <a href="{{ asset('/') }}SupplySlipIndex" class="dropdown-item">仕入一覧</a>
                                @if (Home::authClerkCheck()) <a href="{{ asset('/') }}SaleSlipCreate" class="dropdown-item">売上登録</a> @endif
                                <a href="{{ asset('/') }}SaleSlipIndex" class="dropdown-item">売上一覧</a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    入出金管理<span class="caret"></span>
                                </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                @if (Home::authClerkCheck()) <a href="{{ asset('/') }}WithdrawalCreate" class="dropdown-item">支払登録</a> @endif
                                <a href="{{ asset('/') }}WithdrawalIndex" class="dropdown-item">支払一覧</a>
                                @if (Home::authClerkCheck()) <a href="{{ asset('/') }}DepositCreate" class="dropdown-item">入金登録</a> @endif
                                <a href="{{ asset('/') }}DepositIndex" class="dropdown-item">入金一覧</a>
                                <a href="./searchEvent" class="dropdown-item">請求書出力</a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    在庫管理<span class="caret"></span>
                                </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a href="{{ asset('/') }}InventoryAdjustmentIndex" class="dropdown-item">在庫一覧</a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    マスタ登録<span class="caret"></span>
                                </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                @if (Home::authOwnerCheck()) <a href="{{ asset('/') }}StaffCreate" class="dropdown-item">スタッフ登録</a> @endif
                                <a href="{{ asset('/') }}StaffIndex" class="dropdown-item">スタッフ一覧</a>
                                @if (Home::authOwnerCheck()) <a href="{{ asset('/') }}ProductCreate" class="dropdown-item">製品登録</a> @endif
                                <a href="{{ asset('/') }}ProductIndex" class="dropdown-item">製品一覧</a>
                                @if (Home::authOwnerCheck()) <a href="{{ asset('/') }}SupplyCompanyCreate" class="dropdown-item">仕入先企業登録</a> @endif
                                <a href="{{ asset('/') }}SupplyCompanyIndex" class="dropdown-item">仕入先企業一覧</a>
                                @if (Home::authOwnerCheck()) <a href="{{ asset('/') }}SupplyShopCreate" class="dropdown-item">仕入先店舗登録</a> @endif
                                <a href="{{ asset('/') }}SupplyShopIndex" class="dropdown-item">仕入先店舗一覧</a>
                                @if (Home::authOwnerCheck()) <a href="{{ asset('/') }}SaleCompanyCreate" class="dropdown-item">売上先企業登録</a> @endif
                                <a href="{{ asset('/') }}SaleCompanyIndex" class="dropdown-item">売上先企業一覧</a>
                                @if (Home::authOwnerCheck()) <a href="{{ asset('/') }}SaleShopCreate" class="dropdown-item">売上先店舗登録</a> @endif
                                <a href="{{ asset('/') }}SaleShopIndex" class="dropdown-item">売上先店舗一覧</a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        ログアウト
                                    </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
<!-- jQuery読み込み -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<!-- PopperのJS読み込み -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js"></script>
<!-- BootstrapのJS読み込み -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js">
    < /html>
