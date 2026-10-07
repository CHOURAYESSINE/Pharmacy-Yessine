<?php
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
define('LARAVEL_START', microtime(true));
if (getenv('VERCEL')) {
    $runtimeStorage = sys_get_temp_dir().'/pharmacy';
    foreach (['framework/views','framework/cache/data','framework/sessions','logs','app','bootstrap/cache'] as $directory) {
        if (!is_dir($runtimeStorage.'/'.$directory)) mkdir($runtimeStorage.'/'.$directory,0775,true);
    }
    foreach (['APP_PACKAGES_CACHE'=>'packages.php','APP_SERVICES_CACHE'=>'services.php','APP_CONFIG_CACHE'=>'config.php','APP_ROUTES_CACHE'=>'routes.php','APP_EVENTS_CACHE'=>'events.php'] as $variable=>$file) putenv($variable.'='.$runtimeStorage.'/bootstrap/cache/'.$file);
    putenv('VIEW_COMPILED_PATH='.$runtimeStorage.'/framework/views');
    foreach (['LOG_CHANNEL'=>'stderr','APP_ENV'=>'production','APP_DEBUG'=>'false','SESSION_DRIVER'=>'cookie','SESSION_SECURE_COOKIE'=>'true','CACHE_DRIVER'=>'database','QUEUE_CONNECTION'=>'sync','DB_CONNECTION'=>'pgsql','DB_SCHEMA'=>'pharmacy','DB_SSLMODE'=>'require','MAIL_MAILER'=>'log','BROADCAST_DRIVER'=>'log'] as $key=>$value) {
        if (getenv($key)===false) putenv($key.'='.$value);
    }
    $_SERVER['HTTPS']='on'; $_SERVER['SERVER_PORT']='443';
}
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
if (getenv('VERCEL')) $app->useStoragePath($runtimeStorage);
$kernel=$app->make(Kernel::class);
$request=Request::capture();
$response=$kernel->handle($request);$response->send();$kernel->terminate($request,$response);
