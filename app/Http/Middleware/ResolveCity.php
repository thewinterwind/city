<?php
namespace App\Http\Middleware;
use App\Models\City;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
class ResolveCity {
 public function handle(Request $request, Closure $next) {
  if(app()->environment('production')){
   $token=config('city.origin_token');
   abort_unless(is_string($token) && strlen($token)>30 && hash_equals($token,$request->header('X-Domain-Sales-Origin','')),403);
   // CloudFront appends the actual viewer address. Never trust viewer-supplied entries to its left.
   $addresses=explode(',',$request->header('X-Forwarded-For',''));
   $ip=trim(end($addresses));
   if(filter_var($ip,FILTER_VALIDATE_IP)){$request->server->set('REMOTE_ADDR',$ip);}
  }
  $host=strtolower($request->getHost());
  if (str_starts_with($host,'www.')) { $host=substr($host,4); }
  if (app()->environment(['local','testing']) && in_array($host,['localhost','127.0.0.1'])) { $host='bacolod.com'; }
  $city=City::where('domain',$host)->where('active',true)->firstOrFail();
  app()->instance(City::class,$city); View::share('city',$city);
  if ($request->getHost()==='www.'.$city->domain && $request->isMethod('get')) { return redirect('https://'.$city->domain.$request->getRequestUri(),301); }
  $response=$next($request);
  $response->headers->set('X-Content-Type-Options','nosniff');
  $response->headers->set('Referrer-Policy','strict-origin-when-cross-origin');
  $response->headers->set('X-Frame-Options','SAMEORIGIN');
  $response->headers->set('Cache-Control','private, no-store');
  return $response;
 }
}
