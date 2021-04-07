<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use Carbon\Carbon;
use Exception;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * ユーザ一覧
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        // リクエストパスを取得
        $request_path = $request->path();
        $path_array   = explode('/', $request_path);

        // ページングの番号の有無でindexのaction先を変更
        if(count($path_array) > 1){
            $search_action = '../UserIndex';
        } else {
            $search_action = './UserIndex';
        }

        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST"
        ) { // ページング処理

            $condition_name      = $request->session()->get('condition_name');
            $condition_email     = $request->session()->get('condition_email');
            $condition_authority = $request->session()->get('condition_authority');

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理
                $condition_name      = $request->data['User']['user_name'];
                $condition_email     = $request->data['User']['user_email'];
                $condition_authority = $request->data['User']['user_authority'];
                if ($condition_authority == 99) $condition_authority = null;

                $request->session()->put('condition_name', $condition_name);
                $request->session()->put('condition_position', $condition_email);
                $request->session()->put('condition_authority', $condition_authority);
            } else { // リセットボタンが押された時の処理

                $condition_name      = null;
                $condition_email     = null;
                $condition_authority = null;
                $request->session()->forget('condition_name');
                $request->session()->forget('condition_email');
                $request->session()->forget('condition_authority');
            }
        }

        try {

            // スタッフ一覧を取得
            $userList = DB::table('users AS User')
            ->select(
                'User.id    AS user_id',
                'User.name  AS user_name',
                'User.email AS user_email',
                'User.role  AS user_role'
            )
            ->if(!empty($condition_name), function ($query) use ($condition_name) {
                return $query->where('User.name', 'like', '%'.$condition_name.'%');
            })
            ->if(!empty($condition_email), function ($query) use ($condition_email) {
                return $query->where('User.email', 'like', '%'.$condition_email.'%');
            })
            ->if(isset($condition_authority), function ($query) use ($condition_authority) {
                return $query->where('User.role', '=', $condition_authority);
            })
            ->orderBy('User.created_at', 'asc')
            ->paginate(20);

        } catch (\Exception $e) {

            dd($e);

            return view('User.index')->with([
                'errorMessage' => $e
            ]);
        }

        $userAuthorityName = array(
            '7' => '開発者権限',
            '5' => '経営者権限',
            '3' => '事務権限',
            '1' => '閲覧権限',
            '0' => '権限なし',
        );

        return view('User.index')->with([
            "condition_name"      => $condition_name,
            "condition_email"     => $condition_email,
            "condition_authority" => $condition_authority,
            "search_action"       => $search_action,
            "userList"            => $userList,
            "userAuthorityName"   => $userAuthorityName
        ]);
    }

    /**
     * ユーザ編集画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit($user_id)
    {
        // ユーザー情報を取得
        $editUser = DB::table('users AS User')
        ->select(
            'User.id       AS user_id',
            'User.name     AS user_name',
            'User.email    AS user_email',
            'User.role     AS user_authority'
        )
        ->where([
            ['User.id', '=', $user_id],
        ])
        ->first();

        $userAuthoritys = array(
            '7' => '開発者権限',
            '5' => '経営者権限',
            '3' => '事務権限',
            '1' => '閲覧権限',
            '0' => '権限なし',
        );

        return view('User.edit')->with([
            "editUser"       => $editUser,
            "userAuthoritys" => $userAuthoritys
        ]);
    }


    /**
     * ユーザ新規登録画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create(Request $request)
    {
        // ユーザー権限を作成
        $userAuthoritys = array(
            '7' => '開発者権限',
            '5' => '経営者権限',
            '3' => '事務権限',
            '1' => '閲覧権限',
            '0' => '権限なし',
        );

        return view('User.create')->with([
            "userAuthoritys" => $userAuthoritys,
        ]);
    }

    /**
     * ユーザ新規登録
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function register(Request $request)
    {
        // リクエストパラメータを取得
        $requestParams = $request->data['User'];

        // ユーザー情報の取得
        $user_info    = \Auth::user();
        $user_info_id = $user_info['id'];

        // トランザクション開始
        DB::connection()->beginTransaction();

        try {

            // パスワードを暗号化
            $cryptPassword = Hash::make($requestParams['password']);

            // 登録パラメータ設定
            $insertParams = array(
                'name'       => $requestParams['name'],
                'email'      => $requestParams['email'],
                'password'   => $cryptPassword,
                'role'       => $requestParams['authority'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            );

            // データ登録
            DB::table('users')->insert($insertParams);

            // 問題なければコミット
            DB::connection()->commit();

        } catch (\Exception $e) {

            // ロールバック
            DB::rollback();

            return view('User.index')->with([
                'errorMessage' => $e
            ]);

        }

        return redirect('./UserIndex');
    }

    /**
     * スタッフ編集登録
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function editRegister(Request $request)
    {

        // リクエストパラメータを取得
        $requestParams = $request->data['User'];

        // ユーザー情報の取得
        $user_info    = \Auth::user();
        $user_info_id = $user_info['id'];

        // トランザクション開始
        DB::connection()->beginTransaction();

        try {

            // パスワードを暗号化
            $cryptPassword = Hash::make($requestParams['password']);

            //---------------
            // 保存処理を行う
            //---------------
            $User = \App\User::find($requestParams['id']);
            $User->name        = $requestParams['name'];
            $User->email       = $requestParams['email'];
            $User->password    = $cryptPassword;
            $User->role        = $requestParams['authority'];
            $User->updated_at = Carbon::now();

            $User->save();

            // 問題なければコミット
            DB::connection()->commit();

        } catch (\Exception $e) {

            DB::rollback();

            return view('User.index')->with([
                'errorMessage' => $e
            ]);
        }

        return redirect('./UserIndex');
    }

}
