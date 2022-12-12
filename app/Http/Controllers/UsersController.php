<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuthService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UsersController extends Controller
{
    public function store(Request $request)
    {
        // Returns error if invalid.
        $validated = $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'firstName' => 'required',
            'lastName' => 'required',
        ]);

        $user = User::create([
            'email' => $validated['email'],
            'name' => $validated['firstName'] . ' ' . $validated['lastName'],
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.auth.token')
            ])->post(config('services.auth.base_url').'/accounts', [
                'user_id' => $user['id'],
                'email' => $validated['email'],
                'password' => $validated['password']
            ]);
        } catch (Exception $ex) {
            Log::warning($ex->getMessage());
            return $this->failedCreatingUserInAuthService($user, $response);
        }

        if($response->status() != 201) {
            return $this->failedCreatingUserInAuthService($user, $response);
        }

        return response(["status" => "success", "user" => $user], 201);
    }

    /**
     * Loggs failed creation and removes user from database.
     */
    private function failedCreatingUserInAuthService($user, $response)
    {
        Log::warning("Failed creating user", [
            "user" => $user,
            "response" => $response
        ]);
        // Deleteing user for data integrity reasons. There shouldn't exist a user in users service but not in auth service.
        $user->delete();
        return response(["status" => "error", "error" => "Failed creating user"], 500);
    }

    public function index(Request $request)
    {
        return User::all(['id', 'name', 'avatar', 'created_at'])->map(function ($user) {
            // Created_at will always be a Carbon object, need to format it before returning, thereby the change in parameter name.
            $user->join_date = substr($user->created_at, 0,10);
            return $user;
        });
    }

    /**
     * Returns infomration about user by email
     */
    public function show_by_email(Request $request, $email)
    {
        /** @var \Openwod\ServiceAccounts\Models\ServiceAccount $svc */
        $svc = auth()->guard('svc')->user();
        // Check if request was authenticated by service account and check service account permission
        if($svc != null && $svc->tokenCan('users.view.users')) {
            return User::where('email', $email)->first();
        }
        // If request wasn't authorized, only show specific values.
        return User::where('email', $email)->first(['id', 'name', 'avatar', 'created_at']);
    }

    /**
     * Returns infomration about user by id
     */
    public function show_by_id(Request $request, $id)
    {
        /** @var \Openwod\ServiceAccounts\Models\ServiceAccount $svc */
        $svc = auth()->guard('svc')->user();
        // Check if request was authenticated by service account and check service account permission
        if($svc != null && $svc->tokenCan('users.view.users')) {
            return User::where('id', $id)->first();
        }
        // If request wasn't authorized, only show specific values.
        return User::where('id', $id)->first(['id', 'name', 'avatar', 'created_at']);
    }
}
