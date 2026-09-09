<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::table('listings',function(Blueprint $t){
   $t->unsignedInteger('editorial_rank')->nullable();
   $t->string('summary',280)->nullable();
   $t->string('editorial_photo')->nullable();
   $t->string('editorial_photo_credit')->nullable();
   $t->string('editorial_photo_source_url',1000)->nullable();
   $t->string('editorial_photo_license')->nullable();
   $t->index(['city_id','category_id','editorial_rank']);
  });
 }
 public function down():void {
  Schema::table('listings',function(Blueprint $t){
   $t->dropIndex(['city_id','category_id','editorial_rank']);
   $t->dropColumn(['editorial_rank','summary','editorial_photo','editorial_photo_credit','editorial_photo_source_url','editorial_photo_license']);
  });
 }
};
