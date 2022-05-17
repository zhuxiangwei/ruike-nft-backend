<?php
/**
 * Here is your custom functions.
 */

 include(base_path() . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "phpqrcode" . DIRECTORY_SEPARATOR . "qrlib.php");
 function save_qrcode_to_image($invite_url)
 {
    $qrcode_image_file = public_path() . DIRECTORY_SEPARATOR . md5($invite_url) . ".png";
    if(file_exists($qrcode_image_file))
    {
       return;
    }
    QRcode::png($invite_url, $qrcode_image_file);
    return $qrcode_image_file;
 }