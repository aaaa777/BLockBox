<?php
//namespace App\Http\Controllers\S3Controller;
namespace App\Http\Controllers;
use Illuminate\Http\UploadedFile;
use Storage;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    //
    public function index()
    {
        return view('upload');
    }

    public function store(Request $request){
        // $request->file('file')->store(''); // storage/appに保存
    
        // アップロードされたファイルを変数に格納
        $upload_file = $request->file('file');
 
        // ファイルがアップロードされた場合
        if(!empty($upload_file)) {
 
            // アップロード先S3フォルダ名 
            $dir = 'test';

            // Log::debug(dd(Storage::disk('s3')));    
    
            // バケット内の指定フォルダへアップロード ※putFileはLaravel側でファイル名の一意のIDを自動的に生成してくれます。
            $s3_upload = Storage::disk('s3')->putFile('/'.$dir, $upload_file);
            // Log::debug($s3_upload);
    
            // ※オプション（ファイルダウンロード、削除時に使用するS3でのファイル保存名、アップロード先のパスを取得します。）
            // アップロードファイルurlを取得
            $s3_url = Storage::disk('s3')->url($s3_upload);
            // Log::debug($s3_url);
            
            // s3_urlからS3でのファイル保存名取得
            $s3_upload_file_name = explode("/", $s3_url)[4];
    
            // アップロード先パスを取得 ※ファイルダウンロード、削除で使用します。
            $s3_path = $dir.'/'.$s3_upload_file_name;
            // Log::debug($s3_path);

            // throw new \Exception('debugger');
            return $s3_url;
        }

        // dd($request->all());
    }
}
