<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function index(){
        $title = 'register';
        return view('admin.auth.register',compact('title'));
    }

    public function store(Request $request){
        $this->validate($request ,[
            'name'=>'required|max:100',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|min:8|max:128|confirmed',
        ]);
        
        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
        ]);
        $user->assignRole('sales-person');
        auth()->attempt($request->only('email','password'));
        $request->session()->regenerate();
        return redirect()->route('dashboard');
    }
}
