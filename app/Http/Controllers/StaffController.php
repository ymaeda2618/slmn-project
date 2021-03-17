<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Staff;
use App\StaffPosition;
use Carbon\Carbon;
use Exception;

class StaffController extends Controller
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
     * スタッフ一覧
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
            $search_action = '../StaffIndex';
        } else {
            $search_action = './StaffIndex';
        }

        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST"
        ) { // ページング処理

            $condition_name = $request->session()->get('condition_name');
            $condition_position = $request->session()->get('condition_position');

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理
                $condition_name = $request->data['Staff']['staff_name'];
                $condition_position = $request->data['Staff']['staff_position'];

                $request->session()->put('condition_name', $condition_name);
                $request->session()->put('condition_position', $condition_position);
            } else { // リセットボタンが押された時の処理

                $condition_name = null;
                $condition_position = null;
                $request->session()->forget('condition_name');
                $request->session()->forget('condition_position');
            }
        }

        try {

            // スタッフ役職一覧を取得
            $positionList = StaffPosition::where([
                ['active', 1],
            ])->orderBy('sort', 'asc')->get();

            // スタッフ一覧を取得
            $staffList = DB::table('staffs AS Staff')
            ->select(
                'Staff.id                   AS staff_id',
                'Staff.code                 AS code',
                'StaffPosition.name         AS staff_position_name'
            )
            ->selectRaw('CONCAT(Staff.name_sei," ",Staff.name_mei) AS staff_name')
            ->join('staff_positions AS StaffPosition', function ($join) {
                $join->on('StaffPosition.id', '=', 'Staff.staff_position_id');
            })
            ->if(!empty($condition_name), function ($query) use ($condition_name) {
                return $query->whereRaw('CONCAT(Staff.name_sei,Staff.name_mei) like "%'.$condition_name.'%"');
            })
            ->if(!empty($condition_position), function ($query) use ($condition_position) {
                return $query->where('Staff.staff_position_id', '=', $condition_position);
            })
            ->where('Staff.active', '=', '1')
            ->orderBy('Staff.created', 'asc')->paginate(20);


        } catch (\Exception $e) {

            dd($e);

            return view('Staff.complete')->with([
                'errorMessage' => $e
            ]);
        }

        return view('Staff.index')->with([
            "action"             => $search_action,
            "condition_name"     => $condition_name,
            "condition_position" => $condition_position,
            "search_action"      => $search_action,
            "positionList"       => $positionList,
            "staffList"          => $staffList,
        ]);
    }

    /**
     * スタッフ編集画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit(Request $request, $staff_id)
    {
        // スタッフ役職一覧を取得
        $staffPositionList = StaffPosition::where([
            ['active', 1],
        ])->orderBy('sort', 'asc')->get();

        // スタッフを取得
        $editStaff = DB::table('staffs AS Staff')
        ->select(
            'Staff.id                 AS staff_id',
            'Staff.code               AS code',
            'Staff.name_sei           AS staff_name_sei',
            'Staff.name_mei           AS staff_name_mei',
            'Staff.yomi_sei           AS staff_yomi_sei',
            'Staff.yomi_mei           AS staff_yomi_mei',
            'Staff.staff_position_id  AS staff_position_id',
        )
        ->where([
            ['Staff.id', '=', $staff_id],
            ['Staff.active', '=', '1'],
        ])
        ->first();

        // エラーメッセージ取得
        $error_message       = $request->session()->get('error_message');
        $request->session()->forget('error_message');

        return view('Staff.edit')->with([
            "staffPositionList"   => $staffPositionList,
            "editStaff"           => $editStaff,
            "error_message"       => $error_message,
        ]);
    }


    /**
     * スタッフ新規登録画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create(Request $request)
    {
        // スタッフ役職一覧を取得
        $staffPositionList = StaffPosition::where([
            ['active', 1],
        ])->orderBy('sort', 'asc')->get();

        // エラーメッセージ取得
        $error_message       = $request->session()->get('error_message');
        $request->session()->forget('error_message');

        return view('Staff.create')->with([
            "staffPositionList"  => $staffPositionList,
            "error_message"      => $error_message,
        ]);
    }

     /**
     * スタッフ新規追加　確認
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function confirm(Request $request)
    {

        if($request->submit_type == 1){
            $action_url = './StaffComplete';
        } else {
            $action_url = './StaffEditComplete';
        }

        // 役職名を取得
        $staff_position_name = StaffPosition::where([
            ['id', $request->staff_position],
        ])->first();

        // 役職名をリクエストに格納
        $request->staff_position_name = $staff_position_name->name;

        return view('Staff.confirm')->with([
            "action_url"           => $action_url,
            "request"              => $request,
        ]);
    }

    /**
     * スタッフ編集登録
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function editComplete(Request $request)
    {

        // ユーザー情報の取得
        $user_info    = \Auth::user();
        $user_info_id = $user_info['id'];

        // トランザクション処理
        DB::beginTransaction();

        // エラータイプを初期化
        $exception_type = 0;

        try {

            // リクエストされたコードを格納
            $staff_code = $request->code;

            // codeが存在するかチェック
            $staffCodeCheck = DB::table('staffs AS Staff')
            ->select(
                'Staff.code AS code'
            )
            ->where([
                ['Staff.id', '!=', $request->staff_id],
                ['Staff.active', '=', '1'],
                ['Staff.code', '=', $staff_code],
            ])->orderBy('id', 'desc')->first();

            if (!empty($staffCodeCheck)){

                $exception_type = 1;

                throw new Exception();
            }

            //---------------
            // 保存処理を行う
            //---------------
            $Staff = \App\Staff::find($request->staff_id);
            $Staff->code              = $staff_code;                 // コード
            $Staff->name_sei          = $request->staff_name_sei;    // スタッフ-姓
            $Staff->name_mei          = $request->staff_name_mei;    // スタッフ-名
            $Staff->yomi_sei          = $request->staff_yomi_sei;    // スタッフ-ヨミ-姓
            $Staff->yomi_mei          = $request->staff_yomi_mei;    // スタッフ-ヨミ-名
            $Staff->staff_position_id = $request->staff_position;    // スタッフ役職ID
            $Staff->modified_user_id  = $user_info_id;               // 更新者ユーザーID
            $Staff->modified          = Carbon::now();               // 更新時間

            $Staff->save();


        } catch (\Exception $e) {

            DB::rollback();

            if($exception_type == 1){ // 登録済みのコードを指定の場合

                $errorMsg = "指定のコードは既に登録済みです。";
                $request->session()->put('error_message', $errorMsg);

                return redirect('./StaffEdit/'.$request->staff_id);
            }

            return view('Staff.complete')->with([
                'errorMessage' => $e
            ]);
        }

        // DBの変更を確定
        DB::commit();

        return view('Staff.complete');
    }

    /**
     * イベント新規追加　登録
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function complete(Request $request)
    {

        // ユーザー情報の取得
        $user_info    = \Auth::user();
        $user_info_id = $user_info['id'];

        // トランザクション処理
        DB::beginTransaction();

        // エラータイプを初期化
        $exception_type = 0;

        try {

            // codeが入力されていない場合
            if(empty($request->code)){

                do {
                    // codeのMAX値を取得
                    $staffCode = DB::table('staffs AS Staff')
                    ->select(
                        'Staff.code AS code'
                    )
                    ->where([
                        ['Staff.active', '=', '1'],
                    ])->orderBy('id', 'desc')->first();

                    $staff_code = $staffCode->code + 1;

                    // codeが存在するかチェック
                    $staffCodeCheck = DB::table('staffs AS Staff')
                    ->select(
                        'Staff.code AS code'
                    )
                    ->where([
                        ['Staff.active', '=', '1'],
                        ['Staff.code', '=', $staff_code],
                    ])->orderBy('id', 'desc')->first();

                } while (!empty($staffCodeCheck));

            } else {

                // リクエストされたコードを格納
                $staff_code = $request->code;

                // codeが存在するかチェック
                $staffCodeCheck = DB::table('staffs AS Staff')
                ->select(
                    'Staff.code AS code'
                )
                ->where([
                    ['Staff.active', '=', '1'],
                    ['Staff.code', '=', $staff_code],
                ])->orderBy('id', 'desc')->first();

                if (!empty($staffCodeCheck)){

                    $exception_type = 1;

                    throw new Exception();
                }
            }

            //---------------
            // 保存処理を行う
            //---------------
            $Staff = new Staff;
            $Staff->code              = $staff_code;                 // コード
            $Staff->name_sei          = $request->staff_name_sei;    // スタッフ-姓
            $Staff->name_mei          = $request->staff_name_mei;    // スタッフ-名
            $Staff->yomi_sei          = $request->staff_yomi_sei;    // スタッフ-ヨミ-姓
            $Staff->yomi_mei          = $request->staff_yomi_mei;    // スタッフ-ヨミ-名
            $Staff->staff_position_id = $request->staff_position;    // スタッフ役職ID
            $Staff->created_user_id   = $user_info_id;               // 作成者ユーザーID
            $Staff->created           = Carbon::now();               // 作成時間
            $Staff->modified_user_id  = $user_info_id;               // 更新者ユーザーID
            $Staff->modified          = Carbon::now();               // 更新時間

            $Staff->save();


        } catch (\Exception $e) {

            DB::rollback();

            if($exception_type == 1){ // 登録済みのコードを指定の場合

                $errorMsg = "指定のコードは既に登録済みです。";
                $request->session()->put('error_message', $errorMsg);

                return redirect('./StaffCreate');
            }

            return view('Staff.complete')->with([
                'errorMessage' => $e
            ]);
        }

        // DBの変更を確定
        DB::commit();

        return view('Staff.complete');
    }

}
