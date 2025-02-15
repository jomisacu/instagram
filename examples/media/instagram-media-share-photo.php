<?php

use Hasokeyk\Instagram\Instagram;

require "../../vendor/autoload.php";

$username = 'username';
$password = 'password';

$instagram = new Instagram($username, $password);
$instagram->login->login();

if ($instagram->login->login_control()) {
    $image_path = (__DIR__) . '/assets/a.jpg';
    $check = $instagram->medias->share_photo($image_path, 'Test');
    print_r($check);
} else {
    echo 'Login Fail';
}
