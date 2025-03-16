<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function get(){
        $pageLength = request('pageLength', 10); 
        $users = User::paginate($pageLength);

        if ($users->isEmpty()) {
            return response()->json([
                'status' => 'empty',
                'message' => 'No users found',
                'data' => [],
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ]
            ], 200);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'User list retrieved successfully',
            'data' => UserResource::collection($users), 
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ]
        ], 200);
    }

    public function update(Request $request, User $user){
        $validator = Validator::make($request->all(),[
            'name'=> 'required|string|max:255',
            'email'=> 'required|string|max:255|unique:users,email',
        ]); 

        if($validator -> fails()){
         return response()->json([
            'messege' => "All fields are mandetoory",
            'error' => $validator -> messages(),
         ], 422);
        }

        $user->update([
            'name' => $request ->name,
            'email'=> $request ->email,
        ]);

        return response()->json([
            'messege'=> 'User Updated Succesfully',
            'data' => new UserResource($user)
        ], 200);
    }

    public function destroy(User $user){
        $user ->delete();
        
        return response()->json([
            'messege'=> 'User Deleted Succesfully',
        ], 200);
    }
}