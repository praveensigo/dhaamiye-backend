<?php

namespace App\Helpers;

class Formats
{
    public function customDate($date)
    {
        //return date('F d, Y', strtotime($date));
        return date('d M Y', strtotime($date));
    }

    public function customTimestamp($timestamp)
    {
        return date('d/m/Y h:i A', strtotime($timestamp));
    }

    public function customDateTime($timestamp)
    {
        return date('d M Y, h:i a', strtotime($timestamp));
    }

}
