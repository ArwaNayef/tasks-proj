<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{

    private $sucess_status = 200;

    // --------------- [ Create Task / Update Task ] ------------------
    public function createTask(Request $request) {
        // Validate the request data
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'schedule_date' => 'required|date',
            'is_completed' => 'required|boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Get the authenticated user
        $user = auth()->user();

        // Create the task
        $task = new Task([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'schedule_date' => $validatedData['schedule_date'],
            'is_completed' => $validatedData['is_completed'],
            'user_id' => $user->id,
        ]);
        // Save the task
        $task->save();

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
            $task->images()->save($picture);
        }

        // Return a response with the created task
        return response()->json([
            'message' => 'Task created successfully.',
            'task' => $task
        ], 201);
    }

    public function showTask(Request $request, $id)
    {
        $task = Task::with('commentsWithReplies')->find($id);

        // Return a response with the task details
        return response()->json([
            'task' => $task
        ], 200);
    }
}
