<?php
namespace App\Services;
use App\Models\{Listing,Submission,User};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class ReviewSubmission {
 public function handle(Submission $submission,User $admin,bool $approve,?string $note):void {
  abort_unless($admin->is_admin,403);
  DB::transaction(function()use($submission,$admin,$approve,$note){
   $s=Submission::lockForUpdate()->findOrFail($submission->id);abort_unless($s->status==='pending',409);
   $listing=$s->listing_id ? Listing::where('city_id',$s->city_id)->lockForUpdate()->findOrFail($s->listing_id):null;
   if($approve){
    if($s->type==='claim'){abort_unless($listing && $listing->kind==='business' && !$listing->owner_id && $s->user_id,409);$listing->update(['owner_id'=>$s->user_id]);}
    elseif(in_array($s->type,['new','edit'])){
     $fields=collect($s->payload)->only(['name','category_id','description','address','area','website','phone','tags','photo_path','photo_credit'])->all();
     if($s->type==='new'){$slug=Str::slug($fields['name']) ?: 'place';$base=$slug;$n=2;while(Listing::where('city_id',$s->city_id)->where('slug',$slug)->exists()){$slug=$base.'-'.$n++;}$listing=Listing::create($fields+['city_id'=>$s->city_id,'owner_id'=>$s->user_id,'slug'=>$slug,'kind'=>'business','status'=>'published','published_at'=>now()]);$s->listing_id=$listing->id;}
     else{abort_unless($listing && ($listing->owner_id===$s->user_id || $s->user?->is_admin),409);$listing->update($fields);}
    }
    // Corrections are acknowledged only after the reviewer has made the corresponding listing edit.
   }
   $s->update(['status'=>$approve?'approved':'rejected','review_note'=>$note,'reviewed_by'=>$admin->id]);
   DB::table('audit_events')->insert(['city_id'=>$s->city_id,'user_id'=>$admin->id,'action'=>$approve?'submission.approved':'submission.rejected','subject_id'=>$s->id,'details'=>json_encode(['type'=>$s->type,'listing_id'=>$s->listing_id]),'created_at'=>now(),'updated_at'=>now()]);
  });
 }
}
