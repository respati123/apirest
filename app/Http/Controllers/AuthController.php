<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Transformers\UserTransformer;
use Auth;

class AuthController extends Controller
{
    public function register(Request $request, User $user){



      $this->validate($request, [

        'name'      => 'required',
        'email'     => 'required|email|max:50|unique:users',
        'password'  => 'required|min:6'

      ]);

      $users = $user->create([
        'name'      => $request->name,
        'email'     => $request->email,
        'password'  => bcrypt($request->password),
        'token_api' => bcrypt($request->email),
      ]);

      $response = fractal()
            ->item($users)
            ->transformWith(new UserTransformer)
            ->addMeta([
              'token' => $users->api_token
            ])
            ->toArray();

      return response()->json($response, 201);
    }

    public function login(Request $request, User $user){

      $this->validate($request, [

          'email'    => 'required|email',
          'password' => 'required|min:6'
      ]);

      if(!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {

          return response()->json(['error' => 'your crendentials is wrong'], 401);
      }

      $user = $user->find(Auth::user()->id);

      $response  = fractal()
              ->item($user)
              ->transformWith(new UserTransformer)
              ->addMeta([
                'token' => $user->api_token
              ])
              ->toArray();
      return response()->json($response, 201);

    }
}
