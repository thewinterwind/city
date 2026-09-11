<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::table('listings', function(Blueprint $t) { $t->json('details')->nullable(); $t->json('gallery')->nullable(); }); }
 public function down(): void { Schema::table('listings', function(Blueprint $t) { $t->dropColumn(['details','gallery']); }); }
};
