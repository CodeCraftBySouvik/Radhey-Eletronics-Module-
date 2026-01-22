<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    public function index()
    {
        return view('admin.auth.login');
    }
    public function login(Request $request)
    {
    	$validator = Validator::make($request->all(), [
			'mobile' => 'required|digits_between:8,11',
			'password' => 'required|string'
		], [
			'mobile.required' => 'The mobile number is required.',
			'mobile.digits_between' => 'The mobile number must be between 8 and 11 digits.',
			'password.required' => 'The password is required.',
			'password.string' => 'The password must be a valid string.'
		]);

    // Check if validation fails
    if ($validator->fails()) {
        return redirect()->route('admin.login')
            ->withErrors($validator)
            ->withInput($request->all());
    }
        $adminCreds = $request->only('mobile', 'password');
        $checkAdmin = \App\User::where('mobile',$request->mobile)->first();

        if(!empty($checkAdmin)){
            if(!empty($checkAdmin->status)){    
                if(!in_array($checkAdmin->designation,[1])){
                    if ( Auth::guard('web')->attempt($adminCreds) ) {
                        //  dd(Auth::guard('web')->attempt($adminCreds));
                        return redirect()->route('admin.home');
                    } else {
                        return redirect()->route('admin.login')->withInputs($request->all())->with('failure', 'Invalid credentials. Try again');
                    }
                } else {
                    return redirect()->route('admin.login')->withInputs($request->all())->with('failure', 'You have no access to login here');
                }
                
            }else{
                return redirect()->route('admin.login')->withInputs($request->all())->with('failure', 'Inactive user');
            }
        }else{
            return redirect()->route('admin.login')->withInputs($request->all())->with('failure', 'No user found');
        }

    }

    public function adminLogout()
    {
        Auth::logout();
        // Auth::guard('admin')->logout();
        return redirect(route('admin.login'));
    }
}
