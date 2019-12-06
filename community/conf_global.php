<?php

// Set your environment/url pairs
$environments = array(
  'local'       => 'thepond.local',
  'production'  => 'thepond.howtohockey.com'
);

// Get the hostname
$http_host = $_SERVER['HTTP_HOST'];
$http_protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === 0 ? 'https://' : 'http://';

// Loop through $environments to see if there’s a match
foreach($environments as $environment => $hostname) {
  if (stripos($http_host, $hostname) !== FALSE) {
    define('ENVIRONMENT', $environment);
    break;
  }
}

$INFO = array (
  'sql_host' => getenv('THEPOND_DB_HOST'),
  'sql_database' => getenv('THEPOND_DB_NAME'),
  'sql_user' => getenv('THEPOND_DB_USER'),
  'sql_pass' => getenv('THEPOND_DB_PASSWORD'),
  'sql_port' => getenv('THEPOND_DB_PORT'),
  'sql_socket' => getenv('THEPOND_MYSQL_SOCKET'),
  'sql_tbl_prefix' => 'hth_',
  'sql_utf8mb4' => true,
  'board_start' => 1571926982,
  'installed' => true,
  'base_url' => $http_protocol . $environments[ENVIRONMENT] . '/community/',
  'guest_group' => 2,
  'member_group' => 3,
  'admin_group' => 4,
);
