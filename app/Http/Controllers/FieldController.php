<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Field;
use App\Models\Role;
use App\Helpers\JWTHelper;

class FieldController extends Controller
{

    public function read(Request $request, string $id) {
        $error = $this->validateReadAndDelete($request, $id);
        if(isset($error['mensaje'])) {
            return response($error, 400)->header('Content-Type', 'application/json');
        }
        $field = Field::find($id);
        if(!$field) {
            return response(['mensaje'=>'el field no existe'], 404)->header('Content-Type', 'application/json');
        }
        return $field;
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
        Field::create(['field_type' => $request->field_type, 'venue_id' => $request->venue_id]);
        return response(['mensaje'=>'field creado'], 201)->header('Content-Type', 'application/json');
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
        $field = Field::find($id);
        if(!$field) {
            return response(['mensaje'=>'el field no existe'], 404)->header('Content-Type', 'application/json');
        }
        if(isset($request->field_type)) {
            $updateArray['field_type'] = $request->field_type;
        }
        if(isset($request->venue_id)) {
            $updateArray['venue_id'] = $request->venue_id;
        }
        $field->update($updateArray);
        return response(['mensaje'=>'field actualizado'], 200)->header('Content-Type', 'application/json');
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
        $field = Field::find($id);
        if(!$field) {
            return response(['mensaje'=>'el field no existe, no hay nada para borrar'], 404)->header('Content-Type', 'application/json');
        }
        $field->delete();
        return response(['mensaje'=>'venue eliminado'], 200)->header('Content-Type', 'application/json');
    }

    public function listBookingsByField(Request $request, string $id) {
        $error = $this->validateReadAndDelete($request, $id);
        $errorAuth = $this->validateAuth($request);
        if(isset($errorAuth['mensaje'])) {
            return response($errorAuth, 401)->header('Content-Type', 'application/json');
        }
        if(isset($error['mensaje'])) {
            return response($error, 400)->header('Content-Type', 'application/json');
        }
        $field = Field::find($id);
        if(!$field) {
            return response(['mensaje'=>'el field no existe'], 404)->header('Content-Type', 'application/json');
        }
        Booking::whereBetween('reservation_from', [$from, $to])->get();
    }

    private function validateInteger($idParam) {
        $idParam = filter_var($idParam, FILTER_VALIDATE_INT);
        if(!$idParam) {
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
        if(!isset($request->field_type) || !isset($request->venue_id)) {
            return ['mensaje'=>'los campos field_type y venue_id son obligatorios'];
        }
        if(!$this->validateInteger($request->field_type) || !$this->validateInteger($request->venue_id)) {
            return ['mensaje'=>'los campos field_type y venue_id deben ser enteros'];
        }
        return [];
    }

    private function validateUpdate($request):array {
        if(!$this->validateInteger($request->id)) {
            return ['mensaje'=>'el id debe ser entero'];
        }
        if(!isset($request->field_type) && !isset($request->venue_id)) {
            return ['mensaje'=>'debe al menos actualizar los campos field_type o venue_id'];
        }
        if(!$this->validateInteger($request->field_type) || !$this->validateInteger($request->venue_id)) {
            return ['mensaje'=>'los campos field_type y venue_id deben ser enteros'];
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