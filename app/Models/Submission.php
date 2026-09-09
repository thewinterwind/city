<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Submission extends Model {protected $guarded=[]; protected function casts():array{return ['payload'=>'array'];} public function listing(){return $this->belongsTo(Listing::class);} public function user(){return $this->belongsTo(User::class);} }
