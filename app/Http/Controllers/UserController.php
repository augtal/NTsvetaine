<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Models\User;
use App\Models\LikedAdvertisements;
use App\Models\Advertisement;
use App\Models\Notification;
use App\Models\NotificationAdvertisements;
use App\Models\UserMessages;

class UserController extends Controller
{
    public function showProfilePage(){
        return view('user.profile');
    }

    public function showEditPage(){
        return view('user.profileEdit');
    }

    public function showLikedAdsPage(){
        $likedAds = LikedAdvertisements::where('user_id', auth()->user()->id)->get()->toArray();

        $data = Array();
        foreach($likedAds as $adInfo){
            $data[] = Advertisement::where('id', $adInfo['advertisement_id'])->with('getLastestPrice', 'getCategory', 'getType', 'getWebsite')->first();
        }

        return view('user.favoriteListings')->with('data', $data);
    }

    public function showUserList(){
        $users = User::get();

        return view('user.usersList')->with('users', $users);
    }

    public function changeUserRole($id){
        $user = User::where('id', $id)->first();

        if($user->role == 1){
            $user->role = 73;
            $user->save();
        }
        elseif($user->role == 73){
            $user->role = 1;
            $user->save();
        }

        return redirect()->back();
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
                    return redirect()->to('/')->with('success', "Slaptažodis sekmingai pakeistas.");
                }
                else
                {
                    return back()->withError('Dabartinis slaptažodis neteisingas.');
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
            'password.required' => 'Įveskite dabartinį slaptažodi.',
            'new_password.required' => 'Įveskite naują slaptažodi.',
            'new_password.min' => 'Naujas slaptažodis turi buti bent 8 simbolių.',
            'new_password-confirmation.required' => 'Įveskite pakartoti naują slaptažodi.',
            'new_password-confirmation.same' => 'Naujas slaptažodis ir pakartoti naują slaptažodį nesutampa',
        ];

        $validator = Validator::make($data, [
            'password' => 'required',
            'new_password' => ['required', 'min:8'],
            'new_password-confirmation' => ['required', 'same:new_password'],
        ], $messages);

        return $validator;
    }

    

    public function markAllMessagesRead(){
        $messages = UserMessages::where('user_id', auth()->user()->id)->where('read_msg', 0)->get();

        foreach($messages as $msg){
            $msg->read_msg = 1;
            $msg->save();
        }

        $messages = UserMessages::where('user_id', auth()->user()->id)->orderBy('updated_at', 'desc')->get()->toArray();

        session()->put('messages', $messages);
        session()->put('unreadMsgCnt', 0);

        return redirect()->back();
    }

    public function markMessageRead($messageID){
        $message = UserMessages::find($messageID);
        $message->read_msg = 1;
        $message->save();

        $messages = UserMessages::where('user_id', auth()->user()->id)->orderBy('updated_at', 'desc')->get()->toArray();

        $msgCount = session()->get('unreadMsgCnt') - 1;
        if($msgCount < 0) $msgCount = 0;

        session()->put('messages', $messages);
        session()->put('unreadMsgCnt', $msgCount);

        return redirect('/notification/'. $message['notification_id']);
    }

    public function deleteUser($id){
        $user = User::find($id);

        $notificationsList = Notification::where('user_id', $user->id)->get();
        
        foreach($notificationsList as $notification){
            NotificationAdvertisements::where('notification_id', $notification->id)->delete();
            $notification->delete();
        }

        $user->delete();

        return redirect()->back();
    }
}
