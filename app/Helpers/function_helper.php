<?php

function slugConvert($string, $type)
{
    $string = str_replace(array('[\', \']'), '', $string);
    $string = preg_replace('/\[.*\]/U', '', $string);
    $string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $string);
    $string = htmlentities($string, ENT_COMPAT, 'utf-8');
    $string = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $string);
    if ($type == 'url') {
        $string = preg_replace(array('/[^a-z0-9]/i', '/[-]+/'), '-', $string);
    } else {
        $string = preg_replace(array('/[^a-z0-9]/i', '/[-]+/'), '', $string);
    }
    return strtolower(trim($string, '-'));
}

function profile_path($path = null, $name)
{
    // if url path in database is null
    if ($path == null || $path == '') {
        return 'https://ui-avatars.com/api/?background=random&color=random&format=svg&name=' . $name;
    } else {
        return base_url('admin/assets/img/' . $path);
    }
}

function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
function generateRandomInt($length = 6)
{
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function convertDate($dates, $times = false)
{
    $dates = date_create($dates);
    $day = date_format($dates, "l");
    $month = date_format($dates, "m");
    $tgl = date_format($dates, "d");
    $year = date_format($dates, "Y");
    $time = date_format($dates, "H:i:s");
    switch ($day) {
        case "Sunday":
            $day = "Minggu";
            break;
        case "Monday":
            $day = "Senin";
            break;
        case "Tuesday":
            $day = "Selasa";
            break;
        case "Wednesday":
            $day = "Rabu";
            break;
        case "Thursday":
            $day = "Kamis";
            break;
        case "Friday":
            $day = "Jumat";
            break;
        case "Saturday":
            $day = "Sabtu";
            break;
    }

    switch ($month) {
        case "1":
            $month = "Januari";
            break;
        case "2":
            $month = "Februari";
            break;
        case "3":
            $month = "Maret";
            break;
        case "4":
            $month = "April";
            break;
        case "5":
            $month = "Mei";
            break;
        case "6":
            $month = "Juni";
            break;
        case "7":
            $month = "Juli";
            break;
        case "8":
            $month = "Agustus";
            break;
        case "9":
            $month = "September";
            break;
        case "10":
            $month = "Oktober";
            break;
        case "11":
            $month = "November";
            break;
        case "12":
            $month = "Desember";
            break;
    }
    if ($times == true) {
        return $day . ", " . $tgl . " " . $month . " " . $year . " " . $time;
    } else {
        return $day . ", " . $tgl . " " . $month . " " . $year;
    }
}

function encrypt($data, $key)
{
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    return urlencode(str_replace('/', '-', base64_encode($iv . $encrypted)));
}

// Decode function
function decrypt($data, $key)
{
    $data = str_replace('-', '/', urldecode($data));
    $data = base64_decode($data);
    $iv = substr($data, 0, openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = substr($data, openssl_cipher_iv_length('aes-256-cbc'));
    return openssl_decrypt($encrypted, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
}

function siencrypt($data, $key)
{
    $hash = hash_hmac('sha256', $data, $key);
    $encryptedData = base64_encode($data . '|' . $hash);
    return $encryptedData;
}

function sidecrypt($encryptedData, $key)
{
    $decodedData = base64_decode($encryptedData);
    // if not exist | in decoded data
    if (strpos($decodedData, '|') === false) {
        return false;
    }
    list($data, $hash) = explode('|', $decodedData, 2);

    $calculatedHash = hash_hmac('sha256', $data, $key);

    if (hash_equals($calculatedHash, $hash)) {
        return $data;
    } else {
        // Hash tidak cocok, data mungkin telah diubah atau terjadi kesalahan.
        return false;
    }
}
