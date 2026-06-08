<?php

declare(strict_types=1);

namespace App\Modules\User\Http\Controllers;

use App\Modules\Shared\Http\Controllers\ApiController;
use App\Modules\User\Http\Requests\UpdateProfileRequest;
use App\Modules\User\Http\Requests\UploadImageRequest;
use App\Modules\User\Http\Resources\ProfileResource;
use App\Modules\User\Http\Resources\UserResource;
use App\Modules\User\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    public function __construct(private readonly UserService $users)
    {
    }

    /** View any user's profile by username (with friendship context). */
    public function show(Request $request, string $username): JsonResponse
    {
        $user = $this->users->viewByUsername($username, $request->user());

        return $this->ok(new ProfileResource($user));
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->users->updateProfile($request->user(), $request->validated());

        return $this->ok(new ProfileResource($user), 'Profile updated.');
    }

    public function uploadAvatar(UploadImageRequest $request): JsonResponse
    {
        $user = $this->users->updateAvatar($request->user(), $request->file('image'));

        return $this->ok(new ProfileResource($user), 'Avatar updated.');
    }

    public function uploadCover(UploadImageRequest $request): JsonResponse
    {
        $user = $this->users->updateCover($request->user(), $request->file('image'));

        return $this->ok(new ProfileResource($user), 'Cover photo updated.');
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => ['required', 'string', 'min:1', 'max:100']]);

        $results = $this->users->search(
            (string) $request->string('q'),
            $request->user(),
            (int) $request->integer('per_page', 20),
        );

        return $this->ok(UserResource::collection($results));
    }

    public function deactivate(Request $request): JsonResponse
    {
        $this->users->deactivate($request->user());

        return $this->ok(message: 'Your account has been deactivated.');
    }
}
