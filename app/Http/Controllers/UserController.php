<?php

namespace App\Http\Controllers;

use App\Models\Major;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index() 
    {
        $data = User::with(['role', 'major']) // Ambil relasi
            ->where('major_id', '!=', null) // Hanya user dengan major_id tidak null
            ->get()
            ->append('profile_image_url');

        return Inertia::render('UserManagement/Index', [
            'data' =>  $data
        ]);
    }

    public function create() 
    {
        $majors = Major::all();
        $roles = Role::where('code','!=','DEVELOPER')->get();;

        return Inertia::render('UserManagement/Create', compact('majors','roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'fullname' => 'required|string',
            'phone' => 'required|string',
            'password' => 'required|string',
            'email' => 'required|string',
            'role' => 'required|string',
            'major' => 'nullable|string',
            'avatar' => 'nullable|image'
        ]);

        DB::beginTransaction();
        try {
            $major = DB::table('major')->where('code','=',$request->major)->first();
            $role = DB::table('roles')->where('name','=',$request->role)->first();
            $imagePath = null;

            if($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $extension = $file->getClientOriginalExtension();
                $filename = 'avatar_user_'.rand(100,900).'.'.$extension;

                $imagePath = $file->storeAs('avatar', $filename,'public');
            };

            User::create([
                'username' => $request->username,
                'fullname' => $request->fullname,
                'phone' => $request->phone,
                'password' => $request->password,
                'email' => $request->email,
                'role_id' => $role->id,
                'major_id' => $major->id,
                'profile_image' => $imagePath
            ]);

            DB::commit();

            return redirect()->route('user.index')->with('success','Berhasil membuat pengguna baru!');

        } catch(\Exception $e) {
            DB::rollback();

            return back()->with('failed',$e->getMessage());
        }
    }

    public function destroy(User $user) 
    {
        DB::transaction(function() use ($user) {
            $user->delete();
        });

        return back()->with('success', 'Berhasil menghapus pengguna!');
    }

    public function edit(User $user)
    {
        $user->load(['major','role']);
        $majors = Major::all();
        $roles = Role::all();

        return Inertia::render('UserManagement/Edit', [
            'data' => $user->append('profile_image_url'),
            'majors' => $majors,
            'roles'=> $roles
        ]);
    }


    public function update(Request $request, User $user)
    {
        $request->validate([
            'username' => 'required|string',
            'fullname' => 'required|string',
            'phone' => 'required|string',
            'password' => 'nullable|string',
            'email' => 'required|string',
            'role' => 'required|string',
            'major' => 'nullable|string',
            'avatar' => 'nullable|image'
        ]);

        DB::beginTransaction();
        try {
            $major = DB::table('major')->where('code', $request->major)->first();
            $role = DB::table('roles')->where('name', $request->role)->first();
            $imagePath = $user->profile_image; 

            // Jika ada file baru diunggah
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $extension = $file->getClientOriginalExtension();
                $filename = 'avatar_user_' . rand(100, 900) . '.' . $extension;

                // Simpan file baru
                $imagePath = $file->storeAs('avatar', $filename, 'public');

                // Hapus avatar lama jika ada
                if ($user->profile_image) {
                    Storage::disk('public')->delete($user->profile_image);
                }
            }

            // Update data user
            $user->update([
                'username' => $request->username,
                'fullname' => $request->fullname,
                'phone' => $request->phone,
                'password' => $request->password ? bcrypt($request->password) : $user->password,
                'email' => $request->email,
                'role_id' => $role->id,
                'major_id' => $major->id,
                'profile_image' => $imagePath
            ]);

            DB::commit();

            return redirect()->route('user.index')->with('success', 'Berhasil memperbarui pengguna!');

        } catch (\Exception $e) {
            DB::rollback();

            return back()->with('failed', $e->getMessage());
        }
    }

}
