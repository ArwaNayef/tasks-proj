<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ManagerController extends Controller
{
    public function register(Request $request)
    {
        // Validate the request data
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
        // Create the new manager user
        $dataArray=array(
            "name"=>$request->name,
            "email"=>$request->email,
            "password"=>bcrypt($request->password),
        );

        $user=User::create($dataArray);

        // Assign the manager role to the new user
        $user->assignRole('manager');
        // Generate an access token for the new user
        $accessToken = $user->createToken('authToken')->accessToken;

        // Return a response with the new user and access token
        return response(['user' => $user, 'access_token' => $accessToken]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            if (Auth::user()->hasRole('manager')) {
                $token = Auth::user()->createToken('manager')->accessToken;
                return response()->json(['token' => $token], 200);
            } else {
                Auth::logout();
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } else {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    }
    public function addComment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string',
            'image' => 'nullable|image|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $task = Task::findOrFail($id);
        $user = Auth::user();

        $comment = new Comment();
        $comment->comment = $request->comment;
        $comment->user_id = $user->id;
        $comment->task_id = $task->id;

        // Check if an image was uploaded
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('comments', $image, $filename);
            $comment->image = $filename;
        }

        $comment->save();

        return response()->json(['message' => 'Comment added successfully.']);
    }

}
