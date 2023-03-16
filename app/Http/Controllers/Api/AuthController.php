<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @return User
     */
    private $sucess_status = 200;

    public function createUser(Request $request)
    {

        $validator=Validator::make($request->all(),
            [
                'name'=>'required',
                'email'=>'required|email',
                'password'=>'required|alpha_num|min:5',

            ]
        );

        if($validator->fails()) {
            return response()->json(["validation_errors" => $validator->errors()]);
        }
        $path="";
        if($request->hasfile('photo'))
        {
            $image = $request->file('photo');
            $imgname = time() + rand(1, 10000000) . '.' . $image->getClientOriginalExtension();
            $path = "uploads/images/$imgname";
            Storage::disk('public')->put($path, file_get_contents($image));
        }


        $dataArray=array(
            "name"=>$request->name,
            "email"=>$request->email,
            "password"=>bcrypt($request->password),
            "photo"=>$path,
        );

        $user=User::create($dataArray);

        if(!is_null($user)) {
            return response()->json(["status" => $this->sucess_status, "success" => true, "data" => $user]);
        }

        else {
            return response()->json(["status" => "failed", "success" => false, "message" => "Whoops! user not created. please try again."]);
        }
    }


    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function login(Request $request)
    {

        $validator=Validator::make($request->all(),
            [
                'email'=>'required|email',
                'password'=>'required|alpha_num|min:5'
            ]
        );

        if($validator->fails()) {
            return response()->json(["validation_errors" => $validator->errors()]);
        }

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user=Auth::user();
            $token=$user->createToken('auth_token')->plainTextToken;

            return response()->json(["status" => $this->sucess_status, "success" => true, "login" => true, "token" => $token, "data" => $user]);
        }
        else {
            return response()->json(["status" => "failed", "success" => false, "message" => "Whoops! invalid email or password"]);
        }
    }}
