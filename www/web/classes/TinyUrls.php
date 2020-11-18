<?php
require_once('../settings.php');

class TinyUrls {

	function __construct() {
		$this->redis = new Redis();
		$this->redis->connect(REDIS_HOST, REDIS_PORT);
		$this->time_expire = REDIS_TIME_EXPIRE;

		$servername = MYSQL_HOST;
		$username = MYSQL_USER;
		$password = MYSQL_PASS;
		$db = MYSQL_BASE;

		$this->mysql = new MySQLi($servername, $username, $password, $db);
		if ($this->mysql->connect_errno) {
			echo "Errno: " . $this->mysql->connect_errno . "\n";
		}
	}
	
	public function create($full_url) {
		$tiny_url = $this->getTinyUrlFromMySQL($full_url);

		if($tiny_url == FALSE) {
			$tiny_url = $this->makeurl2();
			// будем создавать случайную комбинацию до тех пор пока она не будет уникальной
			while($this->getFullUrlFromMySQL($tiny_url) != FALSE) {
				$tiny_url = makeurl2();
			}
			
			$this->setFullUrlToMySQL($tiny_url, $full_url);
		}

		$this->setFullUrlToRedis($tiny_url, $full_url);
		return $tiny_url;
	}

	// метод для получения полного урл по короткому. Сначала поиск производится в редисе, если его там нет, производится поиск в MySQL, если его и там нет, то возвращается FALSE. Если находится в MySQL то в редис тоже устанавливается это занчение
	public function getFullUrl($tiny_url) {
		$full_url = $this->getFullUrlFromRedis($tiny_url);
		if(!$full_url) {
			$full_url = $this->getFullUrlFromMySQL($tiny_url);
			if($full_url != FALSE) {
				$this->setFullUrlToRedis($tiny_url, $full_url);
			}
		}

		return $full_url;
	}

	// метод для получения полного урл по короткому из редиса
	public function getFullUrlFromRedis($tiny_url) {
		return $this->redis->get($tiny_url);
	}

	// метод для установки значения в редис
	function setFullUrlToRedis($tiny_url, $full_url) {
		$this->redis->set($tiny_url, $full_url);
		$this->redis->expire($tiny_url, $this->time_expire);
	}

	// метод для получения длинного урл по короткому из MySQL
	public function getFullUrlFromMySQL($tiny_url) {
		$full_url = false;
		if ($result = $this->mysql->query("SELECT * FROM d_urls WHERE tiny_url = '" . $tiny_url . "'")) {
			while ($row = $result->fetch_assoc()) {
				$full_url = $row['full_url'];
			}
		}

		return $full_url;
	}

	// метод для получения короткого урл по полному, необходимо на случай, если происходит повторное добавление одного и того же урл
	public function getTinyUrlFromMySQL($full_url) {
		$tiny_url = false;
		if ($result = $this->mysql->query("SELECT * FROM d_urls WHERE full_url = '" . $full_url . "'")) {
			while ($row = $result->fetch_assoc()) {
				$tiny_url = $row['tiny_url'];
			}
		}

		return $tiny_url;
	}

	// метод для добавления урл в MySQL
	function setFullUrlToMySQL($tiny_url, $full_url) {
		if ($stmt = $this->mysql->prepare("INSERT INTO d_urls (tiny_url, full_url) VALUES (?, ?)")) {
			$stmt->bind_param("ss", $tiny_url, $full_url);
			$stmt->execute();
		}
	}


	// метод для генерации суффикса для короткого урл
	//https://github.com/Pavelstn/linkcrop/blob/master/lib/makeurl.php
	function makeurl2($length=6, $strength=4) {
	    $vowels = 'aeuy';
	    $consonants = 'bdghjmnpqrstvz';
	    if ($strength & 1) {
	        $consonants .= 'BDGHJLMNPQRSTVWXZ';
	    }
	    if ($strength & 2) {
	        $vowels .= "AEUY";
	    }
	    if ($strength & 4) {
	        $consonants .= '1234567890';
	    }
	    if ($strength & 8) {
	        $consonants .= '@#$%';
	    }

	    $password = '';
	    $alt = time() % 2;
	    for ($i = 0; $i < $length; $i++) {
	        if ($alt == 1) {
	            $password .= $consonants[(rand() % strlen($consonants))];
	            $alt = 0;
	        } else {
	            $password .= $vowels[(rand() % strlen($vowels))];
	            $alt = 1;
	        }
	    }
	    return $password;
	}
}