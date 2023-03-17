<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Image;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function addComment(Request $request, $taskId)
    {
        $validator = Validator::make($request->all(), [
            'body' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // optional picture validation
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $task = Task::findOrFail($taskId);
        $user = auth()->user();

        $comment = new Comment([
            'body' => $request->body,
            'user_id' => $user->id,
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
