<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tasks = $user->isAdmin() 
            ? Task::with('user')->latest()->get()
            : $user->tasks()->latest()->get();

        return response()->json($tasks);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:pending,in_progress,completed',
            'due_date' => 'required|date',
            'user_id' => $request->user()->isAdmin() ? 'required|exists:users,id' : 'prohibited',
        ]);

        if (!$request->user()->isAdmin()) {
            $validated['user_id'] = $request->user()->id;
        }

        $task = Task::create($validated);

        return response()->json($task->load('user'), 201);
    }

    public function show(Request $request, Task $task): JsonResponse
    {
        if (!Gate::allows('view', $task)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($task->load('user'));
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        try {
            if (!Gate::allows('update', $task)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'status' => 'sometimes|in:pending,in_progress,completed',
                'due_date' => 'sometimes|date',
                'user_id' => $request->user()->isAdmin() ? 'sometimes|exists:users,id' : 'prohibited',
            ]);

            $task->update($validated);

            return response()->json($task->load('user'));
        } catch (\Exception $e) {
            \Log::error('Task update failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update task: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        try {
            if (!Gate::allows('delete', $task)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            
            $task->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            \Log::error('Task deletion failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete task: ' . $e->getMessage()], 500);
        }
    }
} 