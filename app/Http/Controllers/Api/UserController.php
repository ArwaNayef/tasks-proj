<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function addComment(Request $request, $taskId)
    {
        $validator = Validator::make($request->all(), [
            'body' => 'required|string|max:255',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // optional picture validation
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

        if ($request->hasFile('picture')) {
            $image = $request->file('picture');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('comments', $filename);
            $comment->picture = $path;
        }

        if ($request->input('parent_id')) {
            $parent = Comment::findOrFail($request->input('parent_id'));
            $comment->parent()->associate($parent);
        }

        $comment->save();

        return response()->json(['comment' => $comment], 201);
    }

}
