<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //
    protected $user;
    
    public function __construct(){
        $this->middleware("auth:api",["except" => ["login","register", "updateProfile", "updateProfilePassword"]]);
        $this->user = new User;
    }

    public function register(Request $request){

        $validator = Validator::make($request->all(),[
            'name' => 'required|string',
            'email' => 'required|string|unique:users',
            'password' => 'required|min:6|confirmed',
            'birthDate' => 'required|date',
        ]);

        if($validator->fails()){
            return response()->json([
            'success' => false,
            'message' => $validator->messages()
            ], 422);
        }

        $data = [
            "name" => $request->name,
            "lastName" => $request->lastName,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "birthDate" => $request->birthDate,
            "country" => $request->country,
        ];

        $this->user->create($data);

        $responseMessage = "Registration Successful";
        
        return response()->json([
            'success' => true,
            'message' => $responseMessage
        ], 200);
    }


    public function login(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => 'required|string',
            'password' => 'required|min:6',
        ]);

        if($validator->fails()){
            return response()->json([
            'success' => false,
            'message' => $validator->messages()->toArray()
            ], 500);
        }

        $credentials = $request->only(["email","password"]);

        $user = User::where('email',$credentials['email'])->first();

        if($user){
            if(!auth()->attempt($credentials)){

                $responseMessage = "Invalid username or password";
                return response()->json([
                "success" => false,
                "message" => $responseMessage,
                "error" => $responseMessage
                ], 422);
                
            }

        $accessToken = auth()->user()->createToken('authToken')->accessToken;
        $responseMessage = "Login Successful";
        return $this->respondWithToken($accessToken, $responseMessage,auth()->user());

        }
        else{
            $responseMessage = "Sorry, this user does not exist";
            return response()->json([
            "success" => false,
            "message" => $responseMessage,
            "error" => $responseMessage
            ], 422);
        }
    }

    public function viewProfile(){
        $responseMessage = "user profile";
        $data = Auth::guard("api")->user();
        return response()->json([
        "success" => true,
        "message" => $responseMessage,
        "data" => $data
        ], 200);
    }

    public function updateProfile(Request $request) {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string',
            'lastName' => 'required|string',
            'birthDate' => 'required|date',
            'country' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
            'success' => false,
            'message' => $validator->messages()
            ], 422);
        }

        $data = [
            "name" => $request->name,
            "lastName" => $request->lastName,
            "birthDate" => $request->birthDate,
            "country" => $request->country,
        ];

        $user = Auth::guard("api")->user()->update($data);
        // $user->update($data);

        $responseMessage = "User updated successfully";
        return response()->json([
            'success' => true,
            'message' => $responseMessage
        ], 200);
    }

    public function updateProfilePassword(Request $request) {
        $validator = Validator::make($request->all(),[
            'oldPassword' => 'required|min:6',
            'password' => 'required|min:6',
        ]);

        if($validator->fails()){
            return response()->json([
            'success' => false,
            'message' => $validator->messages()
            ], 422);
        }
        
        $user = Auth::guard("api")->user();

        if(!Hash::check($request->oldPassword,$user->password)){
            $responseMessage = "Old password is incorrect";
            return response()->json([
            'success' => false,
            'message' => $responseMessage
            ], 422);
        }

        $data = [
            "password" => Hash::make($request->password),
        ];

        
        $user->update($data);

        $responseMessage = "User updated successfully";
        return response()->json([
            'success' => true,
            'message' => $responseMessage
        ], 200);
    }

}
