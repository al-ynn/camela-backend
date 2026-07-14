<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return new UserResource(

            $request->user()

        );
    }

    public function update(
        UpdateProfileRequest $request
    )
    {
        $user = $request->user();

        $user->update([

            'name' => trim(

                $request->firstname.' '.$request->lastname

            ),

            'username' => $request->username,

            'email' => $request->email,

            'phone' => $request->phone,

        ]);

        $user->refresh();

        return response()->json([

            'message' => 'Profile updated successfully.',

            'user' => new UserResource($user)

        ]);
    }
}
