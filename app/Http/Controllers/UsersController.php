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
}
