<?php

namespace App\Http\Controllers\Api\Mobile;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProfileController extends Controller
{
    public function Profile()
    {
        $user = Auth::user();
        $user->increment('views');
        return response()->json([
            'status' => true,
            'user' => $user
        ]);
    }

    public function ProfileUpdate(Request $request)
    {
        $user = Auth::user();

        // Validate the profile photo file
        $request->validate([
            'name' => [
                'sometimes', // Add this to only validate if the field is present in the request
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'avatar' => 'nullable|image|max:2048',
            'username' => [
                'sometimes', // Add this to only validate if the field is present in the request
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('users')->ignore(Auth::user()->id),
            ],
        ]);

        // $user->name = $request->input('name');
        if ($request->input('name')) {
            $user->name = $request->input('name');
        }
        if ($request->input('username')) {
            $user->username = $request->input('username');
        }

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = Str::random(20).'.'.$avatar->getClientOriginalExtension();

            // Delete old avatar if it exists
            if ($user->avatar) {
                Cloudinary::destroy($user->avatar);
            }

            // Upload avatar to Cloudinary
            $uploadedFileUrl = Cloudinary::upload($avatar->getRealPath())->getSecurePath();

            $user->avatar = $uploadedFileUrl;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user
        ], 200);
    }


}
