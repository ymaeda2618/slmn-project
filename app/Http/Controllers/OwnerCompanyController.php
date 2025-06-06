<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\OwnerCompany;
use Carbon\Carbon;
use Exception;

class OwnerCompanyController extends Controller
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
     * 本部企業一覧
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        // リクエストパスを取得
        $request_path = $request->path();
        $path_array   = explode('/', $request_path);

        // ページングの番号の有無でindexのaction先を変更
        if (count($path_array) > 1) {
            $search_action = '../OwnerCompanyIndex';
        } else {
            $search_action = './OwnerCompanyIndex';
        }

        // postできたか、getできたか
        if ($_SERVER["REQUEST_METHOD"] != "POST") { // ページング処理

            $owner_company_name = $request->session()->get('owner_company_name');
            $closing_date       = $request->session()->get('closing_date');

            if($owner_company_name == '') $owner_company_name = null;
            if($closing_date == '') $closing_date = 0;

        } else { // POST時の処理

            if (isset($_POST['search-btn'])) { // 検索ボタン押された時の処理
                $owner_company_name = $request->data['OwnerCompany']['owner_company_name'];
                $closing_date = $request->data['OwnerCompany']['closing_date'];

                $request->session()->put('owner_company_name', $owner_company_name);
                $request->session()->put('condition_position', $closing_date);
            } else { // リセットボタンが押された時の処理

                $owner_company_name = null;
                $closing_date = 0;
                $request->session()->forget('owner_company_name');
                $request->session()->forget('closing_date');
            }
        }

        try {

            // 本部企業一覧を取得
            $ownerCompanyList = DB::table('owner_companies AS OwnerCompany')
            ->select(
                'OwnerCompany.id           AS owner_company_id',
                'OwnerCompany.code         AS code',
                'OwnerCompany.name         AS owner_company_name',
                'OwnerCompany.closing_date AS closing_date',
            )
            ->if(!empty($owner_company_name), function ($query) use ($owner_company_name) {
                return $query->orWhere('OwnerCompany.name', 'like', "%{$owner_company_name}%")
                ->orWhere('OwnerCompany.code', 'like', "%{$owner_company_name}%");
            })
            ->if(!empty($closing_date), function ($query) use ($closing_date) {
                return $query->where('OwnerCompany.closing_date', '=', $closing_date);
            })
            ->orderBy('OwnerCompany.sort', 'asc')
            ->orderBy('OwnerCompany.created', 'desc')->paginate(20);


        } catch (\Exception $e) {

            dd($e);

            return view('OwnerCompany.index')->with([
                'errorMessage' => $e
            ]);
        }

        return view('OwnerCompany.index')->with([
            "owner_company_name" => $owner_company_name,
            "closing_date"       => $closing_date,
            "search_action"      => $search_action,
            "ownerCompanyList"   => $ownerCompanyList,
        ]);
    }

    /**
     * スタッフ編集画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit(Request $request, $owner_company_id)
    {
        // 本部企業を取得
        $editOwnerCompany = DB::table('owner_companies AS OwnerCompany')
        ->select(
            'OwnerCompany.id                          AS owner_company_id',
            'OwnerCompany.code                        AS code',
            'OwnerCompany.name                        AS owner_company_name',
            'OwnerCompany.yomi                        AS yomi',
            'OwnerCompany.tax_calc_type               AS tax_calc_type',
            'OwnerCompany.closing_date                AS closing_date',
            'OwnerCompany.postal_code                 AS postal_code',
            'OwnerCompany.address                     AS address',
            'OwnerCompany.bank_code                   AS bank_code',
            'OwnerCompany.bank_name                   AS bank_name',
            'OwnerCompany.branch_code                 AS branch_code',
            'OwnerCompany.branch_name                 AS branch_name',
            'OwnerCompany.bank_type                   AS bank_type',
            'OwnerCompany.bank_account                AS bank_account',
            'OwnerCompany.invoice_output_type         AS invoice_output_type',
            'OwnerCompany.invoice_display_flg         AS invoice_display_flg',
            'OwnerCompany.invoice_display_name        AS invoice_display_name',
            'OwnerCompany.invoice_display_address     AS invoice_display_address',
            'OwnerCompany.invoice_display_postal_code AS invoice_display_postal_code',
        )
        ->where([
            ['OwnerCompany.id', '=', $owner_company_id],
            ['OwnerCompany.active', '=', '1'],
        ])
        ->first();

        // 売上店舗一覧（この企業に属する or 未所属）
        $saleCompanies = DB::table('sale_companies')
            ->select('id', 'code', 'name', 'owner_company_id', 'yomi')
            ->where(function($query) use ($owner_company_id) {
                $query->whereNull('owner_company_id')
                    ->orWhere('owner_company_id', $owner_company_id);
            })
            ->get();

        // 仕入店舗一覧（この企業に属する or 未所属）
        $supplyCompanies = DB::table('supply_companies')
            ->select('id', 'code', 'name', 'owner_company_id', 'yomi')
            ->where(function($query) use ($owner_company_id) {
                $query->whereNull('owner_company_id')
                    ->orWhere('owner_company_id', $owner_company_id);
            })
            ->get();

        // エラーメッセージ取得
        $error_message = $request->session()->get('error_message');
        $request->session()->forget('error_message');

        return view('OwnerCompany.edit')->with([
            "editOwnerCompany" => $editOwnerCompany,
            "error_message"    => $error_message,
            "saleCompanies"    => $saleCompanies,
            "supplyCompanies"  => $supplyCompanies,
        ]);
    }


    /**
     * 本部企業新規登録画面
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create(Request $request)
    {
        // エラーメッセージ取得
        $error_message = $request->session()->get('error_message');
        $request->session()->forget('error_message');

        $saleCompanies = DB::table('sale_companies')->select('id', 'code', 'name', 'yomi')->whereNull('owner_company_id')->get();
        $supplyCompanies = DB::table('supply_companies')->select('id', 'code', 'name', 'yomi')->whereNull('owner_company_id')->get();

        return view('OwnerCompany.create')->with([
            "error_message"   => $error_message,
            "saleCompanies"   => $saleCompanies,
            "supplyCompanies" => $supplyCompanies
        ]);;
    }

     /**
     * 本部企業入力内容　確認
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function confirm(Request $request)
    {

        if ($request->submit_type == 1) {
            $action_url = './OwnerCompanyComplete';
        } else {
            $action_url = './OwnerCompanyEditComplete';
        }

        $selectedIds = explode(',', $request->data['OwnerCompany']['selected_shop_ids'] ?? '');
        $saleIds = [];
        $supplyIds = [];

        foreach ($selectedIds as $entry) {
            if (strpos($entry, 'sale:') === 0) {
                $saleIds[] = (int) str_replace('sale:', '', $entry);
            } elseif (strpos($entry, 'supply:') === 0) {
                $supplyIds[] = (int) str_replace('supply:', '', $entry);
            }
        }

        // 対象の名前を取得
        $saleShops = DB::table('sale_companies')->whereIn('id', $saleIds)->get();
        $supplyShops = DB::table('supply_companies')->whereIn('id', $supplyIds)->get();

        return view('OwnerCompany.confirm')->with([
            'action_url'  => $action_url,
            'request'     => $request,
            'saleShops'   => $saleShops,
            'supplyShops' => $supplyShops,
        ]);
    }

    /**
     * 編集登録
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
            $owner_company_code = $request->data['OwnerCompany']['code'];

            // codeが存在するかチェック
            $OwnerCompanyCodeCheck = DB::table('owner_companies AS OwnerCompany')
            ->select(
                'OwnerCompany.code AS code'
            )
            ->where([
                ['OwnerCompany.id', '!=', $request->data['OwnerCompany']['owner_company_id']],
                ['OwnerCompany.active', '=', '1'],
                ['OwnerCompany.code', '=', $owner_company_code],
            ])->orderBy('id', 'desc')->first();

            if (!empty($OwnerCompanyCodeCheck)) {

                $exception_type = 1;

                throw new Exception();
            }

            //---------------
            // 保存処理を行う
            //---------------
            $OwnerCompany = \App\OwnerCompany::find($request->data['OwnerCompany']['owner_company_id']);
            $OwnerCompany->code                        = $owner_company_code;                                               // コード番号
            $OwnerCompany->name                        = $request->data['OwnerCompany']['owner_company_name'];              // 本部企業名
            $OwnerCompany->yomi                        = $request->data['OwnerCompany']['yomi'];                            // ヨミガナ
            $OwnerCompany->tax_calc_type               = $request->data['OwnerCompany']['tax_calc_type'];                   // 消費税計算区分
            $OwnerCompany->closing_date                = $request->data['OwnerCompany']['closing_date'];                    // 締日
            $OwnerCompany->postal_code                 = $request->data['OwnerCompany']['postal_code'];                     // 郵便番号
            $OwnerCompany->address                     = $request->data['OwnerCompany']['address'];                         // 住所
            $OwnerCompany->bank_code                   = $request->data['OwnerCompany']['bank_code'];                       // 金融機関コード
            $OwnerCompany->bank_name                   = $request->data['OwnerCompany']['bank_name'];                       // 銀行名
            $OwnerCompany->branch_code                 = $request->data['OwnerCompany']['branch_code'];                     // 支店コード
            $OwnerCompany->branch_name                 = $request->data['OwnerCompany']['branch_name'];                     // 支店名
            $OwnerCompany->bank_type                   = $request->data['OwnerCompany']['bank_type'];                       // 口座種別
            $OwnerCompany->bank_account                = $request->data['OwnerCompany']['bank_account'];                    // 口座番号
            $OwnerCompany->invoice_output_type         = $request->data['OwnerCompany']['invoice_output_type'];             // 請求書出力タイプ(0:本部企業毎, 1:店舗毎)
            $OwnerCompany->invoice_display_flg         = $request->data['OwnerCompany']['invoice_display_flg'];             // 請求書表示フラグ
            $OwnerCompany->invoice_display_name        = $request->data['OwnerCompany']['invoice_display_name'];            // 請求書表示名
            $OwnerCompany->invoice_display_address     = $request->data['OwnerCompany']['invoice_display_address'];         // 請求書表示郵便番号
            $OwnerCompany->invoice_display_postal_code = $request->data['OwnerCompany']['invoice_display_postal_code'];     // 請求書表示住所
            $OwnerCompany->modified_user_id            = $user_info_id;                                                     // 更新者ユーザーID
            $OwnerCompany->modified                    = Carbon::now();                                                     // 更新時間

            $OwnerCompany->save();

            //---------------
            // 店舗紐づけ処理を追加
            //---------------
            // 先にこの企業に紐づく店舗の owner_company_id を全て null に戻す
            DB::table('sale_companies')->where('owner_company_id', $OwnerCompany->id)->update(['owner_company_id' => null]);
            DB::table('supply_companies')->where('owner_company_id', $OwnerCompany->id)->update(['owner_company_id' => null]);

            // 選択されたものだけに再設定
            $selected = explode(',', $request->data['OwnerCompany']['selected_shop_ids'] ?? '');
            $saleIds = [];
            $supplyIds = [];

            foreach ($selected as $entry) {
                if (strpos($entry, 'sale:') === 0) {
                    $saleIds[] = (int) str_replace('sale:', '', $entry);
                } elseif (strpos($entry, 'supply:') === 0) {
                    $supplyIds[] = (int) str_replace('supply:', '', $entry);
                }
            }

            if (!empty($saleIds)) {
                DB::table('sale_companies')->whereIn('id', $saleIds)->update(['owner_company_id' => $OwnerCompany->id]);
            }
            if (!empty($supplyIds)) {
                DB::table('supply_companies')->whereIn('id', $supplyIds)->update(['owner_company_id' => $OwnerCompany->id]);
            }

        } catch (\Exception $e) {

            DB::rollback();

            if ($exception_type == 1) { // 登録済みのコードを指定の場合

                $errorMsg = "指定のコードは既に登録済みです。";
                $request->session()->put('error_message', $errorMsg);

                return redirect('./OwnerCompanyEdit/'.$request->data['OwnerCompany']['owner_company_id']);
            }

            return view('OwnerCompany.complete')->with([
                'errorMessage' => $e
            ]);
        }

        // DBの変更を確定
        DB::commit();

        return view('OwnerCompany.complete');
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
            if (empty($request->data['OwnerCompany']['code'])) {

                $max_code = DB::table('owner_companies AS OwnerCompany')
                    ->where('OwnerCompany.active', 1)
                    ->whereRaw('OwnerCompany.code REGEXP "^[0-9]+$"')
                    ->max(DB::raw('CAST(OwnerCompany.code AS UNSIGNED)'));

                $owner_company_code = $max_code ? $max_code + 1 : 1000;

            } else {

                // リクエストされたコードを格納
                $owner_company_code = $request->data['OwnerCompany']['code'];

                // codeが存在するかチェック
                $code_exists = DB::table('owner_companies AS OwnerCompany')
                    ->where([
                        ['OwnerCompany.active', '=', '1'],
                        ['OwnerCompany.code', '=', $owner_company_code],
                    ])->exists();

                if ($code_exists) {

                    $exception_type = 1;

                    throw new Exception();
                }
            }

            //---------------
            // 保存処理を行う
            //---------------
            $OwnerCompany = new OwnerCompany;
            $OwnerCompany->code                        = $owner_company_code;
            $OwnerCompany->name                        = $request->data['OwnerCompany']['owner_company_name'];
            $OwnerCompany->yomi                        = $request->data['OwnerCompany']['yomi'];
            $OwnerCompany->tax_calc_type               = $request->data['OwnerCompany']['tax_calc_type'];
            $OwnerCompany->closing_date                = $request->data['OwnerCompany']['closing_date'];
            $OwnerCompany->postal_code                 = $request->data['OwnerCompany']['postal_code'];
            $OwnerCompany->address                     = $request->data['OwnerCompany']['address'];
            $OwnerCompany->bank_code                   = $request->data['OwnerCompany']['bank_code'];
            $OwnerCompany->bank_name                   = $request->data['OwnerCompany']['bank_name'];
            $OwnerCompany->branch_code                 = $request->data['OwnerCompany']['branch_code'];
            $OwnerCompany->branch_name                 = $request->data['OwnerCompany']['branch_name'];
            $OwnerCompany->bank_type                   = $request->data['OwnerCompany']['bank_type'];
            $OwnerCompany->bank_account                = $request->data['OwnerCompany']['bank_account'];
            $OwnerCompany->invoice_output_type         = $request->data['OwnerCompany']['invoice_output_type'];
            $OwnerCompany->invoice_display_flg         = $request->data['OwnerCompany']['invoice_display_flg'];
            $OwnerCompany->invoice_display_name        = $request->data['OwnerCompany']['invoice_display_name'];
            $OwnerCompany->invoice_display_address     = $request->data['OwnerCompany']['invoice_display_address'];
            $OwnerCompany->invoice_display_postal_code = $request->data['OwnerCompany']['invoice_display_postal_code'];
            $OwnerCompany->sort                        = 100;
            $OwnerCompany->active                      = 1;
            $OwnerCompany->created_user_id             = $user_info_id;               // 作成者ユーザーID
            $OwnerCompany->created                     = Carbon::now();               // 作成時間
            $OwnerCompany->modified_user_id            = $user_info_id;               // 更新者ユーザーID
            $OwnerCompany->modified                    = Carbon::now();               // 更新時間

            $OwnerCompany->save();

            //---------------
            // 店舗紐づけ処理を追加
            //---------------
            $selectedIdsRaw = $request->data['OwnerCompany']['selected_shop_ids'] ?? '';
            $selectedIds = explode(',', $selectedIdsRaw);
            $saleIds = [];
            $supplyIds = [];

            foreach ($selectedIds as $entry) {
                if (strpos($entry, 'sale:') === 0) {
                    $saleIds[] = (int) str_replace('sale:', '', $entry);
                } elseif (strpos($entry, 'supply:') === 0) {
                    $supplyIds[] = (int) str_replace('supply:', '', $entry);
                }
            }

            if (!empty($saleIds)) {
                DB::table('sale_companies')
                    ->whereIn('id', $saleIds)
                    ->update(['owner_company_id' => $OwnerCompany->id]);
            }

            if (!empty($supplyIds)) {
                DB::table('supply_companies')
                    ->whereIn('id', $supplyIds)
                    ->update(['owner_company_id' => $OwnerCompany->id]);
            }


        } catch (\Exception $e) {

            DB::rollback();

            if ($exception_type == 1) { // 登録済みのコードを指定の場合

                $errorMsg = "指定のコードは既に登録済みです。";
                $request->session()->put('error_message', $errorMsg);

                return redirect('./OwnerCompanyCreate');
            }

            return view('OwnerCompany.complete')->with([
                'errorMessage' => $e
            ]);
        }

        // DBの変更を確定
        DB::commit();

        return view('OwnerCompany.complete');
    }

    /**
     * 本部企業に紐づいている店舗を取得
     *
     * @return void
     */
    public function AjaxGetShops($owner_company_id)
    {
        $saleCompanies = DB::table('sale_companies')
            ->select('code', 'name', 'yomi', DB::raw("'売上' as type"))
            ->where('owner_company_id', $owner_company_id);

        $supplyCompanies = DB::table('supply_companies')
            ->select('code', 'name', 'yomi', DB::raw("'仕入' as type"))
            ->where('owner_company_id', $owner_company_id);

        $shops = $saleCompanies->unionAll($supplyCompanies)->get();

        return response()->json($shops);
    }

}
