<?php

namespace OpenproviderPremiumDns\lib\helpers;

class IdnEncoder
{
    public static function encode(string $name): string
    {
        if (!preg_match('//u', $name)) {
            $name = utf8_encode($name);
        }

        return idn_to_ascii($name);
    }
}
