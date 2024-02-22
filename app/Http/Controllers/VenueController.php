<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venue;

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
        if(isset($error['mensaje'])) {
            return response($error, 400)->header('Content-Type', 'application/json');
        }
        Venue::create(['description' => $request->description, 'address' => $request->address]);
    }

    public function update(Request $request, string $id) {
        $updateArray = [];
        $error = $this->validateUpdate($request);
        if(isset($error['mensaje'])) {
            return response($error, 400)->header('Content-Type', 'application/json');
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
    }

    public function delete(Request $request, string $id) {
        $error = $this->validateReadAndDelete($request, $id);
        if(isset($error['mensaje'])) {
            return response($error, 400)->header('Content-Type', 'application/json');
        }
        $venue = Venue::find($id);
        if(!$venue) {
            return response(['mensaje'=>'el venue no existe'], 404)->header('Content-Type', 'application/json');
        }
        $venue->delete();
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
            return ['mensaje'=>'los parámetros description y address son obligatorios'];
        }
        return [];
    }

    private function validateUpdate($request): array {
        if(!$this->validateInteger($request->id)) {
            return ['mensaje'=>'parametro id debe ser entero'];
        }
        if(!isset($request->description) && !isset($request->address)) {
            return ['mensaje'=>'debe al menos actualizar los parámetros description o address'];
        }
        return [];
    }

}