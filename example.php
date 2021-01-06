<?php

include "vendor/autoload.php";

//$form = new \UMFlint\GoogleFormsConverter\Form('https://docs.google.com/forms/d/e/1FAIpQLScuBOotKQTAle1GAg49BC4umx4nCBYHzh9SOiK-5P5eY-xU7A/viewform');
$form = new \UMFlint\GoogleFormsConverter\Form('https://docs.google.com/forms/d/e/1FAIpQLSdls8ZHN0eT2UIKdC2FM730McPOxVPbY3WVJzsxtMQr5vKD1A/viewform');

echo '<pre>';
print_r(json_encode($form->build()));