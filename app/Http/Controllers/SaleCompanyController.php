<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\SaleCompany;
use Carbon\Carbon;

class SaleCompanyController extends Controller
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
     * 売上先企業一覧
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
            $search_action = '../SaleCompanyIndex';
        } else {
            $search_action = './SaleCompanyIndex';
        }

        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST"
        ) { // ページング処理

            $sale_company_name = $request->session()->get('sale_company_name');
            $closing_date        = $request->session()->get('closing_date');

            if($sale_company_name == '') $sale_company_name = null;
            if($closing_date == '')      $closing_date      = 0;

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理
                $sale_company_name = $request->data['SaleCompany']['sale_company_name'];
                $closing_date = $request->data['SaleCompany']['closing_date'];

                $request->session()->put('sale_company_name', $sale_company_name);
                $request->session()->put('condition_position', $closing_date);
            } else { // リセットボタンが押された時の処理

                $sale_company_name = null;
                $closing_date = 0;
                $request->session()->forget('sale_company_name');
                $request->session()->forget('closing_date');
            }
        }

        try {

            // 売上先企業一覧を取得
            $saleCompanyList = DB::table('sale_companies AS SaleCompany')
            ->select(
                'SaleCompany.id            AS sale_company_id',
                'SaleCompany.code          AS code',
                'SaleCompany.name          AS sale_company_name',
                'SaleCompany.closing_date  AS closing_date',
            )
            ->if(!empty($sale_company_name), function ($query) use ($sale_company_name) {
                return $query->whereRaw('SaleCompany.name like "%'.$sale_company_name.'%"');
            })
            ->if(!empty($closing_date), function ($query) use ($closing_date) {
                return $query->where('SaleCompany.closing_date', '=', $closing_date);
            })
            ->where('SaleCompany.active', '=', '1')
            ->orderBy('SaleCompany.created', 'asc')->paginate(20);


        } catch (\Exception $e) {

            dd($e);

            return view('SaleCompany.index')->with([
                'errorMessage' => $e
            ]);
        }

        return view('SaleCompany.index')->with([
            "sale_company_name" => $sale_company_name,
            "closing_date"        => $closing_date,
            "search_action"       => $search_action,
            "saleCompanyList"   => $saleCompanyList,
        ]);
    }

    /**
     * スタッフ編集画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit($sale_company_id)
    {
        // 売上先企業を取得
        $editSaleCompany = DB::table('sale_companies AS SaleCompany')
        ->select(
            'SaleCompany.id            AS sale_company_id',
            'SaleCompany.name          AS sale_company_name',
            'SaleCompany.closing_date  AS closing_date',
            'SaleCompany.postal_code   AS postal_code',
            'SaleCompany.address       AS address',
            'SaleCompany.bank_code     AS bank_code',
            'SaleCompany.bank_name     AS bank_name',
            'SaleCompany.branch_code   AS branch_code',
            'SaleCompany.branch_name   AS branch_name',
            'SaleCompany.bank_type     AS bank_type',
            'SaleCompany.bank_account  AS bank_account',
        )
        ->where([
            ['SaleCompany.id', '=', $sale_company_id],
            ['SaleCompany.active', '=', '1'],
        ])
        ->first();

        return view('SaleCompany.edit')->with([
            "editSaleCompany" => $editSaleCompany,
        ]);
    }


    /**
     * 売上先企業新規登録画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create()
    {
        return view('SaleCompany.create');
    }

     /**
     * スタッフ新規追加　確認
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function confirm(Request $request)
    {

        if($request->submit_type == 1){
            $action_url = './SaleCompanyComplete';
        } else {
            $action_url = './SaleCompanyEditComplete';
        }

        return view('SaleCompany.confirm')->with([
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

        try {

            //---------------
            // 保存処理を行う
            //---------------
            $SaleCompany = \App\SaleCompany::find($request->data['SaleCompany']['sale_company_id']);
            $SaleCompany->name              = $request->data['SaleCompany']['sale_company_name'];
            $SaleCompany->closing_date      = $request->data['SaleCompany']['closing_date'];
            $SaleCompany->postal_code       = $request->data['SaleCompany']['postal_code'];
            $SaleCompany->address           = $request->data['SaleCompany']['address'];
            $SaleCompany->bank_code         = $request->data['SaleCompany']['bank_code'];
            $SaleCompany->bank_name         = $request->data['SaleCompany']['bank_name'];
            $SaleCompany->branch_code       = $request->data['SaleCompany']['branch_code'];
            $SaleCompany->branch_name       = $request->data['SaleCompany']['branch_name'];
            $SaleCompany->bank_type         = $request->data['SaleCompany']['bank_type'];
            $SaleCompany->bank_account      = $request->data['SaleCompany']['bank_account'];
            $SaleCompany->modified_user_id  = $user_info_id;               // 更新者ユーザーID
            $SaleCompany->modified          = Carbon::now();               // 更新時間

            $SaleCompany->save();


        } catch (\Exception $e) {

            DB::rollback();

            dd($e);

            return view('SaleCompany.complete')->with([
                'errorMessage' => $e
            ]);
        }

        // DBの変更を確定
        DB::commit();

        return view('SaleCompany.complete');
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

        try {

            //---------------
            // 保存処理を行う
            //---------------
            $SaleCompany = new SaleCompany;
            $SaleCompany->name              = $request->data['SaleCompany']['sale_company_name'];
            $SaleCompany->closing_date      = $request->data['SaleCompany']['closing_date'];
            $SaleCompany->postal_code       = $request->data['SaleCompany']['postal_code'];
            $SaleCompany->address           = $request->data['SaleCompany']['address'];
            $SaleCompany->bank_code         = $request->data['SaleCompany']['bank_code'];
            $SaleCompany->bank_name         = $request->data['SaleCompany']['bank_name'];
            $SaleCompany->branch_code       = $request->data['SaleCompany']['branch_code'];
            $SaleCompany->branch_name       = $request->data['SaleCompany']['branch_name'];
            $SaleCompany->bank_type         = $request->data['SaleCompany']['bank_type'];
            $SaleCompany->bank_account      = $request->data['SaleCompany']['bank_account'];
            $SaleCompany->sort              = 100;
            $SaleCompany->active            = 1;
            $SaleCompany->created_user_id   = $user_info_id;               // 作成者ユーザーID
            $SaleCompany->created           = Carbon::now();               // 作成時間
            $SaleCompany->modified_user_id  = $user_info_id;               // 更新者ユーザーID
            $SaleCompany->modified          = Carbon::now();               // 更新時間

            $SaleCompany->save();


        } catch (\Exception $e) {

            DB::rollback();

            dd($e);

            return view('SaleCompany.complete')->with([
                'errorMessage' => $e
            ]);
        }

        // DBの変更を確定
        DB::commit();

        return view('SaleCompany.complete');
    }

}
