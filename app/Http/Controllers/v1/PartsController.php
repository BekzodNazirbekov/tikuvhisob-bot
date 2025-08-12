<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Model;
use App\Models\Part;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartsController extends Controller
{

    public function index()
    {

        $parts = Part::query()->with(['model'])->get();
        return $this->okResponse($parts,"Parts list");

    }

    public function show(string $id) : JsonResponse
    {

        $part = Part::query()->with(['model'])->findOrFail($id);
        return $this->okResponse($part,"Part show");

    }

    public function store(Request $request): JsonResponse
    {

        request()->validate([
            'name' => 'required|string',
            'model_id' => 'required',
            'price' => 'required|numeric|min:0',
            'count' => 'required|numeric|min:0',
        ]);

        $model = Model::query()->findOrFail(request('model_id'));

        $part = Part::query()->create([
            'name' => request('name'),
            'model_id' => request('model_id'),
            'price' => request('price'),
            'count' => request('count'),
        ]);

        return $this->okResponse($part,"Part store");

    }

    public function update(Request $request, string $id): JsonResponse
    {
        request()->validate([
            'name' => 'required|string',
            'price' => 'required|numeric|min:0',
            'count' => 'required|numeric|min:0',
        ]);
        $part = Part::query()->findOrFail($id);

        $part->update([
            'name' => request('name'),
            'price' => request('price'),
            'count' => request('count'),
        ]);

        return $this->okResponse($part,"Part update");
    }

    public function destroy(string $id): JsonResponse
    {

        $part = Part::query()->findOrFail($id);

        $part->delete();

        return $this->noContentResponse("Part deleted");

    }
}
