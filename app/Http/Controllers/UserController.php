<?php

namespace App\Http\Controllers;

use id;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::query()->limit(100)->get();
        return view('master-data.user', [
            'users' => $users
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'name' => ['required', 'min:5', 'max:255'],
            'email' => ['required', 'min:15', 'max:255', 'email', 'unique:users'],
            'password' => ['required', 'min:3'],
            'is_admin' => ['required'],
        ]);

        // Store input data
        User::create($request->only('name', 'email', 'email-verivied_at', 'password', 'is_admin'));
        return redirect('/master-data/user');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $user->update($request->all());
        return response()->json(['message' => 'User updated successfully']);
        return redirect('/master-data/user');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }

    // public function delete(User $user)
    // {
    //     // Hapus user
    //     $user->delete();
    //     // return response()->json(['message' => 'User deleted successfully']);
    //     return redirect()->route('users')->with('success', 'User deleted successfully');
    // }

    public function delete($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return response()->json(['success' => true, 'message' => 'User deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting user'], 500);
        }
    }
}
