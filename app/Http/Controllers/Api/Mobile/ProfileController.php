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
            'fullname' => 'nullable|string|min:3|max:255',
            'admin_user_category_id' =>['nullable','integer',
            Rule::exists('admin_user_categories', 'id'),
            ],
            'address' => 'nullable|string',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if($request->input('admin_user_category_id')){
            $user->admin_user_category_id = $request->input('admin_user_category_id');
        }
        $user->fullname = $request->input('fullname');
        $user->address = $request->input('address');

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
