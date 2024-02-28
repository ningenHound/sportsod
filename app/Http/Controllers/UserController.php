<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Helpers\JWTHelper;

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
        $errorAuth = $this->validateAuth($request);
        if(isset($errorAuth['mensaje'])) {
            return response($errorAuth, 401)->header('Content-Type', 'application/json');
        }
        if(isset($error['mensaje'])) {
            return response($error, 400)->header('Content-Type', 'application/json');
        }
        $token = $request->header('Authorization');
        $role_id = JWTHelper::getClaim($token);
        if(Role::find($role_id)->description != "SYSTEM_ADMIN") {
            return response(['mensaje'=>'no autorizado'], 403)->header('Content-Type', 'application/json');
        }
        $userWithSameEmail = User::where('email', $request->email)->first();
        if($userWithSameEmail) {
            return response(['mensaje'=>'ya existe un usuario con el mismo email'], 400)->header('Content-Type', 'application/json');
        }
        if(!$request->role_id) {
            $request->role_id = $this->getRoleId('PLAYER');
        }
        User::create(['name' => $request->name, 'email' => $request->email, 'password' => Hash::make($request->password), 'role_id' => $request->role_id, 'enabled' => true]);
        return response(['mensaje'=>'usuario creado'], 201)->header('Content-Type', 'application/json');
    }

    public function update(Request $request, string $id) {
        $updateArray = [];
        $errorAuth = $this->validateAuth($request);
        if(isset($errorAuth['mensaje'])) {
            return response($error, 401)->header('Content-Type', 'application/json');
        }
        $error = $this->validateUpdate($request);
        if(isset($error['mensaje'])) {
            return response($error, 400)->header('Content-Type', 'application/json');
        }
        $token = $request->header('Authorization');
        $role_id = JWTHelper::getClaim($token);
        $user_id = JWTHelper::getClaim($token, 'user_id');
        $userRole = Role::find($role_id);
        // not allowed if an user wich is not the owner of the bearer token and not having the role SYSTEM_ADMIN tries to update
        if($user_id != $id && $userRole->description != "SYSTEM_ADMIN") {
            return response(['mensaje'=>'no autorizado'], 403)->header('Content-Type', 'application/json');
        }
        $user = User::find($id);
        if(!$user) {
            return response(['mensaje'=>'el usuario no existe, no hay nada que actualizar'], 404)->header('Content-Type', 'application/json');
        }
        $updateArray['name'] = trim($request->name);
        $updateArray['password'] = Hash::make(trim($request->password));
        if(isset($request->role_id)) {
            $updateArray['role_id'] = $request->role_id;
        }
        if(isset($request->enabled) && $userRole->description === "SYSTEM_ADMIN") {
            $updateArray['enabled'] = $request->enabled;
        }
        $user->update($updateArray);
        return response(['mensaje'=>'usuario actualizado'], 200)->header('Content-Type', 'application/json');
    }

    public function delete(Request $request, string $id) {
        $error = $this->validateReadAndDelete($request, $id);
        $errorAuth = $this->validateAuth($request);
        if(isset($errorAuth['mensaje'])) {
            return response($error, 401)->header('Content-Type', 'application/json');
        }
        if(isset($error['mensaje'])) {
            return response($error, 400)->header('Content-Type', 'application/json');
        }
        $user = User::find($id);
        if(!$user) {
            return response(['mensaje'=>'el usuario no existe, no hay nada que borrar'], 404)->header('Content-Type', 'application/json');
        }
        $token = $request->header('Authorization');
        $role_id = JWTHelper::getClaim($token);
        $user_id = JWTHelper::getClaim($token, 'user_id');
        $userRole = Role::find($role_id);
        // not allowed if an user which is not the owner of the bearer token and not having the role SYSTEM_ADMIN tries to update
        if($user_id != $id && $userRole->description != "SYSTEM_ADMIN") {
            return response(['mensaje'=>'no autorizado'], 403)->header('Content-Type', 'application/json');
        }
        $user->delete();
        return response(['mensaje'=>'usuario eliminado'], 200)->header('Content-Type', 'application/json');
    }

    public function login(Request $request) {
        $error = $this->validateLogin($request);
        if(isset($error['mensaje'])) {
            return response($error, 400)->header('Content-Type', 'application/json');
        }
        $user = User::where('email', $request->email)->first();
        if(!$user) {
            return response(['mensaje'=> 'usuario no registrado'], 401)->header('Content-Type', 'application/json');
        }
        if(!$user->enabled) {
            return response(['mensaje'=> 'usuario no habilitado'], 403)->header('Content-Type', 'application/json');
        }
        if(!Hash::check($request->password, $user->password)) {
            return response(['mensaje'=> 'usuario o password incorrectos'], 401)->header('Content-Type', 'application/json');
        }
        $token = JWTHelper::generate($user, env('APP_KEY', 'secret'));
        return response(['token'=>  $token,
                         'bearerToken' => 'Bearer '.$token], 200)->header('Content-Type', 'application/json');
    }

    public function validateAuth(Request $request):array {
        $token = $request->header('Authorization');
        if(!$token) {
            return ['mensaje'=> 'usuario no autenticado'];
        }
        if(!JWTHelper::isValid($token, env('APP_KEY', 'secret'))) {
            return ['mensaje'=> 'token no válido'];
        }
        return [];
    }

    private function validateInteger($idParam) {
        $idParam = filter_var($idParam, FILTER_VALIDATE_INT);
        if(!$idParam) {
            return false;
        }
        return true;
    }

    private function validateBoolean($idParam) {
        $idParam = filter_var($idParam, FILTER_VALIDATE_BOOLEAN);
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

    private function validateLogin($request) {
        if(!isset($request->email) || !isset($request->password)) {
            return ['mensaje'=>'el email y el password son obligatorios'];
        }
        if(!$this->validateEmail($request->email)) {
            return ['mensaje'=>'el campo email no es valido'];
        }
        return [];
    }

    private function validateCreate($request):array {
        if(!isset($request->name) || !isset($request->email) || !isset($request->password)) {
            return ['mensaje'=>'los campos name, email y password son obligatorios'];
        }
        if(trim($request->name) === "") {
            return ['mensaje'=>'el campo name no puede estar vacío'];
        }
        if(trim($request->email) === "") {
            return ['mensaje'=>'el campos email no puede estar vacío'];//email y password 
        }
        if(!$this->validateEmail($request->email)) {
            return ['mensaje'=>'el campo email no es valido'];
        }
        if(trim($request->password) === "") {
            return ['mensaje'=>'el campos email no puede estar vacío'];//email y password 
        }
        if(isset($request->role_id) && !$this->validateInteger($request->role_id)) {
            return ['mensaje'=>'el campo role_id debe ser entero'];
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
            return ['mensaje'=>'el campo nombre no puede estar vacío'];
        }
        if(isset($request->password) && trim($request->password) === "") {
            return ['mensaje'=>'el campo password no puede estar vacío'];
        }
        if(isset($request->email) && trim($request->email) === "") {
            return ['mensaje'=>'el campo email no puede estar vacío'];
        }
        if(isset($request->email) && !$this->validateEmail($request->email)) {
            return ['mensaje'=>'el campo email no es valido'];
        }
        if(isset($request->role_id) && !$this->validateInteger($request->role_id)) {
            return ['mensaje'=>'el campo role_id debe ser entero'];
        }
        if(isset($request->enabled) && !$this->validateBoolean($request->enabled)) {
            return ['mensaje'=>'el campo enabled debe ser booleano'];
        }
        return [];
    }

    private function getRoleId($roleDescription) {
        return Role::where('description', $roleDescription)->first()->id;
    }
}