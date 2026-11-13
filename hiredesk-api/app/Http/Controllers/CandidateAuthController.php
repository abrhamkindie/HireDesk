<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginCandidateRequest;
use App\Http\Requests\RegisterCandidateRequest;
use App\Http\Resources\CandidateResource;
use App\Models\Candidate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class CandidateAuthController extends Controller
{
    public function register(RegisterCandidateRequest $request): JsonResponse
    {
        $candidate = Candidate::create($request->safe()->except('device_name'));

        $token = $candidate->createToken($this->deviceName($request))->plainTextToken;

        return response()->json([
            'candidate' => new CandidateResource($candidate),
            'token' => $token,
        ], 201);
    }

    public function login(LoginCandidateRequest $request): JsonResponse
    {
        $candidate = Candidate::query()->where('email', $request->input('email'))->first();

        if (! $candidate || ! Hash::check($request->input('password'), $candidate->password)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        $token = $candidate->createToken($this->deviceName($request))->plainTextToken;

        return response()->json([
            'candidate' => new CandidateResource($candidate->load('profile')),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        abort_unless($request->user() instanceof Candidate, 403);

        $token = $request->user()->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json(null, 204);
    }

    private function deviceName(Request $request): string
    {
        return (string) ($request->input('device_name') ?: 'api');
    }
}
