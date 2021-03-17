<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\SupplyShop;
use App\SupplyCompany;
use Carbon\Carbon;

class SupplyShopController extends Controller
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
     * 仕入先店舗一覧
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
            $search_action = '../SupplyShopIndex';
        } else {
            $search_action = './SupplyShopIndex';
        }

        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST"
        ) { // ページング処理

            $supply_company_id = $request->session()->get('supply_company_id');
            $supply_shop_name  = $request->session()->get('supply_shop_name');

            if($supply_company_id == '') $supply_company_id = 0;
            if($supply_shop_name == '')  $supply_shop_name  = null;

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理

                $supply_company_id = $request->data['SupplyShop']['supply_company_id'];
                $supply_shop_name  = $request->data['SupplyShop']['supply_shop_name'];

                $request->session()->put('condition_position', $supply_company_id);
                $request->session()->put('supply_shop_name', $supply_shop_name);

            } else { // リセットボタンが押された時の処理

                $supply_company_id = 0;
                $supply_shop_name  = null;
                $request->session()->forget('supply_company_id');
                $request->session()->forget('supply_shop_name');
            }
        }

        try {

            // 仕入先企業一覧を取得
            $SupplyCompanyList = SupplyCompany::where([
                ['active', 1],
            ])->orderBy('sort', 'asc')->get();

            // 仕入先店舗一覧を取得
            $SupplyShopList = DB::table('supply_shops AS SupplyShop')
            ->select(
                'SupplyShop.id               AS supply_shop_id',
                'SupplyShop.code             AS code',
                'SupplyShop.name             AS supply_shop_name',
                'SupplyCompany.closing_date  AS closing_date',
                'SupplyCompany.name          AS supply_company_name',
            )
            ->join('supply_companies AS SupplyCompany', function ($join) {
                $join->on('SupplyCompany.id', '=', 'SupplyShop.supply_company_id');
            })
            ->if(!empty($supply_shop_name), function ($query) use ($supply_shop_name) {
                return $query->whereRaw('SupplyShop.name like "%'.$supply_shop_name.'%"');
            })
            ->if(!empty($supply_company_id), function ($query) use ($supply_company_id) {
                return $query->where('SupplyShop.supply_company_id', '=', $supply_company_id);
            })
            ->where([
                ['SupplyShop.sort', '!=', '0'],
                ['SupplyShop.active', '=', '1'],
            ])
            ->orderBy('SupplyShop.created', 'asc')->paginate(20);


        } catch (\Exception $e) {

            dd($e);

            return view('SupplyShop.index')->with([
                'errorMessage' => $e
            ]);
        }

        return view('SupplyShop.index')->with([
            "supply_shop_name"    => $supply_shop_name,
            "supply_company_id"   => $supply_company_id,
            "search_action"       => $search_action,
            "SupplyCompanyList"   => $SupplyCompanyList,
            "SupplyShopList"      => $SupplyShopList,
        ]);
    }

    /**
     * 仕入店舗編集画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit($supply_shop_id)
    {
        // 仕入先企業一覧を取得
        $SupplyCompanyList = SupplyCompany::where([
            ['active', 1],
        ])->orderBy('sort', 'asc')->get();

        // 仕入れ先店舗を取得
        $editSupplyShop = DB::table('supply_shops AS SupplyShop')
        ->select(
            'SupplyShop.id                AS supply_shop_id',
            'SupplyShop.code              AS code',
            'SupplyShop.supply_company_id AS supply_company_id',
            'SupplyShop.name              AS supply_shop_name',
            'SupplyShop.postal_code       AS postal_code',
            'SupplyShop.address           AS address',
        )
        ->where([
            ['SupplyShop.id', '=', $supply_shop_id],
            ['SupplyShop.active', '=', '1'],
        ])
        ->first();

        return view('SupplyShop.edit')->with([
            "SupplyCompanyList" => $SupplyCompanyList,
            "editSupplyShop"    => $editSupplyShop,
        ]);
    }


    /**
     * 仕入れ先店舗新規登録画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create()
    {
        // 仕入れ先企業一覧取得
        $SupplyCompanyList = SupplyCompany::where([
            ['active', 1],
        ])->orderBy('sort', 'asc')->get();

        return view('SupplyShop.create')->with([
            "SupplyCompanyList"   => $SupplyCompanyList,
        ]);
    }

     /**
     * 仕入れ先店舗追加　確認
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function confirm(Request $request)
    {

        if($request->submit_type == 1){
            $action_url = './SupplyShopComplete';
        } else {
            $action_url = './SupplyShopEditComplete';
        }

        // 仕入れ先企業名取得
        $supply_company_list = SupplyCompany::where([
            ['id', $request->data['SupplyShop']['supply_company_id']],
        ])->first();

        return view('SupplyShop.confirm')->with([
            "action_url"           => $action_url,
            "supply_company_list"  => $supply_company_list,
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
            $SupplyShop = \App\SupplyShop::find($request->data['SupplyShop']['supply_shop_id']);
            $SupplyShop->supply_company_id = $request->data['SupplyShop']['supply_company_id'];
            $SupplyShop->name              = $request->data['SupplyShop']['supply_shop_name'];
            $SupplyShop->postal_code       = $request->data['SupplyShop']['postal_code'];
            $SupplyShop->address           = $request->data['SupplyShop']['address'];
            $SupplyShop->modified_user_id  = $user_info_id;               // 更新者ユーザーID
            $SupplyShop->modified          = Carbon::now();               // 更新時間

            $SupplyShop->save();


        } catch (\Exception $e) {

            DB::rollback();

            dd($e);

            return view('SupplyShop.complete')->with([
                'errorMessage' => $e
            ]);
        }

        // DBの変更を確定
        DB::commit();

        return view('SupplyShop.complete');
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
            $SupplyShop = new SupplyShop;
            $SupplyShop->supply_company_id = $request->data['SupplyShop']['supply_company_id'];
            $SupplyShop->name              = $request->data['SupplyShop']['supply_company_name'];
            $SupplyShop->postal_code       = $request->data['SupplyShop']['postal_code'];
            $SupplyShop->address           = $request->data['SupplyShop']['address'];
            $SupplyShop->sort              = 100;
            $SupplyShop->active            = 1;
            $SupplyShop->created_user_id   = $user_info_id;               // 作成者ユーザーID
            $SupplyShop->created           = Carbon::now();               // 作成時間
            $SupplyShop->modified_user_id  = $user_info_id;               // 更新者ユーザーID
            $SupplyShop->modified          = Carbon::now();               // 更新時間

            $SupplyShop->save();


        } catch (\Exception $e) {

            DB::rollback();

            dd($e);

            return view('SupplyShop.complete')->with([
                'errorMessage' => $e
            ]);
        }

        // DBの変更を確定
        DB::commit();

        return view('SupplyShop.complete');
    }

}
