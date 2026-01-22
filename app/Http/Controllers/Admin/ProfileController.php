<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\User;
use Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class ProfileController extends BaseController
{
    

    public function dashboard()
    {
        $this->setPageTitle('Dashboard', 'Manage Dashboard');
        return view('admin.dashboard.index');
    }
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $profile = User::find(Auth::user()->id);
        $this->setPageTitle('Profile', 'Manage Profile');
        return view('admin.profile.index', compact('profile'));
    }

    /**
     * @param Request $request
     */
    public function update(Request $request)
    {
        $request->validate([
            "name" => "required|string|max:200",

        ]);
        $updateRequest = $request->all();
        $id = Auth::user()->id;

        User::where('id',$id)->update(['name'=>$request->name]);
        
        Session::flash('message', 'Profile updated successfully.'); 
        return redirect()->route('admin.admin.profile');
        
    }

    /**
     * @param Request $request
     */
    public function changePassword(Request $request) {
        $request->validate([
            "current_password" => "required|string|min:6|",
            "password" => "required|string|min:6|confirmed",
            "password_confirmation" => "required|string|min:6",
        ]);
        $id = Auth::user()->id;

        if(Hash::check($request->current_password, Auth::user()->password)){            
            User::where('id',$id)->update(['password' => Hash::make($request->password)]);
            
            Session::flash('message', 'Password changed successfully.'); 
            return redirect()->route('admin.admin.profile');
        } else {
            return redirect()->back()->withErrors(['current_password'=> "Current password is wrong"])->withInput();
        }
        
    }
}
