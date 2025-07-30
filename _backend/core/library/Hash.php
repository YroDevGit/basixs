<?php

class Hashing{

    public function hashString(String $string, int $size = 16){
        $secret = getenv("hash_secret");
        return substr(md5($secret.$string), 0, $length);
    }

    public function verifyHash(String $string, String $hash){
        $hashed1 = $this->hashString($string);
        return $hashed1 === $hash;
    }
}

?>