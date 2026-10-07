<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
class BackupController extends Controller {
 public function index(){
  $backups=DB::table('app_backups')->orderByDesc('created_at')->get()->map(fn($b)=>['file_path'=>$b->id,'file_name'=>$b->id,'file_size'=>$b->size,'last_modified'=>strtotime($b->created_at),'disk'=>'database','download'=>true]);
  return view('admin.backup',['title'=>'backups','backups'=>$backups]);
 }
 public function create(){
  $schema=config('database.connections.'.config('database.default').'.schema','public');
  $tables=DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema=? AND table_type='BASE TABLE'",[$schema]);
  $data=['format'=>'pharmacy-data-v1','created_at'=>now()->toIso8601String(),'tables'=>[]];
  foreach($tables as $table){if(!in_array($table->table_name,['app_backups','cache']))$data['tables'][$table->table_name]=DB::table($table->table_name)->get()->toArray();}
  $temp=tempnam(sys_get_temp_dir(),'pharmacy-backup-');$zip=new \ZipArchive();
  try {
   abort_unless($zip->open($temp,\ZipArchive::OVERWRITE)===true,500);
   $zip->addFromString('pharmacy-data.json',json_encode($data,JSON_THROW_ON_ERROR));$zip->close();
   $bytes=file_get_contents($temp);abort_if(strlen($bytes)>3*1024*1024,422,'Backup exceeds the 3 MB download limit. Use a database export for larger data.');
   DB::table('app_backups')->insert(['id'=>(string)Str::uuid(),'name'=>'pharmacy-'.now()->format('Y-m-d-His').'.zip','content'=>Crypt::encryptString(base64_encode($bytes)),'size'=>strlen($bytes),'created_at'=>now()]);
  }finally{if(is_file($temp))unlink($temp);}
  return back()->with(notify('Backup created successfully.'));
 }
 public function download(Request $request){$b=DB::table('app_backups')->where('id',$request->file_name)->first();abort_unless($b,404);return response(base64_decode(Crypt::decryptString($b->content)))->header('Content-Type','application/zip')->header('Content-Disposition','attachment; filename="'.$b->name.'"')->header('Cache-Control','private,no-store');}
 public function destroy($file_name){DB::table('app_backups')->where('id',$file_name)->delete();return back()->with(notify('Backup deleted.'));}
}
