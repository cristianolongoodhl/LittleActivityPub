<?php
define('BASE_URI', (empty($_SERVER['HTTPS']) ? 'http' : 'https').'://'.$_SERVER['SERVER_NAME'].'/');
define('LAP_SRC_DIR_URI',BASE_URI.'lap_src/'); //users directory path, relative to the lap_src dir
define('LAP_USERS_DIR_PATH','../lap_users/'); //users directory path, relative to the lap_src dir
define('LAP_USERS_DIR_URI',BASE_URI.'lap_users/'); //users directory path, relative to the lap_src dir
?>
