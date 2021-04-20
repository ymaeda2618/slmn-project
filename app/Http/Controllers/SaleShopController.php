<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\SaleShop;
use App\SaleCompany;
use Carbon\Carbon;
use Exception;

class SaleShopController extends Controller
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
     * 売上先店舗一覧
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
            $search_action = '../SaleShopIndex';
        } else {
            $search_action = './SaleShopIndex';
        }

        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST"
        ) { // ページング処理

            $sale_company_id = $request->session()->get('sale_company_id');
            $sale_shop_name  = $request->session()->get('sale_shop_name');

            if($sale_company_id == '') $sale_company_id = 0;
            if($sale_shop_name == '')  $sale_shop_name  = null;

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理

                $sale_company_id = $request->data['SaleShop']['sale_company_id'];
                $sale_shop_name  = $request->data['SaleShop']['sale_shop_name'];

                $request->session()->put('condition_position', $sale_company_id);
                $request->session()->put('sale_shop_name', $sale_shop_name);

            } else { // リセットボタンが押された時の処理

                $sale_company_id = 0;
                $sale_shop_name  = null;
                $request->session()->forget('sale_company_id');
                $request->session()->forget('sale_shop_name');
            }
        }

        try {

            // 売上先企業一覧を取得
            $SaleCompanyList = SaleCompany::where([
                ['active', 1],
            ])->orderBy('sort', 'asc')->get();

            // 売上先店舗一覧を取得
            $SaleShopList = DB::table('sale_shops AS SaleShop')
            ->select(
                'SaleShop.id               AS sale_shop_id',
                'SaleShop.code             AS code',
                'SaleShop.name             AS sale_shop_name',
                'SaleCompany.closing_date  AS closing_date',
                'SaleCompany.name          AS sale_company_name',
            )
            ->join('sale_companies AS SaleCompany', function ($join) {
                $join->on('SaleCompany.id', '=', 'SaleShop.sale_company_id');
            })
            ->if(!empty($sale_shop_name), function ($query) use ($sale_shop_name) {
                return $query->whereRaw('SaleShop.name like "%'.$sale_shop_name.'%"');
            })
            ->if(!empty($sale_company_id), function ($query) use ($sale_company_id) {
                return $query->where('SaleShop.sale_company_id', '=', $sale_company_id);
            })
            ->where([
                ['SaleShop.sort', '!=', '0'],
                ['SaleShop.active', '=', '1'],
            ])
            ->orderBy('SaleShop.created', 'asc')->paginate(20);


        } catch (\Exception $e) {

            dd($e);

            return view('SaleShop.index')->with([
                'errorMessage' => $e
            ]);
        }

        return view('SaleShop.index')->with([
            "sale_shop_name"    => $sale_shop_name,
            "sale_company_id"   => $sale_company_id,
            "search_action"       => $search_action,
            "SaleCompanyList"   => $SaleCompanyList,
            "SaleShopList"      => $SaleShopList,
        ]);
    }

    /**
     * 仕入店舗編集画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit(Request $request, $sale_shop_id)
    {
        // 売上先企業一覧を取得
        $SaleCompanyList = SaleCompany::where([
            ['active', 1],
        ])->orderBy('sort', 'asc')->get();

        // 売上先店舗を取得
        $editSaleShop = DB::table('sale_shops AS SaleShop')
        ->select(
            'SaleShop.id                AS sale_shop_id',
            'SaleShop.sale_company_id AS sale_company_id',
            'SaleShop.name              AS sale_shop_name',
            'SaleShop.postal_code       AS postal_code',
            'SaleShop.address           AS address',
        )
        ->where([
            ['SaleShop.id', '=', $sale_shop_id],
            ['SaleShop.active', '=', '1'],
        ])
        ->first();

        // エラーメッセージ取得
        $error_message       = $request->session()->get('error_message');
        $request->session()->forget('error_message');

        return view('SaleShop.edit')->with([
            "SaleCompanyList" => $SaleCompanyList,
            "editSaleShop"    => $editSaleShop,
            "error_message"     => $error_message,
        ]);
    }


    /**
     * 売上先店舗新規登録画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create(Request $request)
    {
        // 売上先企業一覧取得
        $SaleCompanyList = SaleCompany::where([
            ['active', 1],
        ])->orderBy('sort', 'asc')->get();

        // エラーメッセージ取得
        $error_message       = $request->session()->get('error_message');
        $request->session()->forget('error_message');

        return view('SaleShop.create')->with([
            "SaleCompanyList"   => $SaleCompanyList,
            "error_message"       => $error_message,
        ]);
    }

     /**
     * 売上先店舗追加　確認
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function confirm(Request $request)
    {

        if($request->submit_type == 1){
            $action_url = './SaleShopComplete';
        } else {
            $action_url = './SaleShopEditComplete';
        }

        // 売上先企業名取得
        $sale_company_list = SaleCompany::where([
            ['id', $request->data['SaleShop']['sale_company_id']],
        ])->first();

        return view('SaleShop.confirm')->with([
            "action_url"           => $action_url,
            "sale_company_list"  => $sale_company_list,
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
            $sale_shop_code = $request->data['SaleShop']['code'];

            // codeが存在するかチェック
            $saleShopCodeCheck = DB::table('sale_shops AS SaleShop')
            ->select(
                'SaleShop.code AS code'
            )
            ->where([
                ['SaleShop.id', '!=', $request->data['SaleShop']['sale_shop_id']],
                ['SaleShop.active', '=', '1'],
                ['SaleShop.code', '=', $sale_shop_code],
            ])->orderBy('id', 'desc')->first();

            if (!empty($saleShopCodeCheck)){

                $exception_type = 1;

                throw new Exception();
            }

            //---------------
            // 保存処理を行う
            //---------------
            $SaleShop = \App\SaleShop::find($request->data['SaleShop']['sale_shop_id']);
            $SaleShop->code              = $sale_shop_code;
            $SaleShop->sale_company_id   = $request->data['SaleShop']['sale_company_id'];
            $SaleShop->name              = $request->data['SaleShop']['sale_shop_name'];
            $SaleShop->yomi              = $request->data['SaleShop']['yomi'];
            $SaleShop->postal_code       = $request->data['SaleShop']['postal_code'];
            $SaleShop->address           = $request->data['SaleShop']['address'];
            $SaleShop->modified_user_id  = $user_info_id;               // 更新者ユーザーID
            $SaleShop->modified          = Carbon::now();               // 更新時間

            $SaleShop->save();


        } catch (\Exception $e) {

            DB::rollback();

            dd($e);

            if($exception_type == 1){ // 登録済みのコードを指定の場合

                $errorMsg = "指定のコードは既に登録済みです。";
                $request->session()->put('error_message', $errorMsg);

                return redirect('./SaleShopEdit/'.$request->data['SaleShop']['sale_shop_id']);
            }

            return view('SaleShop.complete')->with([
                'errorMessage' => $e
            ]);
        }

        // DBの変更を確定
        DB::commit();

        return view('SaleShop.complete');
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
            if(empty($request->data['SaleShop']['code'])){

                do {
                    // codeのMAX値を取得
                    $saleShopCode = DB::table('sale_shops AS SaleShop')
                    ->select(
                        'SaleShop.code AS code'
                    )
                    ->where([
                        ['SaleShop.code', '!=', ''],
                        ['SaleShop.active', '=', '1'],
                    ])
                    ->whereNotNull('SaleShop.code')->orderBy('id', 'desc')->first();

                    if(empty($saleShopCode)) {
                        $sale_shop_code = 1;
                    } else {
                        $sale_shop_code = $saleShopCode->code + 1;
                    }

                    // codeが存在するかチェック
                    $saleShopCodeCheck = DB::table('sale_shops AS SaleShop')
                    ->select(
                        'SaleShop.code AS code'
                    )
                    ->where([
                        ['SaleShop.active', '=', '1'],
                        ['SaleShop.code', '=', $sale_shop_code],
                    ])->orderBy('id', 'desc')->first();

                } while (!empty($saleShopCodeCheck));

            } else {

                // リクエストされたコードを格納
                $sale_shop_code = $request->data['SaleShop']['code'];

                // codeが存在するかチェック
                $saleShopCodeCheck = DB::table('sale_shops AS SaleShop')
                ->select(
                    'SaleShop.code AS code'
                )
                ->where([
                    ['SaleShop.active', '=', '1'],
                    ['SaleShop.code', '=', $sale_shop_code],
                ])->orderBy('id', 'desc')->first();

                if (!empty($saleShopCodeCheck)){

                    $exception_type = 1;

                    throw new Exception();
                }
            }

            //---------------
            // 保存処理を行う
            //---------------
            $SaleShop = new SaleShop;
            $SaleShop->code              = $sale_shop_code;
            $SaleShop->sale_company_id   = $request->data['SaleShop']['sale_company_id'];
            $SaleShop->name              = $request->data['SaleShop']['sale_company_name'];
            $SaleShop->yomi              = $request->data['SaleShop']['yomi'];
            $SaleShop->postal_code       = $request->data['SaleShop']['postal_code'];
            $SaleShop->address           = $request->data['SaleShop']['address'];
            $SaleShop->sort              = 100;
            $SaleShop->active            = 1;
            $SaleShop->created_user_id   = $user_info_id;               // 作成者ユーザーID
            $SaleShop->created           = Carbon::now();               // 作成時間
            $SaleShop->modified_user_id  = $user_info_id;               // 更新者ユーザーID
            $SaleShop->modified          = Carbon::now();               // 更新時間

            $SaleShop->save();


        } catch (\Exception $e) {

            DB::rollback();

            dd($e);

            if($exception_type == 1){ // 登録済みのコードを指定の場合

                $errorMsg = "指定のコードは既に登録済みです。";
                $request->session()->put('error_message', $errorMsg);

                return redirect('./SaleShopCreate');
            }

            return view('SaleShop.complete')->with([
                'errorMessage' => $e
            ]);
        }

        // DBの変更を確定
        DB::commit();

        return view('SaleShop.complete');
    }

}
