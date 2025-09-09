<?php

namespace App\Http\Controllers;

use App\Models\Permissions;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{

    public function listUsers()
    {
        try {
            $users = User::all();
            return response()->json([
                'success' => true,
                'message' => 'List of users',
                'data' => $users
            ], 200);
         } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to show employee!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createVerifikator(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|string|email|unique:users',
                'password' => 'required|string|min:6'
            ]);

            $verifikator = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'status_verifikasi' => 1,
                'password' => Hash::make($request->password),
                'role'     => 'verifikator'
            ]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Verifikator created successfully',
                'data'    => $verifikator
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to created Verifikator!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function promoteToVerifikator($id)
    {
        DB::beginTransaction();
        try{
            $user = User::findOrFail($id);
            if ($user->role !== 'user') {
                return response()->json(['error' => 'User is not ordinary'], 400);
            }
            $user->role = 'verifikator';
            $user->save();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'User promoted to verifikator',
                'data'    => $user
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to promote user!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function resetPassword($id)
    {
        DB::beginTransaction();
        try{
            $user = User::findOrFail($id);
            $newPassword = Str::password(12, true, true, true);

            $user->password = Hash::make($newPassword);
            $user->save();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully',
                'new_password' => $newPassword
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to reset password!',
                'error' => $e->getMessage(),
            ], 500);
        }   
    }

    public function listPermissions()
    {
        try{
            $permissions = Permissions::with('user')->get();
            return response()->json([
                'success' => true,
                'data'    => $permissions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch permissions!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
