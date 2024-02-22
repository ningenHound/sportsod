<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Booking;

class BookingController extends Controller
{

    public function read(Request $request, string $id) {
        $error = $this->validateReadAndDelete($request, $id);
        if(isset($error['mensaje'])) {
            return response($error, 400)->header('Content-Type', 'application/json');
        }
        $booking = Booking::find($id);
        if(!$booking) {
            return response(['mensaje'=>'el booking no existe'], 404)->header('Content-Type', 'application/json');
        } 
        return $booking;
    }

    public function create(Request $request) {
        $error = $this->validateCreate($request);
        if(isset($error['mensaje'])) {
            return response($error, 400)->header('Content-Type', 'application/json');
        }
        Booking::create(['field_id' => $request->field_id, 
                        'user_id' => $request->user_id,
                        'booking_start' => $request->booking_start,
                        'booking_end' => $request->booking_end
                    ]);
    }

    public function update(Request $request, string $id) {
        $updateArray = [];
        $error = $this->validateUpdate($request);
        if(isset($error['mensaje'])) {
            return response($error, 400)->header('Content-Type', 'application/json');
        }
        if(isset($request->field_id)) {
            $updateArray['field_id'] = $request->field_id;
        }
        if(isset($request->booking_end)) {
            $updateArray['booking_end'] = $request->booking_end;
        }
        Booking::where('id', $request->id)
        ->update($updateArray);
    }

    public function delete(Request $request, string $id) {
        $error = $this->validateReadAndDelete($request, $id);
        if(isset($error['mensaje'])) {
            return response($error, 400)->header('Content-Type', 'application/json');
        }
        $booking = Booking::find($id);
        if(!$booking) {
            return response(['mensaje'=>'el booking no existe'], 404)->header('Content-Type', 'application/json');
        }
        $booking->delete();
    }

    public function listBookingsByField(Request $request) {
        // TODO
    }

    public function listBookingsBetweenDates(Request $request) {
        // TODO
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
            return ['mensaje'=>'el id debe ser entero'];
        }
        return [];
    }

    private function validateCreate($request): array {
        if(!isset($request->field_id) || !isset($request->user_id) || !isset($request->booking_start) || !isset($request->booking_end)) {
            return ['mensaje'=>'los campos field_id, user_id, booking_start y booking_end son obligatorios'];
        }
        return [];
    }

    private function validateUpdate($request): array {
        if(!$this->validateInteger($request->id)) {
            return ['mensaje'=>'parametro id debe ser entero'];
        }
        if(!isset($request->field_id) && !isset($request->address)) {
            return ['mensaje'=>'debe al menos actualizar los campos field_id o booking_end'];
        }
        return [];
    }
}