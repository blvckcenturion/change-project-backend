<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Petition;
use App\Models\User;
use App\Models\Signed;
use App\Models\Comment;
use DB;
use Auth;
use Validator;

class PetitionController extends Controller
{

    public function __construct() {
        $this->middleware("auth:api",["except" => ["getAllPetitions","getPetition","postPetition","signPetition","deletePetition","getPetitionComments","postComment","deleteComment","getUserSigned","postSigned","deleteSigned"]]);
    }

    public function getAllPetitions() {
        return Petition::where('status', 1)->get();
    }

    public function getMyPetitions() {
        $user = Auth::guard("api")->user();
        if(!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        $created_petitions = Petition::where('userId', $user->id)->get();
        $signed_petitions = [];
        $signed = Signed::where('userId', $user->id)->get();
        foreach($signed as $s) {
            $s->petition = Petition::find($s->petitionId);
            $petition_user = User::find($s->petition->userId);
            $s->petition->userName = $petition_user->name . " " . $petition_user->lastname;
            $signed_petitions[] = $s->petition;
        }

        foreach($created_petitions as $p){
            $petition_user = User::find($p->userId);
            $p->userName = $petition_user->name . " " . $petition_user->lastname;
        }

        return response()->json([
            'success' => true,
            'created_petitions' => $created_petitions,
            'signed_petitions' => $signed_petitions
        ], 200);
    }


    public function getPetition($id) {
        $petition = Petition::find($id);
        if($petition && $petition->status == 1) {
            $user = User::find($petition->userId);
            $petition->userName = $user->name . " " . $user->lastname;
            $comments = Comment::where('petitionId', $id)->get();
            foreach($comments as $comment) {
                $user = User::find($comment->userId);
                $comment->userName = $user->name . " " . $user->lastname;
            }
            $petition->comments = $comments;
            return response()->json([
                'success' => true,
                'data' => $petition
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Petition not found'
            ], 404);
        }
    }

    public function postPetition(Request $request) {
        $result = ['result' => 'ok'];
        $validator = Validator::make($request->all(),[
            'title' => 'required|string',
            'description' => 'required|string',
            'directedTo' => 'required|string',
            'goal' => 'required|integer',
            'image' => 'required|image|mimes:jpeg,png,jpg',
        ]);

        if($validator->fails()){
            return response()->json([
            'success' => false,
            'message' => $validator->messages()
            ], 422);
        }

        $user = Auth::guard("api")->user();


        try {
            // Petition::create([
            //     'title' => $request->title,
            //     'directedTo' => $request->directedTo,
            //     'description' => $request->description,
            //     'goal' => $request->goal,
            //     'userId' => $user->id,
            //     'imageUrl' => $request->imageUrl,
            // ]);

            if($request->hasFile('image')){
                $image = $request->file('image');
                $name = time() . $image->getClientOriginalName();
                $filePath = 'images/' . $name;
                Storage::disk('s3')->put($filePath, file_get_contents($image), 'public');
                $url = Storage::disk('s3')->url($filePath);
                $petition = Petition::create([
                    'title' => $request->title,
                    'directedTo' => $request->directedTo,
                    'description' => $request->description,
                    'goal' => $request->goal,
                    'userId' => $user->id,
                    'imageUrl' => $url,
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'petition created',
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'no image found.'
                ], 400);
            }
        } catch (Exception $e) {
            $result = ['success' => false, 'message' => $e->getMessage()];
            return $result;
        }
    }

    public function putPetition(Request $request, $id) {
        $result = ['success' => true];
        $validator = Validator::make($request->all(),[
            'title' => 'required|string',
            'description' => 'required|string',
            'directedTo' => 'required|string',
            'goal' => 'required|integer',
        ]);

        if($validator->fails()){
            return response()->json([
            'success' => false,
            'message' => $validator->messages()
            ], 422);
        }
        try {
            $petition = Petition::where(["id" => $id])->first();
            
            if($petition) {
                if($request->goal > $petition->signatureCount) {
                    $petition->isGoalCompleted = 0;
                }
                if($request->hasFile('image')){
                    $image = $request->file('image');
                    $name = time() . $image->getClientOriginalName();
                    $filePath = 'images/' . $name;
                    Storage::disk('s3')->put($filePath, file_get_contents($image), 'public');
                    $url = Storage::disk('s3')->url($filePath);
                    $petition->imageUrl = $url;
                    return response()->json([
                        'success' => true,
                        'message' => 'has img',
                    ], 200);
                }
                $petition->title = $request->title;
                $petition->directedTo = $request->directedTo;
                $petition->description = $request->description;
                $petition->goal = $request->goal;
                $petition->save();
                return response()->json([
                    'success' => true,
                    'message' => 'Petition updated successfully',
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Petition not found'
                ], 404);
            }
        } catch (Exception $e) {
            $result = ['success' => false];
        }

        return $result;
    }

    public function signPetition($id, Request $request) {
        $result = ['result' => 'ok'];

        try {
            DB::beginTransaction();
            $petition = Petition::where(["id" => $id])->first();
            $petition->signatureCount = $petition->signatureCount + 1;
            if($petition->signatureCount >= $petition->goal) {
                $petition->completed = true;
            }
            $petition->update();
            
            Signed::create([
                'userId' => $request->userId,
                'petitionId' => $id
            ]);

            DB::commit();
        } catch (Exception $e) {
            $result = ['result' => 'error'];
            DB::rollBack();
        }

        return $result;
    }
}
