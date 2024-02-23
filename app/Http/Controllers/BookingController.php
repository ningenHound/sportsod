<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Booking;
use \Datetime;

class BookingController extends Controller
{

    const BOOKING_TIME = 1800;
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

    public function listBookingsByField(Request $request, string $id) {
        $error = $this->validateReadAndDelete($request, $id);
        if(isset($error['mensaje'])) {
            return response($error, 400)->header('Content-Type', 'application/json');
        }
        $bookings = Booking::where('field_id', $id)->get();
        return response($bookings, 200)->header('Content-Type', 'application/json');
    }

    public function listActiveBookings(Request $request) {
        if(!$this->isValidDate($request->booking_start) || !$this->isValidDate($request->booking_end)) {
            return response(['mensaje'=>'los campos booking_start y booking_end deben ser fechas validas y con el formato correcto: YYYY-mm-dd HH:mm:ss'], 404)->header('Content-Type', 'application/json');
        }
        $bookings = Booking::all()->whereBetween('booking_start', [$request->booking_start, $request->booking_end]);
        return response($bookings, 200)->header('Content-Type', 'application/json');
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
            return ['mensaje'=>'el id debe ser numerico'];
        }
        return [];
    }

    private function validateCreate($request): array {
        if(!isset($request->field_id) || !isset($request->user_id) || !isset($request->booking_start) || !isset($request->booking_end)) {
            return ['mensaje'=>'los campos field_id, user_id, booking_start y booking_end son obligatorios'];
        }
        if(!$this->validateInteger($request->field_id) || !$this->validateInteger($request->user_id)) {
            return ['mensaje'=>'los campos field_id y user_id deben ser numericos'];
        }
        if(!$this->isValidDate($request->booking_start) || !$this->isValidDate($request->booking_end)) {
            return ['mensaje'=>'los campos booking_start y booking_end deben ser fechas validas y con el formato correcto: YYYY-mm-dd HH:mm:ss'];
        }
        
        if(strtotime($request->booking_end) - strtotime($request->booking_start) < 0) {
            return ['mensaje'=>'la fecha de inicio booking_start no puede ser mayor a la fecha fin booking_end'];
        }
        if(strtotime($request->booking_end) - strtotime($request->booking_start) < $this::BOOKING_TIME) {
            return ['mensaje'=>'el tiempo minimo de reserva es de media'];
        }

        return [];
    }

    private function validateUpdate($request): array {
        if(!$this->validateInteger($request->id)) {
            return ['mensaje'=>'parametro id debe ser entero'];
        }
        if(!isset($request->field_id) && !isset($request->booking_end)) {
            return ['mensaje'=>'debe al menos actualizar los campos field_id o booking_end'];
        }
        if(strtotime($request->booking_end) - strtotime($request->booking_start) < 0) {
            return ['mensaje'=>'la fecha de inicio booking_start no puede ser mayor a la fecha fin booking_end'];
        }
        if(strtotime($request->booking_end) - strtotime($request->booking_start) < $this::BOOKING_TIME) {
            return ['mensaje'=>'el tiempo minimo de reserva es de media'];
        }
        return [];
    }

    private function isValidDate($dateParam, $format="Y-m-d H:i:s") {
        $d = DateTime::createFromFormat($format, $dateParam);
        return $d && $d->format($format) == $dateParam;
    }
}