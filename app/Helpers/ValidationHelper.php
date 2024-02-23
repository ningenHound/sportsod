<?php

namespace App\Helpers;

class ValidationHelper
{
    public function isInteger($idParam) {
        $idParam = filter_var($idParam, FILTER_VALIDATE_INT);
        if(!$idParam) {
            return false;
        }
        return true;
    }

    public function isValidDate($dateParam, $format="Y-m-d H:i:s") {
        $d = DateTime::createFromFormat($format, $dateParam);
        return $d && $d->format($format) == $dateParam;
    }

    public function isValidEmail($emailParam) {
        $emailParam = filter_var($emailParam, FILTER_VALIDATE_EMAIL);
        if(!$emailParam) {
            return false;
        }
        return true;
    }

}