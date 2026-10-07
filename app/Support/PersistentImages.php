<?php
namespace App\Support;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class PersistentImages {
    public static function store(UploadedFile $file,string $folder):string {
        $name=(string)Str::uuid().'.'.$file->extension();
        DB::table('uploaded_images')->insert(['path'=>$folder.'/'.$name,'mime'=>$file->getMimeType(),'content'=>base64_encode(file_get_contents($file->getRealPath())),'created_at'=>now()]);
        return $name;
    }
}
