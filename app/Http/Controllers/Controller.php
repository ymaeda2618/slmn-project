<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $login_user_id;

    public function __construct()
    {
        // ログイン済みならユーザーIDを取得
        $this->middleware(function ($request, $next) {
            if (\Auth::check()) {
                $this->login_user_id = \Auth::id(); // 数値形式で取得
            }
            return $next($request);
        });
    }

}
