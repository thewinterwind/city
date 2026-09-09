<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
class AppServiceProvider extends ServiceProvider {
 public function boot():void {
  RateLimiter::for('auth',fn($r)=>Limit::perMinute(6)->by($r->ip()));
  RateLimiter::for('submissions',fn($r)=>Limit::perHour(12)->by(($r->user()?->id ?? $r->ip()).':'.$r->getHost()));
 }
}
