<?php
require_once(__DIR__ . '/../classes/TinyUrls.php');

$tiny_url = str_replace('/', '', $_SERVER['REQUEST_URI']);
$tu = new TinyUrls();

$full_url = $tu->getFullUrl($tiny_url);

if($full_url == FALSE) {
	header('location: 404.php');
} else {
	header('location: ' . $full_url);
}