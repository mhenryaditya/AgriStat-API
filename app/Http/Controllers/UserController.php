<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]); 

        if($validator -> fails()){
         return response()->json([
            'messege' => "All fields are mandetoory",
            'error' => $validator-> messages(),
         ], 422);
        }

        $validated = $validator->validated();
        $user->update([
            'name' => $request->name,
            'email'=> $request->email,
            'password' => bcrypt($validated['password']),
        ]);

        return response()->json([
            'messege'=> 'User Updated Succesfully',
            'data' => new UserResource($user)
        ], 200);
    }

    public function updateProfile(Request $request, User $user){
        $validator = Validator::make($request->all(),[
            'name'=> 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users,email,' . $user->kode_pegawai . ',kode_pegawai',
        ]); 

        if($validator -> fails()){
         return response()->json([
            'messege' => "All fields are mandetoory",
            'error' => $validator-> messages(),
         ], 422);
        }

        $user->update([
            'name' => $request->name,
            'email'=> $request->email,
        ]);

        return response()->json([
            'messege'=> 'User Updated Succesfully',
            'data' => new UserResource($user)
        ], 200);
    }

    public function updatePassword(Request $request, User $user){
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => "All fields are mandatory",
                'errors' => $validator->messages(),
            ], 422);
        }

        $validated = $validator->validated();
        $user->update([
            'password' => bcrypt($validated['password']),
        ]);

        return response()->json([
            'message' => 'Password updated successfully',
        ], 200);
    }

    public function getImageName($kodePegawai){
        $user = User::where('kode_pegawai', $kodePegawai)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        if (!$user->img_profile) {
            return response()->json(['message' => 'User has no profile image.'], 200);
        }

        $imageName = basename($user->img_profile);
        return response()->json([
            'message' => 'Profile image found.',
            'image_name' => $imageName
        ], 200);
    }
    

    public function updateImage(Request $request, User $user){
        $validator = Validator::make($request->all(), [
            'img_profile' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => "All fields are mandatory",
                'errors' => $validator->messages(),
            ], 422);
        }

        if ($request->hasFile('img_profile')) {
            if ($user->img_profile) {
                Storage::delete($user->img_profile);
            }
            $path = $request->file('img_profile')->store('profiles');
            $user->update([
                'img_profile' => $path,
            ]);

            return response()->json([
                'message' => 'Profile image updated successfully',
                'img_profile' => asset('storage/' . $path),
            ], 200);
        }

        return response()->json([
            'message' => 'No image uploaded atau gambar tidak ada',
        ], 400);
    }


    public function destroy(User $user){
        $user ->delete();
        
        return response()->json([
            'messege'=> 'User Deleted Succesfully',
        ], 200);
    }
}