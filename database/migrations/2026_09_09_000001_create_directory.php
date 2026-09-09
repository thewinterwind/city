<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('cities',function(Blueprint $t){$t->id();$t->string('name');$t->string('slug')->unique();$t->string('domain')->unique();$t->string('tagline');$t->text('intro');$t->boolean('active')->default(false);$t->timestamps();});
  Schema::create('categories',function(Blueprint $t){$t->id();$t->string('name');$t->string('slug')->unique();$t->string('description');$t->string('color',7);$t->unsignedInteger('position');$t->timestamps();});
  Schema::create('users',function(Blueprint $t){$t->id();$t->string('name');$t->string('email')->unique();$t->string('password');$t->boolean('is_admin')->default(false);$t->rememberToken();$t->timestamps();});
  Schema::create('listings',function(Blueprint $t){$t->id();$t->foreignId('city_id')->constrained();$t->foreignId('category_id')->constrained();$t->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();$t->string('slug');$t->string('name');$t->string('kind')->default('business');$t->string('status')->default('pending');$t->text('description');$t->string('address');$t->string('area');$t->string('website',1000)->nullable();$t->string('phone',50)->nullable();$t->unsignedInteger('featured_position')->nullable();$t->string('photo_path')->nullable();$t->string('photo_credit')->nullable();$t->json('tags');$t->string('source_url',1000)->nullable();$t->date('source_checked_at')->nullable();$t->timestamp('published_at')->nullable();$t->timestamps();$t->unique(['city_id','slug']);$t->index(['city_id','status','category_id']);});
  Schema::create('submissions',function(Blueprint $t){$t->id();$t->foreignId('city_id')->constrained();$t->foreignId('listing_id')->nullable()->constrained()->nullOnDelete();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->string('type');$t->string('status')->default('pending');$t->json('payload');$t->text('review_note')->nullable();$t->foreignId('reviewed_by')->nullable()->constrained('users');$t->timestamps();$t->index(['city_id','status']);});
  Schema::create('audit_events',function(Blueprint $t){$t->id();$t->foreignId('city_id')->constrained();$t->foreignId('user_id')->constrained();$t->string('action');$t->unsignedBigInteger('subject_id');$t->json('details');$t->timestamps();});
 }
 public function down():void {foreach(['audit_events','submissions','listings','users','categories','cities'] as $table){Schema::dropIfExists($table);}}
};
