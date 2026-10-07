<?php
namespace App\Http\Middleware;
use Closure;
class PharmacyAccess {
 public function handle($request,Closure $next){
  $user=$request->user();$route=$request->route();$name=$route?$route->getName():null;
  if($user && $request->is('settings')) abort_unless($user->hasPermissionTo('view-settings'),403);
  if(!$user||!$name) return $next($request);
  if(in_array($name,['profile.update','update-password'])) abort_unless((int)$route->parameter('user')->id===(int)$user->id,403);
  $group=explode('.',$name)[0];
  $map=['users'=>['user','view-users'],'categories'=>['category','view-category'],'suppliers'=>['supplier','view-supplier'],'purchases'=>['purchase','view-purchase'],'products'=>['product','view-products'],'sales'=>['sale','view-sales'],'roles'=>['role','view-role'],'permissions'=>['permission','view-permission']];
  $permission=null;
  if(isset($map[$group])){
   [$singular,$view]=$map[$group];$action=explode('.',$name)[1]??'index';
   $permission=['index'=>$view,'create'=>'create-'.$singular,'store'=>'create-'.$singular,'edit'=>'edit-'.$singular,'update'=>'edit-'.$singular,'destroy'=>'destroy-'.$singular,'report'=>'view-reports'][$action]??$view;
  }
  if($group==='backup')$permission='backup-app';if($name==='settings')$permission='view-settings';if($name==='expired')$permission='view-expired-products';if($name==='outstock')$permission='view-outstock-products';
  if($permission)abort_unless($user->hasPermissionTo($permission),403);
  return $next($request);
 }
}
