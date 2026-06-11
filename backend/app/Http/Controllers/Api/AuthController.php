<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user  = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('app')->plainTextToken;

        return response()->json([
            'user'  => $this->userData($user),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('app')->plainTextToken;

        return response()->json([
            'user'  => $this->userData($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $this->userData($request->user())]);
    }

    public function orders(Request $request)
    {
        $orders = Order::where('email', $request->user()->email)
            ->with('items')
            ->latest()
            ->get()
            ->map(fn($o) => [
                'order_number'   => $o->order_number,
                'status'         => $o->status,
                'total_omr'      => $o->total_omr,
                'payment_method' => $o->payment_method,
                'created_at'     => $o->created_at,
                'items_count'    => $o->items->sum('quantity'),
            ]);

        return response()->json(['data' => $orders]);
    }

    public function lastOrder(Request $request)
    {
        $order = Order::where('email', $request->user()->email)->latest()->first();

        if (! $order) {
            return response()->json(['data' => null]);
        }

        return response()->json(['data' => [
            'first_name'  => $order->first_name,
            'last_name'   => $order->last_name,
            'phone'       => $order->phone,
            'governorate' => $order->governorate,
            'city'        => $order->city,
            'address'     => $order->address,
        ]]);
    }

    private function userData(User $user): array
    {
        return [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ];
    }
}
