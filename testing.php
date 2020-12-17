<?php

include "vendor/autoload.php";

$form = new \UMFlint\GoogleFormsConverter\Form('https://docs.google.com/forms/d/e/1FAIpQLScuBOotKQTAle1GAg49BC4umx4nCBYHzh9SOiK-5P5eY-xU7A/viewform');

echo 'Testing Form';
echo '<pre>';
print_r($form->build());
echo '</pre>';