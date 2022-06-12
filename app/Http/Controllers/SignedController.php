<?php

namespace App\Http\Controllers;
use App\Models\Signed;
use Illuminate\Http\Request;
use Auth;
use DB;
use App\Models\Petition;

class SignedController extends Controller
{
    public function __construct() {
        $this->middleware("auth:api",["except" => ["getUserSigned","postSigned"]]);
    }

    public function getUserSigned($id) {
        $user = Auth::guard("api")->user();
        if(!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        $signed = Signed::where('userId', $user->id)->where('petitionId', $id)->first();
        if($signed) {
            return response()->json([
                'success' => true,
                'signed' => true
            ], 200);
        } else {
            return response()->json([
                'success' => true,
                'signed' => false
            ], 200);
        }
    }

    public function postSigned($id) {
        $user = Auth::guard("api")->user();
        if(!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        $signed = Signed::where('userId', $user->id)->where('petitionId', $id)->first();
        if(!$signed) {
            try{
                DB::beginTransaction();
                $signed = new Signed;
                $signed->userId = $user->id;
                $signed->petitionId = $id;
                $signed->save();

                $petition = Petition::find($id);
                $petition->signatureCount = $petition->signatureCount + 1;
                if($petition->signatureCount >= $petition->goal) {
                    $petition->status = 1;
                }
                $petition->save();

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Signed successfully'
                ], 200);
            } catch(Exception $e) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Error signing'
                ], 500);
            }
        }
    }
}
