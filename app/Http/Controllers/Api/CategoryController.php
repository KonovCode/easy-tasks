<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $categories = auth()->user()->categories;

        return CategoryResource::collection($categories);
    }

    public function show(int $id): CategoryResource|JsonResponse
    {
        $category = auth()->user()->categories()->findOrFail($id);

        return new CategoryResource($category);
    }

    public function store(StoreRequest $request): CategoryResource|JsonResponse
    {
        $category = auth()->user()->categories()->create($request->validated());

        return new CategoryResource($category);
    }

    public function update(StoreRequest $request, int $id): CategoryResource|JsonResponse
    {
        $category = auth()->user()->categories()->findOrFail($id);

        $category->update($request->validated());

        return new CategoryResource($category);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = auth()->user()->categories()->findOrFail($id);

        $category->delete();

        return response()->json(null, 204);
    }
}
