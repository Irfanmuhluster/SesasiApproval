<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\Permissions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    public function store(Request $request)
    {
        try{
            $request->validate([
                'judul' => 'required|string|max:255',
                'isi'   => 'required|string',
                'detail'=> 'nullable|string',
            ]);

            $izin = Permissions::create([
                'user_id' => auth()->id(),
                'judul'   => $request->judul,
                'isi'     => $request->isi,
                'detail'  => $request->detail,
                'status'  => 'submitted'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Permission has been successfully submitted',
                'data'    => $izin
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to submit permission!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function index()
    {
        $izin = Permissions::where('user_id', auth()->id())->get();

        return response()->json([
            'success' => true,
            'data'    => $izin
        ], 200);
    }

    public function show($id)
    {
        $izin = Permissions::where('user_id', auth()->id())->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $izin
        ], 200);
    }


    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try{
            $izin = Permissions::where('user_id', auth()->id())->findOrFail($id);

            if ($izin->status !== 'submitted') {
                return response()->json(['error' => 'Permission cannot be changed once processed.'], 400);
            }
            $request->validate([
                'judul' => 'sometimes|required|string|max:255',
                'isi'   => 'sometimes|required|string',
                'detail'=> 'nullable|string',
            ]);

            $izin->judul = $request->input('judul', $izin->judul);
            $izin->isi = $request->input('isi', $izin->isi);
            $izin->detail = $request->input('detail', $izin->detail);
            $izin->status = 'revised';
            $izin->save();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Permission successfully updated',
                'data'    => $izin
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update permission!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cancel($id)
    {
        $izin = Permissions::where('user_id', auth()->id())->findOrFail($id);

        if ($izin->status !== 'submitted') {
            return response()->json(['error' => 'Only permits that are still being submitted for can be canceled.'], 400);
        }

        $izin->status = 'canceled';
        $izin->save();

        return response()->json([
            'success' => true,
            'message' => 'Permission has been successfully canceled',
            'data'    => $izin
        ], 200);
    }

    public function destroy($id)
    {
        $izin = Permissions::where('user_id', auth()->id())->findOrFail($id);
        $izin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permission successfully deleted'
        ], 200);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6'
        ]);

        $user = auth()->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['error' => 'Old password is incorrect'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password has been successfully updated'
        ], 200);
    }
}
