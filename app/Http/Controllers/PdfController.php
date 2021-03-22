<?php

namespace App\Http\Controllers;

class PdfController extends Controller
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

    public function index()
    {
        $pdf = \PDF::view('pdf.pdf_tamplate')->setOption('encoding', 'utf-8');
        return $pdf->inline('thisis.pdf');  //ブラウザ上で開ける
        // return $pdf->download('thisis.pdf'); //こっちにすると直接ダウンロード
    }

}
