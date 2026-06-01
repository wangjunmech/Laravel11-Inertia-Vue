<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class EmailVerificationController extends Controller
{
    //email verification notice
    public function notice(){
        return Inertia::render('Auth/VerifyEmail',[
            'status' => session('status'),
        ]);  
    }   
    
    public function handler(EmailVerificationRequest $request){
        $request->fulfill(); 
        return redirect()->route('home');
    }
    public function resend(Request $request){
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'Verification link sent!');
    }
}
