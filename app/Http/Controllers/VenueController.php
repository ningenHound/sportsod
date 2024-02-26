<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venue;
use App\Models\Role;
use Illuminate\Support\Facades\Redis;
use App\Helpers\JWTHelper;

class VenueController extends Controller
{

    public function read(Request $request, string $id) {
        $error = $this->validateReadAndDelete($request, $id);
        if(isset($error['mensaje'])) {
            return response($error, 400)->header('Content-Type', 'application/json');
        }
        $venue = Venue::find($id);
        if(!$venue) {
            return response(['mensaje'=>'el venue no existe'], 404)->header('Content-Type', 'application/json');
        }
        return $venue;
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

        Venue::create(['description' => $request->description, 'address' => $request->address]);
        return response(['mensaje'=>'venue creado'], 201)->header('Content-Type', 'application/json');
    }

    public function update(Request $request, string $id) {
        $updateArray = [];
        $error = $this->validateUpdate($request);
        $errorAuth = $this->validateAuth($request);
        if(isset($errorAuth['mensaje'])) {
            return response($errorAuth, 401)->header('Content-Type', 'application/json');
        }
        if(isset($error['mensaje'])) {
            return response($error, 400)->header('Content-Type', 'application/json');
        }
        $token = $request->header('Authorization');
        $role_id = JWTHelper::getClaim($token);
        $userRole = Role::find($role_id)->description;
        if($userRole != "ADMIN" || $userRole != "SYSTEM_ADMIN") {
            return response(['mensaje'=>'no autorizado'], 403)->header('Content-Type', 'application/json');
        }
        $venue = Venue::find($id);
        if(!$venue) {
            return response(['mensaje'=>'el venue no existe'], 404)->header('Content-Type', 'application/json');
        }
        if(isset($request->field_type)) {
            $updateArray['field_type'] = $request->field_type;
        }
        if(isset($request->venue_id)) {
            $updateArray['venue_id'] = $request->venue_id;
        }
        $venue->update($updateArray);
        return response(['mensaje'=>'venue actualizado'], 200)->header('Content-Type', 'application/json');
    }

    public function delete(Request $request, string $id) {
        $error = $this->validateReadAndDelete($request, $id);
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
        
        if(isset($error['mensaje'])) {
            return response($error, 400)->header('Content-Type', 'application/json');
        }
        $venue = Venue::find($id);
        if(!$venue) {
            return response(['mensaje'=>'el venue no existe'], 404)->header('Content-Type', 'application/json');
        }
        $venue->delete();
        return response(['mensaje'=>'venue eliminado'], 200)->header('Content-Type', 'application/json');
    }

    public function listVenues(Request $request) {
        $redis = Redis::connection();
        $redisVenues = $redis->get('venues');
        if(isset($redisVenues)) {
            $venues = json_decode($redisVenues);
        } else {
            $redis->set('venues', json_encode(Venue::all()));
            $venues = json_decode($redis->get('venues'));
        }
        return response($venues, 200)->header('Content-Type', 'application/json');
    }

    private function validateInteger($idParam) {
        $idParam = filter_var($idParam, FILTER_VALIDATE_INT);
        if(!$idParam) {
            return false;
        }
        return true;
    }

    private function validateReadAndDelete($request, $id): array {
        if(!$this->validateInteger($id)) {
            return ['mensaje'=>'parametro id debe ser entero'];
        }
        return [];
    }

    private function validateCreate($request): array {
        if(!isset($request->description) || !isset($request->address)) {
            return ['mensaje'=>'los campos description y address son obligatorios'];
        }
        if(trim($request->description) === "") {
            return ['mensaje'=>'el campo descripcion no puede estar vacío'];
        }
        if(trim($request->address) === "") {
            return ['mensaje'=>'el campo address no puede estar vacío'];
        }
        return [];
    }

    private function validateUpdate($request): array {
        if(!$this->validateInteger($request->id)) {
            return ['mensaje'=>'parametro id debe ser entero'];
        }
        if(!isset($request->description) && !isset($request->address)) {
            return ['mensaje'=>'debe al menos actualizar los campos description o address'];
        }
        if(isset($request->description) && trim($request->description) === "") {
            return ['mensaje'=>'el campo descripcion no puede estar vacío'];
        }
        if(isset($request->address) && trim($request->address) === "") {
            return ['mensaje'=>'el campo address no puede estar vacío'];
        }
        return [];
    }

    private function validateAuth(Request $request):array {
        $token = $request->header('Authorization');
        if(!$token) {
            return ['mensaje'=> 'usuario no autenticado'];
        }
        if(!JWTHelper::isValid($token, env('APP_KEY', 'secret'))) {
            return ['mensaje'=> 'token no valido'];
        }
        return [];
    }

}