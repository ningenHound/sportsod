<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function read(Request $request, string $id) {
        $error = $this->validateReadAndDelete($request, $id);
        if(isset($error['mensaje'])) {
            return response($error, 400)->header('Content-Type', 'application/json');
        }
        $user = User::find($id);
        if(!$user) {
            return response(['mensaje'=>'el usuario no existe'], 404)->header('Content-Type', 'application/json');
        }
        return $user;
    }

    public function create(Request $request) {
        $error = $this->validateCreate($request);
        if(isset($error['mensaje'])) {
            return response($error, 400)->header('Content-Type', 'application/json');
        }
        $userWithSameEmail = User::where('email', $request->email)->first();
        if($userWithSameEmail) {
            return response(['mensaje'=>'ya existe un usuario con el mismo email'], 400)->header('Content-Type', 'application/json');
        }
        User::create(['name' => $request->name, 'email' => $request->email, 'password' => Hash::make($request->password), 'role_id' => $request->role_id]);
        return response(['mensaje'=>'usuario creado'], 200)->header('Content-Type', 'application/json');
    }

    public function update(Request $request, string $id) {
        $updateArray = [];
        $error = $this->validateUpdate($request);
        if(isset($error['mensaje'])) {
            return response($error, 400)->header('Content-Type', 'application/json');
        }
        $user = User::find($id);
        if(!$user) {
            return response(['mensaje'=>'el usuario no existe'], 404)->header('Content-Type', 'application/json');
        }
        if(isset($request->name)) {
            $updateArray['name'] = $request->name;
        }
        if(isset($request->venue_id)) {
            $updateArray['password'] = Hash::make($request->password);
        }
        if(isset($request->venue_id)) {
            $updateArray['email'] = $request->email;
        }
        $user->update($updateArray);
        return response(['mensaje'=>'usuario actualizado'], 200)->header('Content-Type', 'application/json');
    }

    public function delete(Request $request, string $id) {
        $error = $this->validateReadAndDelete($request, $id);
        if(isset($error['mensaje'])) {
            return response($error, 400)->header('Content-Type', 'application/json');
        }
        $user = User::find($id);
        if(!$user) {
            return response(['mensaje'=>'el usuario no existe'], 404)->header('Content-Type', 'application/json');
        }
        $user->delete();
    }

    private function validateInteger($idParam) {
        $idParam = filter_var($idParam, FILTER_VALIDATE_INT);
        if(!$idParam) {
            return false;
        }
        return true;
    }

    public function validateEmail($emailParam) {
        $emailParam = filter_var($emailParam, FILTER_VALIDATE_EMAIL);
        if(!$emailParam) {
            return false;
        }
        return true;
    }

    private function validateReadAndDelete($request, $id):array {
        if(!$this->validateInteger($id)) {
            return ['mensaje'=>'el id debe ser entero'];
        }
        return [];
    }

    private function validateCreate($request):array {
        if(!isset($request->name) || !isset($request->email) || !isset($request->password) || !isset($request->role_id)) {
            return ['mensaje'=>'los campos name, email, password y role_id son obligatorios'];
        }
        if(!$this->validateInteger($request->role_id)) {
            return ['mensaje'=>'el campo role_id debe ser entero'];
        }
        if(!$this->validateEmail($request->email)) {
            return ['mensaje'=>'el campo email no es valido'];
        }
        return [];
    }

    private function validateUpdate($request):array {
        if(!$this->validateInteger($request->id)) {
            return ['mensaje'=>'el id debe ser entero'];
        }
        if(!isset($request->name) && !isset($request->email) && !isset($request->password) && !isset($request->role_id)) {
            return ['mensaje'=>'debe actualizar al menos un campo'];
        }
        if(isset($request->name) && trim($request->name) === "") {
            return ['mensaje'=>'el campo nombre no debe estar vacío'];
        }
        if(isset($request->email) && !$this->validateEmail($request->email)) {
            return ['mensaje'=>'el campo email no es valido'];
        }
        if(isset($request->role_id) && !$this->validateInteger($request->role_id)) {
            return ['mensaje'=>'el campo role_id debe ser entero'];
        }
        return [];
    }
}