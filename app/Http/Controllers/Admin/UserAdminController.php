<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserAdminController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $users = User::paginate(20);

        if ($request->has('search')) {
            $search = $request->search;
            $users = $users->where('name', 'like', '%' . $search . '%')
                           ->orWhere('email', 'like', '%' . $search . '%');
        }

        return view('admin.user.index', compact('users'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create(User $user)
    {
        return view('admin.user.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();

        // 1. Tentukan id_role yang diizinkan (1 = User, 2 = Admin)
        $allowedRoles = [1, 2];

        // Jika yang login adalah Super Admin, izinkan id_role 3 (Super Admin)
        if ($currentUser->isSuperAdmin()) {
            $allowedRoles[] = 3;
        }

        // 2. Validasi input
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'id_role' => ['required', 'integer', Rule::in($allowedRoles)],
        ]);

        // cegah admin menambah super admin
        if ($currentUser->isAdmin() && $validatedData['id_role'] === 3) {
            return redirect()->route('admin.user.index')
                ->with('error', 'Anda tidak memiliki izin untuk menambah akun Super Admin.');
        }

        // 3. Simpan data
        User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'id_role' => $validatedData['id_role'],
        ]);

        return redirect()->route('admin.user.index')
            ->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    /**
     * Show user detail
     */
    public function show(User $user)
    {
        return view('admin.user.show', compact('user'));
    }

    /**
     * Show the form for editing user
     */
    public function edit(User $user)
    {
        return view('admin.user.edit', compact('user'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $currentUser = Auth::user();

        // 1. Tentukan id_role yang diizinkan (1 = User, 2 = Admin)
        $allowedRoles = [1, 2];

        // Jika yang login adalah Super Admin, izinkan id_role 3 (Super Admin)
        if ($currentUser->isSuperAdmin()) {
            $allowedRoles[] = 3;
        }

        // 2. Validate the request, ensuring the role is within the allowed list
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'id_role' => ['required', 'integer', Rule::in($allowedRoles)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // 3. Prevent an Admin from modifying a Super Admin's account
        if ($currentUser->isAdmin() && $user->isSuperAdmin()) {
            return redirect()->route('admin.user.index')
                ->with('error', 'Anda tidak memiliki izin untuk mengubah data Super Admin.');
        }

        // 4. Prevent a user from removing their own admin privileges (optional, but recommended)
        if ($currentUser->id === $user->id && $validatedData['id_role'] === 1) {
            return back()->withErrors(['id_role' => 'Anda tidak dapat menurunkan role akun Anda sendiri.']);
        }

        // 5. Update the user record
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->id_role = $validatedData['id_role'];

        if (!empty($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }

        $user->save();

        return redirect()->route('admin.user.show', $user)
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        $currentUser = Auth::user();

        // Proteksi 1: Cegah user menghapus akunnya sendiri yang sedang dipakai login
        if ($currentUser->id === $user->id) {
            return redirect()->route('admin.user.index')
                ->with('error', 'Tindakan ditolak! Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Proteksi 2: Cegah Admin biasa menghapus akun Super Admin
        if ($currentUser->isAdmin() && $user->isSuperAdmin()) {
            return redirect()->route('admin.user.index')
                ->with('error', 'Akses ditolak! Anda tidak memiliki izin untuk menghapus akun Super Admin.');
        }

        // Jika aman, lanjutkan penghapusan
        $user->delete();

        return redirect()->route('admin.user.index')
            ->with('success', 'Data pengguna berhasil dihapus.');
    }
}
