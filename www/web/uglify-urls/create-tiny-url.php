<?php
if(isset($_POST['data'])) {
	$data = json_decode($_POST['data'], true);
	$full_url = $data['full_url'];

	require_once(__DIR__ . '/../classes/TinyUrls.php');

	$tu = new TinyUrls();
	$tiny_url = $tu->create($full_url);

	echo json_encode([
		'full_url' => $full_url,
		'tiny_url' => GATEWAY_DOMAIN . '/' . $tiny_url
	]);
} else {
	echo 'Этот адрес принимает POST запрос в формате JSON вида: `{"full_url": "<ваша ссылка>"}`, а в ответ отдаёт JSON в формате `{"full_url": "<ваша ссылка>", "tiny_url": "<ваша короткая ссылка>"}`';
}