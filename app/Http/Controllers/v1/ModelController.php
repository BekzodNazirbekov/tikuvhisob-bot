<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class ModelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {

        $model = Model::query()->with(['parts'])->get();
        return $this->successResponse($model, 'Model list');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255|unique:models,name',
        ]);

       $model = Model::query()->create([
            'name' => request('name'),
        ]);

        return $this->createdResponse($model, 'Model created');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $model = Model::query()->with(['parts'])->findOrFail($id);
        return $this->successResponse($model, 'Model detail');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('models', 'name')->ignore($id),
            ],
        ]);

        $model = Model::query()->findOrFail($id);

        $model->update([
            'name' => request('name'),
        ]);

        return $this->okResponse($model, 'Model updated');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Model $model): JsonResponse
    {
        try {
            $model->delete();

            // note: noContentResponse returns 204 in our base controller.
            // if you prefer to return a body use okResponse(...).
            return $this->noContentResponse('Model deleted');
        } catch (Throwable $e) {
            // optionally log: \Log::error($e);
            return $this->serverErrorResponse('Failed to delete model');
        }
    }
}
