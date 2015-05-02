<?php

define('STATIC_ROOT', '/fedportal/');

include_once(__DIR__ . '/../vendor/autoload.php');

use Monolog\Logger;

use Monolog\Handler\StreamHandler;

/**
 * @param string $name
 * @return Logger
 */
function get_logger($name)
{

  $log = new Logger($name);

  $log->pushHandler(new StreamHandler(__DIR__ . '/../out_log.log', Logger::DEBUG));

  return $log;
}

function get_log_file()
{
  return __DIR__ . '/../out_log.log';
}

function get_photo_dir()
{
  $uploadsDirectory = __DIR__ . '/../photo_files/';

  if (!file_exists($uploadsDirectory)) {
    mkdir($uploadsDirectory);
  }

  return $uploadsDirectory;
}

function path_to_link($path)
{
  if (!file_exists($path)) {
    return '';
  }

  $unix_path = str_replace('\\', '/', realpath($path));

  $root_pos = strpos($unix_path, STATIC_ROOT);

  return substr($unix_path, $root_pos) . (is_dir($path) ? '/' : '');
}

//echo path_to_link(__DIR__ . '/../admin_academics/home/css/');
