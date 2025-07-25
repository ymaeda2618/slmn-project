<?php

namespace App\Http\ViewCreators;

use Illuminate\View\View,
    Illuminate\Support\Facades\View as V,
    Illuminate\View\ViewName,
    Illuminate\View\FileViewFinder,
    Jenssegers\Agent\Agent;

/**
 * スマホのビューに自動で切り替える
 *
 * @package App\Http\ViewCreators
 */
class ViewSwitchCreator
{
    /**
     * ビュー差し替え対象外とするフォルダ
     *
     * @var array
     */
    private $excluded_folder = ['layouts', 'shared'];

    /**
     * ビュー名の接尾語
     *
     * @var string
     */
    private $view_name_suffix = '_sp';

    /**
     * 新しいプロフィールコンポーザの生成
     */
    public function __construct()
    {
        //
    }

    /**
     * データをビューと結合
     *
     * @param  View  $view
     * @return void
     */
    public function create(View $view)
    {
        // Agentインスタンスを生成
        $agent = new Agent();

        // スマホではない場合はスキップ
        if (!$agent->isMobile()) {
            return;
        }

        // スマホ向けのビューの場合はスキップ
        $name = $view->getName();
        if ($name && preg_match('|' . $this->view_name_suffix . '$|', $name) !== 0) {
            return;
        }

        // 除外フォルダの場合はスキップ
        list($dir) = explode('.', $name);
        if (in_array($dir, $this->excluded_folder)) {
            return;
        }

        // ビューを変更する
        $name = $name . $this->view_name_suffix;

        // ビューの存在チェック
        // ※ レスポンシブ対応ページも作れるように500エラーにはしない
        if (V::exists($name)) {
            $finder = V::getFinder();
            $view->setPath(
                $finder->find(
                    ViewName::normalize($name)
                )
            );
        }
    }
}
