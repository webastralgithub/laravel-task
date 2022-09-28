<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'numeric', 'digits:10','unique:users'],
            'photo' => ['required'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        if($request->file()) {

            $fileName = time() . '_' . $request->photo->getClientOriginalName();
            $filePath = $request->file('photo')->storeAs('User', $fileName, 'public');
            $image = $fileName;

        }
        else{
            $image = '';
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'phone' => $request->phone,
            'photo'=>$image,
            'password' => Hash::make($request->password),
        ]);
        if($user){
            $basic  = new \Nexmo\Client\Credentials\Basic('a95238e3', 'dMLW7rUlvPaPtZfE');
            $client = new \Nexmo\Client($basic);
            $message = $client->message()->send([
                'to' => "+91 98828 85354",
                'from' => 'John Doe',
                'text' => 'A simple hello message sent from Vonage SMS API'
            ]);
        }
        \Mail::to($user->email)->send(new \App\Mail\UserMail($user));

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
