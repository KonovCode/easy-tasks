<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreRequest;
use App\Http\Resources\TaskResource;

class TaskController extends Controller
{
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $tasks = auth()->user()->tasks()->with('category')->latest()->paginate(20);

        return TaskResource::collection($tasks);
    }

    public function show(int $id): TaskResource
    {
        $task = auth()->user()->tasks()->with('category')->findOrFail($id);

        return new TaskResource($task);
    }

    public function store(StoreRequest $request): TaskResource
    {
        $task = auth()->user()->tasks()->create($request->validated());
        $task->load('category');

        return new TaskResource($task);
    }

    public function update(StoreRequest $request, int $id): TaskResource
    {
        $task = auth()->user()->tasks()->findOrFail($id);

        $task->update($request->validated());
        $task->load('category');

        return new TaskResource($task);
    }

    public function destroy(int $id): \Illuminate\Http\JsonResponse
    {
        $task = auth()->user()->tasks()->findOrFail($id);

        $task->delete();

        return response()->json(null, 204);
    }
}
