<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Permission;
use App\Models\Permissions;

class VerifikatorController extends Controller
{
    
    public function filterUsers(Request $request)
    {
        try{
            $status = $request->input('status'); // 1 (verified) atau 0 (unverified)

            $users = User::where('role', 'user')
                ->when(isset($status), function ($query) use ($status) {
                    return $query->where('status_verifikasi', $status);
                })
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $users
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to show user!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    
    public function verifyUser(Request $request, $id)
    {
        try{
            $user = User::findOrFail($id);

            if ($user->role !== 'user') {
                return response()->json(['error' => 'Only ordinary users can be verified'], 400);
            }

            $user->status_verifikasi = $request->input('status_verifikasi') ?? 0;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User has been verified',
                'data'    => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to verified user!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function filterPermissions(Request $request)
    {
        try{
            $status = $request->input('status');

            $query = Permissions::with('user')->when(isset($status), function ($query) use ($status) {
                return $query->where('status', $status)->whereHas('user', function ($q) {
                    $q->where('role', 'user');
                });
            });

            $permissions = $query->get();

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

    public function updatePermissionStatus(Request $request, $id)
    {
        try{
            $request->validate([
                'status' => 'required|in:approved,processed,rejected',
            ]);

            $permission = Permissions::whereHas('user', function ($q) {
                $q->where('role', 'user');
            })->findOrFail($id);

            $permission->status = $request->input('status');
            $permission->komentar_verifikator = $request->input('komentar_verifikator', $permission->keterangan);
            $permission->save();

            return response()->json([
                'success' => true,
                'message' => "Permission has been {$request->status}",
                'data'    => $permission
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update permission status!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
