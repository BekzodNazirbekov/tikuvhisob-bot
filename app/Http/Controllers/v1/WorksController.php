<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Part;
use App\Models\WorkEntry;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorksController extends Controller
{
    public function index(Request $request):JsonResponse
    {
//        $work = WorkEntry::query()->with(['part'])->get();
        $work = $request->user()->workEntries()->with(['part'])->get();
        return $this->okResponse($work, "Work list");
    }

    public function store(Request $request): JsonResponse
    {

        $request->validate([
            'part_id'=> 'required|exists:parts,id',
            'count' => 'required|integer|min:1',
        ]);

        $part = Part::query()->findOrFail(request('part_id'));

        if ($part->count >= request('count')) {

            $part->count -= request('count');
            $part->save();


            $work = $request->user()->workEntries()->create([
                'part_id' => $part->id,
                'quantity' => request('count'),
                'date' => Carbon::today(),
            ]);


            return $this->createdResponse($work, "Work entry created");

        }else{

            return $this->badRequestResponse();

        }

    }

    public function show(string $id):JsonResponse
    {
        $work = WorkEntry::query()->findOrFail($id);
        return $this->okResponse($work, "Work entry");
    }
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'part_id'=> 'required|exists:parts,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $part = Part::query()->findOrFail(request('part_id'));

        $work = WorkEntry::query()->findOrFail($id);

        $work->update([
            'part_id' => $part->id,
            'quantity' => request('quantity'),
            'date' => Carbon::today(),
        ]);

        return $this->okResponse($work, "Work entry updated");

    }
    public function destroy(string $id): JsonResponse
    {
        $work = WorkEntry::query()->findOrFail($id);
        $work->delete();
        return $this->noContentResponse("Work entry deleted");
    }
}
