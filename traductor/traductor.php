<?php

require_once ('vendor/autoload.php');

$source = 'pt';
$target = 'en';
$text = 'buenos días';

$trans = new \Statickidz\GoogleTranslate();
$result = $trans->translate($source, $target, $text);

// Good morning
echo $result;

