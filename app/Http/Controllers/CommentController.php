<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\User;

class CommentController extends Controller
{
    public function getPetitionComments($id) {
        return Comment::where(["petitionId" => $id])->get();
    }

    public function postComment(Request $request) {
        $result = ['result' => 'ok'];

        try {
            Comment::create([
                'message' => $request->message,
                'registerDate' => $request->registerDate,
                'userId' => $request->userId,
                'petitionId' => $request->petitionId
            ]);
        } catch (Exception $e) {
            $result = ['result' => 'error'];
        }

        return $result;
    }

    public function deleteComment($id) {
        $result = ['result' => 'ok'];

        try {
            $comment = Comment::find($id);
            $comment->delete();
        } catch (Exception $e) {
            $result = ['result' => 'error'];
        }

        return $result;
    }
}
