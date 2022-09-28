<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user =User::find($id);
        return view('edit',compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required',
            'password' => 'required|min:6',
            'photo'=>'required'

        ]);


        if($request->file()) {

            $fileName = time().'_'.$request->photo->getClientOriginalName();
            $request->file('photo')->storeAs('User', $fileName, 'public');
            $image = $fileName;
            $data= $request->only('name','email','phone','password');
            $data['password']= bcrypt($request->password);
            User::where('id',$id)->update($data +['photo'=>@$image]);
        }
        else{
            $data= $request->only('name','email','phone');

            User::where('id',$id)->update($data);
        }



        return redirect()->back()->with('message',"Updated Succssfully");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $basic  = new \Nexmo\Client\Credentials\Basic('a95238e3', 'dMLW7rUlvPaPtZfE');
        $client = new \Nexmo\Client($basic);
        $message = $client->message()->send([
            'to' => "+91 98828 85354",
            'from' => 'John Doe',
            'text' => 'Your Account Has been deleted'
        ]);
        User::find($id)->delete();
        return redirect()->back()->with('message',"User Deleted Succssfully");
    }
}
