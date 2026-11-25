<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Van;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index() {
        $pendingUsers = User::where('account_status','pending')->latest()->get();
        $allUsers = User::where('role','user')->latest()->get();
        $drivers = User::where('role','driver')->latest()->get();
        $vans = Van::with('driver')->orderBy('van_plate')->get();
        return view('admin.users.index', compact('pendingUsers','allUsers','drivers','vans'));
    }
    public function approve(User $user) {
        $user->update(['account_status' => 'approved']);
        return redirect()->back()->with('success', 'User approved!');
    }
    public function reject(User $user) {
        $user->update(['account_status' => 'rejected']);
        return redirect()->back()->with('success', 'User rejected!');
    }
    public function destroy(User $user) {
        $user->delete();
        return redirect()->back()->with('success', 'User deleted!');
    }
    public function createDriver(Request $request) {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'position' => 'nullable|string|max:100',
        ]);
        User::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => bcrypt($request->password),
            'position'       => $request->position ?? 'Van Driver',
            'role'           => 'driver',
            'account_status' => 'approved',
        ]);
        return redirect()->back()->with('success', 'Driver account created successfully!');
    }
    public function assignVan(Request $request, User $driver) {
        $request->validate(['van_id' => 'nullable|exists:vans,id']);
        // Remove driver from previous van
        Van::where('driver_id', $driver->id)->update(['driver_id' => null]);
        // Assign to new van
        if ($request->van_id) {
            Van::where('id', $request->van_id)->update(['driver_id' => $driver->id]);
        }
        return redirect()->back()->with('success', 'Van assigned to driver successfully!');
    }
}
