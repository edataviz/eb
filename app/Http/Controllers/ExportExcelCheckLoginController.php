<?php
/**
 * Created by PhpStorm.
 * User: MyPC
 * Date: 26/07/2018
 * Time: 5:56 PM
 */

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class ExportExcelCheckLoginController extends EBController
{
    public function View(Request $request){
        $postData = $request->all();
        $table = $postData['table'];
        unset($postData['table']);
        $key = $postData['key'];
        $this->middleware('auth:api');
        unset($postData['key']);
                $datas = \DB::table($table)->where($postData)->get();

        return view('excel_export.export',['datas'=>$datas]);
    }
}