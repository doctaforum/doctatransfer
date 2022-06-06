<?php

namespace App\Helper;

class RandomPassword {

    public static function generate() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $symbols = '@#~%';

        $chars = [$alphabet, $alphabet, $alphabet, $alphabet, $alphabet, $alphabet, $symbols];

        $pass = [];
        $rand = rand(12, 16);

        for ($i = 0; $i < $rand; $i++) {
            $randArray = rand(0,6);

            $n = rand(0, strlen($chars[$randArray])- 1);

            $pass[] = $chars[$randArray][$n];
        }

        return implode($pass);
    }
}