<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\SupplyCompany;
use Carbon\Carbon;

class SupplyController extends Controller
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
     * 仕入先企業一覧
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
            $search_action = '../SupplyCompanyIndex';
        } else {
            $search_action = './SupplyCompanyIndex';
        }

        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST"
        ) { // ページング処理

            $supply_company_name = $request->session()->get('supply_company_name');
            $closing_date        = $request->session()->get('closing_date');

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理
                $supply_company_name = $request->data['SupplyCompany']['supply_company_name'];
                $closing_date = $request->data['SupplyCompany']['closing_date'];

                $request->session()->put('supply_company_name', $supply_company_name);
                $request->session()->put('condition_position', $closing_date);
            } else { // リセットボタンが押された時の処理

                $supply_company_name = null;
                $closing_date = 0;
                $request->session()->forget('supply_company_name');
                $request->session()->forget('closing_date');
            }
        }

        try {

            // 仕入先企業一覧を取得
            $supplyCompanyList = DB::table('supply_companies AS SupplyCompany')
            ->select(
                'SupplyCompany.id            AS supply_company_id',
                'SupplyCompany.name          AS supply_company_name',
                'SupplyCompany.closing_date  AS closing_date'
            )
            ->if(!empty($supply_company_name), function ($query) use ($supply_company_name) {
                return $query->whereRaw('SupplyCompany.name like "%'.$supply_company_name.'%"');
            })
            ->if(!empty($closing_date), function ($query) use ($closing_date) {
                return $query->where('SupplyCompany.closing_date', '=', $closing_date);
            })
            ->where('SupplyCompany.active', '=', '1')
            ->orderBy('SupplyCompany.created', 'asc')->paginate(5);


        } catch (\Exception $e) {

            dd($e);

            return view('SupplyCompany.index')->with([
                'errorMessage' => $e
            ]);
        }

        return view('SupplyCompany.index')->with([
            "supply_company_name" => $supply_company_name,
            "closing_date"        => $closing_date,
            "search_action"       => $search_action,
            "supplyCompanyList"   => $supplyCompanyList,
        ]);
    }

    /**
     * スタッフ編集画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit($supply_company_id)
    {
        // 仕入れ先企業を取得
        $editSupplyCompany = DB::table('supply_companies AS SupplyCompany')
        ->select(
            'SupplyCompany.id            AS supply_company_id',
            'SupplyCompany.name          AS supply_company_name',
            'SupplyCompany.closing_date  AS closing_date',
            'SupplyCompany.postal_code   AS postal_code',
            'SupplyCompany.address       AS address',
            'SupplyCompany.bank_code     AS bank_code',
            'SupplyCompany.bank_name     AS bank_name',
            'SupplyCompany.branch_code   AS branch_code',
            'SupplyCompany.branch_name   AS branch_name',
            'SupplyCompany.bank_type     AS bank_type',
            'SupplyCompany.bank_account  AS bank_account'
        )
        ->where([
            ['SupplyCompany.id', '=', $supply_company_id],
            ['SupplyCompany.active', '=', '1'],
        ])
        ->first();

        return view('SupplyCompany.edit')->with([
            "editSupplyCompany" => $editSupplyCompany,
        ]);
    }


    /**
     * 仕入れ先企業新規登録画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create()
    {
        return view('SupplyCompany.create');
    }

     /**
     * スタッフ新規追加　確認
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function confirm(Request $request)
    {

        if($request->submit_type == 1){
            $action_url = './SupplyCompanyComplete';
        } else {
            $action_url = './SupplyCompanyEditComplete';
        }

        return view('SupplyCompany.confirm')->with([
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
            $SupplyCompany = \App\SupplyCompany::find($request->data['SupplyCompany']['supply_company_id']);
            $SupplyCompany->name              = $request->data['SupplyCompany']['supply_company_name'];
            $SupplyCompany->closing_date      = $request->data['SupplyCompany']['closing_date'];
            $SupplyCompany->postal_code       = $request->data['SupplyCompany']['postal_code'];
            $SupplyCompany->address           = $request->data['SupplyCompany']['address'];
            $SupplyCompany->bank_code         = $request->data['SupplyCompany']['bank_code'];
            $SupplyCompany->bank_name         = $request->data['SupplyCompany']['bank_name'];
            $SupplyCompany->branch_code       = $request->data['SupplyCompany']['branch_code'];
            $SupplyCompany->branch_name       = $request->data['SupplyCompany']['branch_name'];
            $SupplyCompany->bank_type         = $request->data['SupplyCompany']['bank_type'];
            $SupplyCompany->bank_account      = $request->data['SupplyCompany']['bank_account'];
            $SupplyCompany->modified_user_id  = $user_info_id;               // 更新者ユーザーID
            $SupplyCompany->modified          = Carbon::now();               // 更新時間

            $SupplyCompany->save();


        } catch (\Exception $e) {

            DB::rollback();

            dd($e);

            return view('SupplyCompany.complete')->with([
                'errorMessage' => $e
            ]);
        }

        // DBの変更を確定
        DB::commit();

        return view('SupplyCompany.complete');
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
            $SupplyCompany = new SupplyCompany;
            $SupplyCompany->name              = $request->data['SupplyCompany']['supply_company_name'];
            $SupplyCompany->closing_date      = $request->data['SupplyCompany']['closing_date'];
            $SupplyCompany->postal_code       = $request->data['SupplyCompany']['postal_code'];
            $SupplyCompany->address           = $request->data['SupplyCompany']['address'];
            $SupplyCompany->bank_code         = $request->data['SupplyCompany']['bank_code'];
            $SupplyCompany->bank_name         = $request->data['SupplyCompany']['bank_name'];
            $SupplyCompany->branch_code       = $request->data['SupplyCompany']['branch_code'];
            $SupplyCompany->branch_name       = $request->data['SupplyCompany']['branch_name'];
            $SupplyCompany->bank_type         = $request->data['SupplyCompany']['bank_type'];
            $SupplyCompany->bank_account      = $request->data['SupplyCompany']['bank_account'];
            $SupplyCompany->sort              = 100;
            $SupplyCompany->active            = 1;
            $SupplyCompany->created_user_id   = $user_info_id;               // 作成者ユーザーID
            $SupplyCompany->created           = Carbon::now();               // 作成時間
            $SupplyCompany->modified_user_id  = $user_info_id;               // 更新者ユーザーID
            $SupplyCompany->modified          = Carbon::now();               // 更新時間

            $SupplyCompany->save();


        } catch (\Exception $e) {

            DB::rollback();

            return view('SupplyCompany.complete')->with([
                'errorMessage' => $e
            ]);
        }

        // DBの変更を確定
        DB::commit();

        return view('SupplyCompany.complete');
    }

}
