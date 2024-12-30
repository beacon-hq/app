<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class AccessTokenController extends Controller
{
    protected $tenant;

    public function __construct()
    {
        $this->tenant = Tenant::findOrFail(App::context()->tenant->id);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $name = $request->post('name');
        $token = $this->tenant->createToken($name);

        return response()->json([
            'id' => Str::before($token->plainTextToken, '|'),
            'name' => $name,
            'token' => Str::after($token->plainTextToken, '|'),
            'last_used_at' => null,
            'created_at' => $token->accessToken->created_at,
        ]);
    }

    public function show(): JsonResponse
    {
        return response()->json($this->tenant->tokens->map(fn (PersonalAccessToken $token) => [
            'id' => $token->id,
            'name' => $token->name,
            'token' => Str::of($token->token)->substr(-5)->prepend('*********************'),
            'last_used_at' => $token->last_used_at,
            'created_at' => $token->created_at,
        ]));
    }

    public function destroy(string $id): Response
    {
        $this->tenant->tokens()->where('id', $id)->delete();

        return response(null, 204);
    }
}
