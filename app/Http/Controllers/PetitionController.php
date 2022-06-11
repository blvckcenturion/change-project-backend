<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Petition;
use App\Models\User;
use App\Models\Signed;
use DB;
use Auth;
use Validator;

class PetitionController extends Controller
{

    public function __construct() {
        $this->middleware("auth:api",["except" => ["getAllPetitions","getPetition","postPetition","signPetition","deletePetition","getPetitionComments","postComment","deleteComment","getUserSigned","postSigned","deleteSigned"]]);
    }

    public function getAllPetitions() {
        return Petition::all();
    }

    public function getPetition($id) {
        return Petition::find($id);
    }

    public function postPetition(Request $request) {
        $result = ['result' => 'ok'];
        $validator = Validator::make($request->all(),[
            'title' => 'required|string',
            'description' => 'required|string',
            'directedTo' => 'required|string',
            'goal' => 'required|integer',
            'imageUrl' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
            'success' => false,
            'message' => $validator->messages()
            ], 422);
        }

        $user = Auth::guard("api")->user();


        try {
            Petition::create([
                'title' => $request->title,
                'directedTo' => $request->directedTo,
                'description' => $request->description,
                'goal' => $request->goal,
                'userId' => $user->id,
                'imageUrl' => $request->imageUrl,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Petition created successfully'
            ], 200);
        } catch (Exception $e) {
            $result = ['success' => false, 'message' => $e->getMessage()];
            return $result;
        }
    }

    public function putPetition(Request $request) {
        $result = ['result' => 'ok'];

        try {
            $petition = Petition::where(["id" => $request->id])->first();
            $petition->title = $request->title;
            $petition->directedTo = $request->directedTo;
            $petition->description = $request->description;
            $petition->goal = $request->goal;
            $petition->imageUrl = $request->imageUrl;
            $petition->update();
        } catch (Exception $e) {
            $result = ['result' => 'error'];
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

    public function deletePetition($id) {
        $result = ['result' => 'ok'];

        try {
            $petition = Petition::find($id);
            $petition->delete();
        } catch (Exception $e) {
            $result = ['result' => 'error'];
        }

        return $result;
    }
}
