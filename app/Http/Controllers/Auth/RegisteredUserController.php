<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AdminNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'           => $request->name,
            'position'       => $request->position,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'role'           => 'user',
            'account_status' => 'pending',
        ]);

        // Force update position directly after create to bypass any fillable cache issues
        $user->position = $request->position;
        $user->save();

        // Create admin notification for new user registration
        AdminNotification::create([
            'type'       => 'new_user',
            'title'      => 'New Account Request',
            'message'    => $user->name . ' (' . $user->position . ') has requested an account.',
            'related_id' => $user->id,
            'is_read'    => false,
        ]);

        return redirect()->route('login')->with('status', 'Your account has been created and is pending admin approval. You will be notified once approved.');
    }
}
