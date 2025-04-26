<?php

function encrypt($data, string $key = null) {
    if($data == null || $data == ""){
        return null;
    }
    $cipher = "AES-256-CBC";
    $iv_length = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($iv_length);
    $encrypted_data = null;
    if($key==null||$key==""){
        $encrypted_data = openssl_encrypt($data, $cipher, getenv("encrypt_key"), 0, $iv);
    }else{
        $encrypted_data = openssl_encrypt($data, $cipher, $key, 0, $iv);
    }
    

    $combined_data = $iv . $encrypted_data;


    $encrypted_data = base64_encode($combined_data);

    $encrypted_data = strtr($encrypted_data, [
        '+' => '-',  
        '/' => '_',  
        '=' => '',   
        '&' => '%26', 
        '#' => '%23',
    ]);
    return $encrypted_data;
}

function decrypt($encrypted_data, string $key = null) {
    if($encrypted_data == null || $encrypted_data == ""){
        return null;
    }
    $cipher = "AES-256-CBC";
    $iv_length = openssl_cipher_iv_length($cipher);

    $encrypted_data = strtr($encrypted_data, [
        '-' => '+',  
        '_' => '/', 
        '%26' => '&', 
        '%23' => '#', 
    ]);

    $padding_needed = 4 - (strlen($encrypted_data) % 4);
    if ($padding_needed !== 4) {
        $encrypted_data .= str_repeat('=', $padding_needed);
    }

    $decoded_data = base64_decode($encrypted_data, true);
    if ($decoded_data === false) {
        die("Error: Base64 decoding failed.");
    }

    if (strlen($decoded_data) < $iv_length) {
        die("Error: Invalid encrypted data length.");
    }
    $iv = substr($decoded_data, 0, $iv_length);
    $encrypted_data = substr($decoded_data, $iv_length);

    $decryption_key = $key ?: getenv("encrypt_key");
    $decrypted_data = openssl_decrypt($encrypted_data, $cipher, $decryption_key, 0, $iv);

    if ($decrypted_data === false || !mb_check_encoding($decrypted_data, 'UTF-8')) {
        die("Error decrypting data.");
    }

    return $decrypted_data;
}


?>