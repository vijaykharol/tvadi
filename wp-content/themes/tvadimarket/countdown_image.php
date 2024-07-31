<?php
// if (isset($_GET['expiry'])) {
//     $expiryTime         =   intval($_GET['expiry']);
//     $currentTime        =   time();
//     $remainingTime      =   $expiryTime - $currentTime;

//     if ($remainingTime <= 0) {
//         $remainingTime = 0;
//     }

//     $minutes = floor($remainingTime / 60);
//     $seconds = $remainingTime % 60;

//     header('Content-Type: image/png');
//     $im = imagecreatetruecolor(200, 50);
//     $white = imagecolorallocate($im, 255, 255, 255);
//     $black = imagecolorallocate($im, 0, 0, 0);
//     imagefilledrectangle($im, 0, 0, 200, 50, $white);

//     $font = __DIR__ . '/fonts/Roboto-Bold.ttf'; // Make sure you have this font or any other font in your directory
//     $text = sprintf('%02d:%02d', $minutes, $seconds);
//     imagettftext($im, 20, 0, 10, 30, $black, $font, $text);
//     imagepng($im);
//     imagedestroy($im);
// } else {
//     echo "No expiry time provided.";
// }

if (isset($_GET['expiry'])){
    $expiryTime     =   intval($_GET['expiry']);
    $currentTime    =   time();
    $remainingTime  =   $expiryTime - $currentTime;

    if ($remainingTime <= 0) {
        $remainingTime = 0;
    }

    $minutes = floor($remainingTime / 60);
    $seconds = $remainingTime % 60;

    // Prevent caching of the image
    header("Expires: Tue, 01 Jan 1980 00:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    // Create the image
    header('Content-Type: image/png');
    $im = imagecreatetruecolor(200, 50);
    $white = imagecolorallocate($im, 255, 255, 255);
    $black = imagecolorallocate($im, 0, 0, 0);
    imagefilledrectangle($im, 0, 0, 200, 50, $white);

    // Path to your font file
    $font = __DIR__ . '/fonts/Roboto-Bold.ttf'; // Make sure you have this font or any other font in your directory
    if (!file_exists($font)) {
        // If the font file doesn't exist, use a default system font
        $font = __DIR__ . '/fonts/Roboto-Bold.ttf'; // Make sure you have this font or any other font in your directory
    }

    $text = sprintf('%02d:%02d', $minutes, $seconds);
    imagettftext($im, 20, 0, 10, 30, $black, $font, $text);
    imagepng($im);
    imagedestroy($im);
} else {
    echo "No expiry time provided.";
}
?>
