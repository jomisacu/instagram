<?php

use Hasokeyk\Instagram\Instagram;

require "../../vendor/autoload.php";

$username = 'username';
$password = 'password';

$instagram = new Instagram($username, $password);
$instagram->login->login();

$login = $instagram->login->login_control();
if ($login) {
    $post = $instagram->medias->send_comment_post('2546428212937660604', 'Good Pic');
    print_r($post);
} else {
    echo 'Login Fail';
}
