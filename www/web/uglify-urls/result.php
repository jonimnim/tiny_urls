<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Сокращатель ссылок</title>

	<script>
		let full_url = '<?= $_POST['full_url'] ?>';
		let formData = new FormData();
		formData.append('data', JSON.stringify({'full_url': full_url}));
		fetch('create-tiny-url.php', {method: 'POST', body: formData}).then(r => r.json()).then(d => {
			let a_in = document.getElementById('a_in');
			let a_out = document.getElementById('a_out');
			
			a_in.href = d.full_url;
			a_in.innerHTML = d.full_url;

			a_out.href = d.tiny_url;
			a_out.innerHTML = d.tiny_url;
			console.log(d);
		});
	</script>
</head>
<body>
	<h1>Сокращатель ссылок</h1>
	Для вашей ссылки <a href="" id="a_in"></a> создана сокращённая ссылка: <a href="" id="a_out"></a>
</body>
</html>



