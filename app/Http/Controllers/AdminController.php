<?php
namespace App\Http\Controllers;
use App\Models\{City,Submission,Listing};
use App\Services\ReviewSubmission;
use Illuminate\Http\Request;
class AdminController {
 public function index(City $city){$submissions=Submission::where('city_id',$city->id)->where('status','pending')->with(['listing','user'])->oldest()->paginate(20);$listings=Listing::forCity($city)->with('category')->orderBy('name')->get();return view('admin',compact('submissions','listings'));}
 public function review(Request $r,City $city,int $id,ReviewSubmission $review){$data=$r->validate(['decision'=>'required|in:approve,reject','note'=>'nullable|string|max:1500','ownership_verified'=>'nullable|accepted']);$submission=Submission::where('city_id',$city->id)->findOrFail($id);if($submission->type==='claim' && $data['decision']==='approve'){$r->validate(['ownership_verified'=>'accepted']);}if($submission->type==='correction' && $data['decision']==='approve'){$r->validate(['note'=>'required|string|min:10|max:1500']);}$review->handle($submission,$r->user(),$data['decision']==='approve',$data['note']??null);return back()->with('success','Request reviewed.');}
 public function archive(Request $r,City $city,int $id){$listing=Listing::forCity($city)->findOrFail($id);$listing->update(['status'=>$listing->status==='published'?'archived':'published']);return back()->with('success','Listing visibility updated.');}
}
