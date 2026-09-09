<?php
namespace App\Http\Middleware;
use Closure;
class RequireAdmin { public function handle($request, Closure $next){abort_unless($request->user()?->is_admin,403); return $next($request);} }
