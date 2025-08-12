<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function UserWorks(string $id): JsonResponse
    {
        $user = User::query()->findOrFail($id);
        $works = $user->workEntries()->with(['part'])->get();
        return $this->okResponse($works, "Work list");
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {

        $users = User::all();

        return $this->successResponse($users,'Users retrieved successfully');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {

        $request->validate([
            'name' => 'required',
            'phone' => 'required|unique:users',
            'password' => 'required',
            'role' => 'required|string:in,admin,user',
        ]);


        $user = User::query()->create([
            'name' => request('name'),
            'phone' => request('phone'),
            'password' => bcrypt(request('password')),
            'role' => request('role'),
        ]);

        return $this->successResponse($user,'User created successfully');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {

        $user = User::query()->findOrFail($id);

        return $this->successResponse($user,'User retrieved successfully');

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {

        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'role' => 'required|string:in,admin,user',
            'password' => 'required',
        ]);
        $user = User::query()->findOrFail($id);
        $user->update([
            'name' => request('name'),
            'phone' => request('phone'),
            'role' => request('role'),
            'password' => bcrypt(request('password')),
        ]);
        return $this->successResponse($user,'User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {

        $user = User::query()->findOrFail($id);
        $user->delete();
        return $this->successResponse($user,'User deleted successfully');
    }
}
