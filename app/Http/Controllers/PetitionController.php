<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Petition;
use App\Models\User;
use App\Models\Signed;

class PetitionController extends Controller
{
    public function getAllPetitions() {
        return Petition::all();
    }

    public function getPetition($id) {
        return Petition::find($id);
    }

    public function postPetition(Request $request) {
        $result = ['result' => 'ok'];

        try {
            Petition::create([
                'title' => $request->title,
                'directedTo' => $request->directedTo,
                'description' => $request->description,
                'goal' => $request->goal,
                'imageUri' => $request->imageUri,
                'completed' => $request->completed,
                'registerDate' => $request->registerDate,
                'status' => $request->status,
                'userId' => $request->userId
            ]);
        } catch (Exception $e) {
            $result = ['result' => 'error'];
        }

        return $result;
    }

    public function putPetition(Request $request) {
        $result = ['result' => 'ok'];

        try {
            $petition = Petition::where(["id" => $request->id])->first();
            $petition->title = $request->title;
            $petition->directedTo = $request->directedTo;
            $petition->description = $request->description;
            $petition->goal = $request->goal;
            $petition->imageUri = $request->imageUri;
            $petition->completed = $request->completed;
            $petition->registerDate = $request->registerDate;
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
