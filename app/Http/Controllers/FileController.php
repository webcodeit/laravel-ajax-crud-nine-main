<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function __destruct()
    {
        $this->middleware('auth');
    }

    public function secureImage($path)
    {
        $imagePath = Crypt::decryptString($path);

        if (Storage::exists($imagePath)) {
            $fileName = basename($imagePath);  
            $mimeType = Storage::mimeType($imagePath);
            $file =  Storage::get($imagePath);
            $fileName = basename($imagePath);  
            $headers = [
              'Content-Type' => $mimeType, 
              'Content-Description' => 'File Transfer',
              'Content-Disposition' => "attachment; filename={$fileName}",
              'filename'=> $fileName
           ];
      
            return response($file, 200, $headers);
            // $headers = [
            //     'Content-Type' => $mimeType,
            // ];

            //return response()->file($imagePath, $headers);
        } else {
            abort(404, 'File not found!');
        }
    }

    public function securePdf($path)
    {
        $pdfFile = Crypt::decryptString($path);
        //$pdfFile = storage_path('app/' . $path);

        if (Storage::exists($pdfFile)) { // Checking if file exist
            $fileName = basename($pdfFile);  
            $mimeType = Storage::mimeType($pdfFile);
            $file =  Storage::get($pdfFile);
            $fileName = basename($pdfFile);  
            $headers = [
              'Content-Type' => $mimeType, 
              'Content-Description' => 'File Transfer',
              'Content-Disposition' => "attachment; filename={$fileName}",
              'filename'=> $fileName
           ];
      
            return response($file, 200, $headers);
            // $headers = [
            //     'Content-Type' => 'application/pdf',
            // ];

            // return response()->download($pdfFile, 'download', $headers, 'inline');
        } else {
            abort(404, 'File not found!');//terminate 
        }
    }

    // Route::get('file/{fileName}', 'FileController@getFileFromRoot')->where('filename', '^[^/]+$');
    public function getFileFromRoot($fileName)
    {
        return response()->download(storage_path($fileName), null, [], null);
    }
  
    public function secureFile($path, $file_delete_after_download = true)
    {
        $path = Crypt::decryptString($path);
        $excelFile = storage_path('app/' . $path);

        if (file_exists($excelFile)) {
            $headers = [
                'Content-Type' => 'text/csv',
            ];
            
            return response()->download(storage_path('app/' . $path), null, [], null)->deleteFileAfterSend($file_delete_after_download);
        } else {
            abort(404, 'File not found!');//terminate 
        }
    }
    
    
}
