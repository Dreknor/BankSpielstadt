<?php
if (! function_exists('payments_per_hour')) {
    function payments_per_hour(\Illuminate\Database\Eloquent\Collection $payments, Int $getHour = null) {

        $array = [];

        foreach ($payments as $payment){
            $hour = $payment->created_at->format('H');
            if (array_key_exists($hour, $array )){
                $array[$hour] += $payment->amount;
            } else {
                $array[$hour] = $payment->amount;
            }

        }

        if ($getHour == null){
            return $array;
        } else {
            if (array_key_exists($getHour, $array )) {
                return $array[$getHour];
            }
            return 0;
        }
    }
}
