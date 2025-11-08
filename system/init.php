<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by WeCenter Software
|   © 2011 - 2014 WeCenter. All Rights Reserved
|   http://www.wecenter.com
|   ========================================
|   Support: WeCenter@qq.com
|
+---------------------------------------------------------------------------
*/

define('IN_ANWSION', TRUE);
define('ENVIRONMENT_PHP_VERSION', '5.2.2');
//define('SYSTEM_LANG', 'en_US');

if (substr(PHP_VERSION, -4) == 'hhvm')
{
	die('Error: WeCenter not support HHVM currently');
}
else if (version_compare(PHP_VERSION, ENVIRONMENT_PHP_VERSION, '<'))
{
	die('Error: WeCenter require PHP version ' . ENVIRONMENT_PHP_VERSION . ' or newer');
}

define('START_TIME', microtime(TRUE));
define('TIMESTAMP', time());

if (function_exists('memory_get_usage'))
{
	define('MEMORY_USAGE_START', memory_get_usage());
}

if (! defined('AWS_PATH'))
{
	define('AWS_PATH', dirname(__FILE__) . '/');
}

if (defined('SAE_TMP_PATH'))
{
	define('IN_SAE', true);
}

define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');

@ini_set('display_errors', '0');

if (defined('IN_SAE'))
{
	error_reporting(0);

	define('TEMP_PATH', rtrim(SAE_TMP_PATH, '/') . '/');
}
else
{
	if (version_compare(PHP_VERSION, '5.4', '>='))
	{
		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING);
	}
	else
	{
		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
	}

	define('TEMP_PATH', dirname(dirname(__FILE__)) . '/tmp/');
}

if (file_exists(AWS_PATH . 'enterprise.php'))
{
	require_once(AWS_PATH . 'enterprise.php');
}

// magic_quotes_gpc has been removed since PHP 5.4, no longer needed in PHP 7.4+

require_once(ROOT_PATH . 'version.php');
require_once(AWS_PATH . 'functions.inc.php');

array_walk_recursive($_GET, 'remove_invisible_characters');
array_walk_recursive($_POST, 'remove_invisible_characters');
array_walk_recursive($_COOKIE, 'remove_invisible_characters');
array_walk_recursive($_REQUEST, 'remove_invisible_characters');

// register_globals has been removed since PHP 5.4, no longer needed in PHP 7.4+

require_once(AWS_PATH . 'functions.app.php');

if (file_exists(AWS_PATH . 'config.inc.php'))
{
	require_once(AWS_PATH . 'config.inc.php');
}

// Load Composer autoload if available
if (file_exists(ROOT_PATH . 'vendor/autoload.php'))
{
	require_once(ROOT_PATH . 'vendor/autoload.php');
}

load_class('core_autoload');

date_default_timezone_set('Etc/GMT-8');
