<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,Hash};
use Illuminate\Validation\Rules\Password;
class AuthController {
 public function login(Request $r){$data=$r->validate(['email'=>'required|email','password'=>'required|string']);if(!Auth::attempt($data)){return back()->withErrors(['email'=>'The email or password did not match.'])->withInput($r->only('email'));}$r->session()->regenerate();return redirect()->intended('/dashboard');}
 public function register(Request $r){$data=$r->validate(['name'=>'required|string|max:100','email'=>'required|email|max:254|unique:users','password'=>['required','confirmed',Password::min(12)],'accept'=>'accepted','company_website'=>'prohibited']);$user=User::create(collect($data)->only(['name','email','password'])->all());Auth::login($user);$r->session()->regenerate();return redirect('/dashboard')->with('success','Your account is ready. Add your business or request ownership of an existing listing.');}
 public function logout(Request $r){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect('/');}
 public function password(Request $r){$data=$r->validate(['current_password'=>'required|current_password','password'=>['required','confirmed',Password::min(12)]]);$r->user()->update(['password'=>$data['password']]);$r->session()->regenerate();return back()->with('success','Password updated.');}
}
