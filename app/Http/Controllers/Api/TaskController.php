<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        // Handle the photo upload, if provided
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $filename = time() . '_' . $photo->getClientOriginalName();
            $photo->storeAs('public/tasks', $filename);
            $task->photo = $filename;
        }

        // Save the task
        $task->save();

        // Return a response with the created task
        return response()->json([
            'message' => 'Task created successfully.',
            'task' => $task
        ], 201);
    }

    public function showTasks(Request $request)
    {
        // Get the authenticated user
        $user = auth()->user();

        // Retrieve all tasks associated with the user
        $tasks = Task::where('user_id', $user->id)->get();

        // Return a response with the tasks
        return response()->json([
            'tasks' => $tasks
        ], 200);
    }
    public function showTask(Request $request, $id)
    {
        // Get the authenticated user
        $user = auth()->user();

        // Retrieve the task with the specified ID that belongs to the user
        $task = Task::where('user_id', $user->id)->where('id', $id)->first();

        // Check if the task exists
        if (!$task) {
            return response()->json([
                'message' => 'Task not found'
            ], 404);
        }

        // Return a response with the task details
        return response()->json([
            'task' => $task
        ], 200);
    }
}
