<?php

require '../vendor/autoload.php';

$app = new \atk4\ui\App('Form Login');
$app->initLayout('Centered');
$mytemplate = $app->layout->add(['defaultTemplate'=>'./mytemplate.html']);

$form = $mytemplate->add('Form');
$form->addField('email');
$form->addField('password');