<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SendEmail;
use App\Jobs\SendMessegePhone;
use App\Mail\SendMessages;
use App\Models\User;
use App\Models\Verify;
use Http;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Mail;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        $code = rand(1000, 9999);

        $data = Verify::create([
            'user_id' => $user->id,
            'code' => $code,
        ]);

        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE3MzQwOTM5NzQsImlhdCI6MTczMTUwMTk3NCwicm9sZSI6InRlc3QiLCJzaWduIjoiMTQwOWVhMmQyMmIxOWIyOGI2NmQxMzA4NDQ1YWIzZmUxNGMxZTA5NDI5NTljZWQ2MmM1M2YyYjA4ZDc1ZTc4MyIsInN1YiI6Ijg5MjMifQ.F2yuAtzCPjqClMUVoSkCJ1H7z6EZysn8lkVL66MEnHs';
        $data1 = [
            'mobile_phone' => $user->phone,
            'message' => 'Bu Eskiz dan test',
            'from' => 4546,
            'callback_url' => 'http://127.0.0.1:8000/',
        ];

        Auth::login($user);
        SendMessegePhone::dispatch($token, $data1);

        return redirect(route('registerVerify'));
    }

    public function verifyPage()
    {
        return view('auth.registerVerify');
    }

    public function verify(Request $request)
    {
        $user = User::findOrFail(auth()->user()->id);
        $verify = Verify::where('user_id', $user->id)->first();

        $code = $request->code;

        if ($code == $verify->code) {
            $date = now();
            $verify_email = ['email_verified_at' => $date];
            $user->update($verify_email);
            return redirect('/');
        } else {
            return back();
        }
    }
}
