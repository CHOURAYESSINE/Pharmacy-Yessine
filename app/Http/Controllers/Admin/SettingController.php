<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Support\PersistentImages;
use QCod\AppSettings\SavesSettings;
use QCod\AppSettings\Setting\AppSettings;
class SettingController extends Controller {
 use SavesSettings;
 public function store(Request $request,AppSettings $settings){
  $request->validate(['app_name'=>'required|string|max:50','app_currency'=>'required|string|max:10','logo'=>'nullable|image|mimes:png,jpg,jpeg,gif|max:500','favicon'=>'nullable|image|mimes:png,jpg,jpeg,gif|max:500']);
  $settings->set('app_name',$request->app_name);$settings->set('app_currency',$request->app_currency);
  foreach(['logo','favicon'] as $field){if($request->hasFile($field))$settings->set($field,'logos/'.PersistentImages::store($request->file($field),'logos'));}
  return back()->with('status','Settings saved.');
 }
}
