<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreateDeploymentStorage extends Migration {
 public function up(){
  Schema::create('uploaded_images',function(Blueprint $t){$t->string('path')->primary();$t->string('mime');$t->longText('content');$t->timestamp('created_at');});
  Schema::create('app_backups',function(Blueprint $t){$t->uuid('id')->primary();$t->string('name');$t->longText('content');$t->unsignedBigInteger('size');$t->timestamp('created_at');});
  Schema::create('cache',function(Blueprint $t){$t->string('key')->primary();$t->mediumText('value');$t->integer('expiration');});
 }
 public function down(){Schema::dropIfExists('cache');Schema::dropIfExists('app_backups');Schema::dropIfExists('uploaded_images');}
}
