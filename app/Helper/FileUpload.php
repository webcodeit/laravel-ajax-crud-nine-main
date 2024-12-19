<?php 

namespace App\Helper;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FileUpload
{

    public function uploadFile($fileReq, $storagePath, $user = null)
    {
        $user = $user ?? \Auth::user();
        $file_size = round($fileReq->getSize() / 1024, 2);
        $file_extension = strtolower($fileReq->extension());
        $file_last_modified = $fileReq->getMTime();
        $file_org_name = $fileReq->getClientOriginalName();
        $file_temp_path = $fileReq->getPathName();
        $file_new_name = app('common')->generateEcryptedFileName($fileReq->getClientOriginalExtension());

        if($file_extension == 'heif') {
            $im = new \Imagick();
            $im->setFormat(config('constants.HEIC_TO_OTHER_FORMAT'));
            $im->readImage($file_temp_path);
            $im->setImageCompressionQuality(60);
            $getImageBlob = $im->getImageBlob();
            $file_new_name = $getImageBlob;
            Storage::put($storagePath, $file_new_name);
        } else {
            $fileReq->storeAs($storagePath, $file_new_name);
        }

        return [
            'file_size' => $file_size, 'file_extension' => $file_extension, 
            'file_last_modified' => $file_last_modified, 'file_org_name' => $file_org_name, 
            'file_new_name' => $file_new_name, 'file_path' => $storagePath
        ];
    }

    public function fileDeleteFromFolder($file_path)
    {
        if(!empty($file_path))
        {
            if (Storage::exists($file_path)) {
                Storage::delete($file_path);
            }
        }
    }

    public function heicToBlob($documentFileTempPath)
    {
        $im = new \Imagick();
        $im->setFormat(config('constants.HEIC_TO_OTHER_FORMAT'));
        $im->readImage( $documentFileTempPath);
        $im->setImageCompressionQuality(60);
        return $im->getImageBlob();
    }

    public function uploadPathRename($file_path, $id) {
        return str_replace('{id}',$id, $file_path);
    }
}
?>
