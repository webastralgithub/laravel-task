<?php

namespace App\Http\Controllers;

use JWTAuth;
use Auth;
use Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\GroupSchedule;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SchedulesExport;
use DB;
use Carbon\Carbon;
use api;
use App\Models\UserType;
use App\Models\TrainerClient;
use Twilio\Rest\Client;
class ApiController extends Controller
{
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'photo' => 'required'

        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            $message = [
                'message' => $validator->errors()->first()
            ];
            return response()->json($message,500);
        }

        //Request is valid, create new user
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
                'image'=>$image,
                'password' => bcrypt($request->password),

            ]);
            if($user){
                \Mail::to($user->email)->send(new \App\Mail\UserMail($user));
                $basic  = new \Nexmo\Client\Credentials\Basic('a95238e3', 'dMLW7rUlvPaPtZfE');
                $client = new \Nexmo\Client($basic);
                $message = $client->message()->send([
                    'to' => '+91 98828 85354',
                    'from' => $user->name,
                    'text' => 'Your Account has been created'
                ]);


            }
            return $this->authenticate($request);

    }


    public function update(Request $request,$id){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required',
            'password' => 'required|min:6',
            'photo'=>'required'

        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            $message = [
                'message' => $validator->errors()->first()
            ];
            return response()->json($message,500);
        }

        if($request->file()) {

            $fileName = time().'_'.$request->photo->getClientOriginalName();
        $request->file('photo')->storeAs('User', $fileName, 'public');
            $image = $fileName;

        }
        $data= $request->only('name','email','phone','password');
        $data['password']= bcrypt($request->password);
        $image =User::where('id',$id)->update($data +['photo'=>@$image]);
        return response()->json([
            'success' => true,
            'message' => 'Update Successfully'
        ], Response::HTTP_OK);
    }

    public function ResetPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'password' => ['required'],
            'email' => ['required'],

        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            $message = [
                'message' => $validator->errors()->first()
            ];
            return response()->json($message,500);
        }

        $user=User::where('email',$request->email)->first();

        if (isset($user)) {
            $user-> update(['password'=>bcrypt($request->password)]);

            return response()->json([
                'status'=>True,
                "message" => 'Updated Successfully'
            ]);
        }
        else{
            return response()->json([
                'status'=>True,
                "message" => 'Invalid Email '
            ]);

        }

    }

    public function change_password(Request $request){

        $validator = Validator::make($request->all(), [
            'currentpassword' => ['required'],

        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            $message = [
                'message' => $validator->errors()->first()
            ];
            return response()->json($message,500);
        }
        if (Hash::check($request->currentpassword, Auth::user()->password)) {
            User::whereId(Auth::user()->id)->update(['password'=>bcrypt($request->password)]);
            return response()->json([
                'status'=>True,
                "message" => 'Updated Successfully'
            ]);
        }
        else{
            return response()->json([
                'success' => false,
                'message' => 'Current Password Does Not Match',
            ], 500);
        }

    }

    public function authenticate(Request $request)
    {

        $credentials = $request->only('email', 'password');

        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is validated
        //Crean token
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Login credentials are invalid.',

                ], 400);
            }
        } catch (JWTException $e) {
            return $credentials;

        }

        //Token created, return with success response and jwt token
        return response()->json([
            'success' => true,
            'token' => $token,

            'user_details'=>Auth::user()
        ]);
    }

    public function logout(Request $request)
    {
        //valid credential
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is validated, do logout
        try {
            JWTAuth::invalidate($request->token);

            return response()->json([
                'success' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function forgot(Request $request)
    {


        $date = Carbon::now();
        $date=strtotime($date);
        $futureDate = $date+(60*5);
        $expiry=date("Y-m-d H:i:s", $futureDate);
        $credentials = request()->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        //  if (isset($user)) {
        // $user = Password::sendResetLink(
        //       $request->only('email')
        //      );

        if (isset($user)) {
            $verification_code = Str::random(8);

            $user->verification_code=$verification_code;
            $user->code_expiry=$expiry;
            $user->save();
            //Password::sendResetLink($credentials);
            //\Mail::to($request->email)->send(new sendResetLink($credentials));
            \Mail::to($user->email)->send(new \App\Mail\VerificationCode($user));
            return response()->json([
                'status'=>true,
                "message" => 'Please check mail for verification code'
            ]);

        }

        else{
            return response()->json([
                'status'=>false,
                "message" => 'Email Id is not Exist '
            ]);
        }
    }


    public function get_user(Request $request)
    {
        //$user =User::get();
        $user = JWTAuth::authenticate($request->bearerToken());
        $appdetails =array();
        return response()->json([
            'user' => $user,
            'appDetails'=>$appdetails
        ]);
    }

    public function destroy($id)
    {
       if(User::find($id)->exists()){
           $basic  = new \Nexmo\Client\Credentials\Basic('a95238e3', 'dMLW7rUlvPaPtZfE');
           $client = new \Nexmo\Client($basic);
           $message = $client->message()->send([
               'to' => "+91 98828 85354",
               'from' => 'John Doe',
               'text' => 'Your account Deleted'
           ]);
           User::find($id)->delete();
       }

        return response()->json([
            'success' => true,
            'message' => 'Deleted Successfully'
        ], Response::HTTP_OK);
    }



}
