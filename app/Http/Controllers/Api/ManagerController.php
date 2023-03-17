<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Image;
use App\Models\Manager;
use App\Models\Task;
use App\Models\User;
use Cassandra\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ManagerController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:managers',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $manager = Manager::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $manager->createToken('manager')->plainTextToken;
        $path="";
        if($request->hasfile('photo')) {
            $image = $request->file('photo');
            $imgname = time() + rand(1, 10000000) . '.' . $image->getClientOriginalExtension();
            $path = "uploads/images/$imgname";
            Storage::disk('public')->put($path, file_get_contents($image));
        }
        if($path!=""){
            $picture = new Image();
            $picture->image=$path;
            $manager->images()->save($picture);
        }
        return response()->json(['token' => $token], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $manager = Manager::where('email', $request->email)->first();

        if (!$manager || !Hash::check($request->password, $manager->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $manager->createToken('manager')->plainTextToken;

        return response()->json(['token' => $token], 200);
    }
    public function addManagerComment(Request $request, Task $task)
    {
        $validator = Validator::make($request->all(), [
            'body' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // optional picture validation
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $task = Task::findOrFail($task);
        $user = auth()->user();

        $comment = new Comment([
            'body' => $request->body,
            'user_id' => $managerId = auth('manager')->id(),
            'task_id' => $task->id,
        ]);

        $comment->save();

        $path="";
        if($request->hasfile('photo')) {
            $image = $request->file('photo');
            $imgname = time() + rand(1, 10000000) . '.' . $image->getClientOriginalExtension();
            $path = "uploads/images/$imgname";
            Storage::disk('public')->put($path, file_get_contents($image));
        }
        if($path!=""){
            $picture = new Image();
            $picture->image=$path;
            $comment->images()->save($picture);
        }
        return response()->json(['comment' => $comment], 201);
    }

}
