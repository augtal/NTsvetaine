<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


use App\Models\User;
use App\Models\LikedAdvertisements;

class UserController extends Controller
{
    public function showProfilePage(){
        return view('user.profile');
    }

    public function showEditPage(){
        return view('user.profileEdit');
    }

    public function showLikedAdsPage(){
        $data = null;
        return view('user.likedAds')->with('data', $data);
    }
    

    public function changePassword(Request $request){
        if(Auth::Check())
        {
            $requestData = $request->All();
            $validator = $this->validatePasswords($requestData);
            if($validator->fails())
            {
                return back()->withErrors($validator->getMessageBag());
            }
            else
            {
                $currentPassword = Auth::User()->password;
                if(Hash::check($requestData['password'], $currentPassword))
                {
                    $userId = Auth::User()->id;
                    $user = User::find($userId);
                    $user->password = Hash::make($requestData['new_password']);;
                    $user->save();
                    return back()->with('message', 'Your password has been updated successfully.');
                }
                else
                {
                    return back()->withErrors(['Sorry, your current password was not recognised. Please try again.']);
                }
            }
        }
        else
        {
            // Auth check failed - redirect to domain root
            return redirect()->to('/');
        }
    }

    private function validatePasswords(array $data)
    {
        $messages = [
            'password.required' => 'Please enter your current password',
            'new_password.required' => 'Please enter a new password',
            'new_password_confirmation.not_in' => 'Sorry, common passwords are not allowed. Please try a different new password.'
        ];

        $validator = Validator::make($data, [
            'password' => 'required',
            'new_password' => ['required', 'same:new_password', 'min:8'],
            'new_password_confirmation' => 'required|same:new_password',
        ], $messages);

        return $validator;
    }
}
