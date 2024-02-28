<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Booking;
use App\Helpers\JWTHelper;
use Illuminate\Support\Facades\DB;
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
        dd($booking);
        if(!$booking) {
            return response(['mensaje'=>'el booking no existe'], 404)->header('Content-Type', 'application/json');
        } 
        return $booking;
    }

    public function create(Request $request) {
        //dd(env('APP_KEY'));
        $error = $this->validateCreate($request);
        $errorAuth = $this->validateAuth($request);
        if(isset($errorAuth['mensaje'])) {
            return response($errorAuth, 401)->header('Content-Type', 'application/json');
        }
        if(isset($error['mensaje'])) {
            return response($error, 400)->header('Content-Type', 'application/json');
        }
        $activeBookings = $bookings = DB::select('select id, field_id, user_id, booking_start, booking_end, created_at, updated_at from bookings where field_id=? and booking_start >= ? and booking_end <= ?',[$request->field_id, $request->booking_start, $request->booking_end]);
        if(count($activeBookings) > 0) {
            return response(['mensaje' => 'ya existe una reserva para ese campo en esa fecha'], 403)->header('Content-Type', 'application/json');
        }
        Booking::create(['field_id' => $request->field_id, 
                        'user_id' => $request->user_id,
                        'booking_start' => $request->booking_start,
                        'booking_end' => $request->booking_end
                    ]);

        return response(['mensaje' => 'reserva creada'], 201)->header('Content-Type', 'application/json');            
    }

    public function update(Request $request, string $id) {
        $updateArray = [];
        $errorAuth = $this->validateAuth($request);
        if(isset($errorAuth['mensaje'])) {
            return response($errorAuth, 401)->header('Content-Type', 'application/json');
        }
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
        return response(['mensaje'=>'booking actualizado'], 200)->header('Content-Type', 'application/json');
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
        if(!isset($request->booking_start) || !isset($request->booking_start) || !isset($request->field_id)) {
            return response(['mensaje'=>'los campos booking_start, booking_end y field_id son obligatorios'], 400)->header('Content-Type', 'application/json');
        }
        if(!$this->validateInteger($request->field_id)) {
            return response(['mensaje'=>'el campo field_id debe ser numérico'], 400)->header('Content-Type', 'application/json');
        }
        if(!$this->isValidDate($request->booking_start) || !$this->isValidDate($request->booking_end)) {
            return response(['mensaje'=>'los campos booking_start y booking_end deben ser fechas validas y con el formato correcto: YYYY-mm-dd HH:mm:ss'], 400)->header('Content-Type', 'application/json');
        }
        $bookings = DB::select('select id, field_id, user_id, booking_start, booking_end, created_at, updated_at from bookings where field_id=? and booking_start >= ? and booking_end <= ?',[$request->field_id, $request->booking_start, $request->booking_end]);
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
            return ['mensaje'=>'el id debe ser numérico'];
        }
        return [];
    }

    private function validateCreate($request): array {
        if(!isset($request->field_id) || !isset($request->user_id) || !isset($request->booking_start) || !isset($request->booking_end)) {
            return ['mensaje'=>'los campos field_id, user_id, booking_start y booking_end son obligatorios'];
        }
        if(!$this->validateInteger($request->field_id) || !$this->validateInteger($request->user_id)) {
            return ['mensaje'=>'los campos field_id y user_id deben ser numéricos'];
        }
        if(!$this->isValidDate($request->booking_start) || !$this->isValidDate($request->booking_end)) {
            return ['mensaje'=>'los campos booking_start y booking_end deben ser fechas validas y con el formato correcto: YYYY-mm-dd HH:mm:ss'];
        }
        
        if(strtotime($request->booking_end) - strtotime($request->booking_start) < 0) {
            return ['mensaje'=>'la fecha de inicio booking_start no puede ser mayor a la fecha fin booking_end'];
        }
        if((strtotime($request->booking_end) - strtotime($request->booking_start)) < $this::BOOKING_TIME) {
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

    public function validateAuth(Request $request):array {
        $token = $request->header('Authorization');
        //dd($token);
        if(!$token) {
            return ['mensaje'=> 'usuario no autenticado'];
        }
        if(!JWTHelper::isValid($token, env('APP_KEY', 'secret'))) {
            return ['mensaje'=> 'token no valido'];
        }
        return [];
    }
}