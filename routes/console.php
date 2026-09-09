<?php
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
Artisan::command('city:admin {email}',function(){ $user=User::where('email',$this->argument('email'))->first();if(!$user){$this->error('Register this account on the site first.');return 1;}$user->forceFill(['is_admin'=>true])->save();$this->info('Administrator access granted.');})->purpose('Grant admin access to an existing, verified account (server access required).');
