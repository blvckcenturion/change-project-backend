<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\User;
use Auth;


class CommentController extends Controller
{
    public function __construct() {
        $this->middleware("auth:api",["except" => ["postComment"]]);
    }

    public function postComment(Request $request, $id) {
        $result = ['success' => true];

        $user = Auth::guard("api")->user();
        if(!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        try {
            Comment::create([
                'comment' => $request->comment,
                'userId' => $user->id,
                'petitionId' => $id
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
