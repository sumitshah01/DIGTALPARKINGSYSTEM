<?php
// config/config.php
session_start();

$envValues = [];
$envFile = __DIR__ . '/../.env';
if (is_readable($envFile)) {
	$parsedEnv = @parse_ini_file($envFile, false, INI_SCANNER_RAW);
	if (is_array($parsedEnv)) {
		$envValues = $parsedEnv;
	}
}
$env = static function (string $key, string $default = '') use ($envValues): string {
	$value = getenv($key);
	return $value !== false ? $value : (string)($envValues[$key] ?? $default);
};

define('SITE_NAME', 'SmartPark');
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$projectRoot = realpath(__DIR__ . '/..');
$basePath = '';
if ($documentRoot && $projectRoot && str_starts_with(
	strtolower($projectRoot . DIRECTORY_SEPARATOR),
	strtolower(rtrim($documentRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)
)) {
	$relativePath = substr($projectRoot, strlen(rtrim($documentRoot, DIRECTORY_SEPARATOR)));
	$pathSegments = array_filter(explode(DIRECTORY_SEPARATOR, trim($relativePath, DIRECTORY_SEPARATOR)), 'strlen');
	$basePath = $pathSegments ? '/' . implode('/', array_map('rawurlencode', $pathSegments)) : '';
}
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['SERVER_NAME'] ?? 'localhost';
$port = (int)($_SERVER['SERVER_PORT'] ?? 0);
if ($port && !(($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443))) {
	$host .= ':' . $port;
}
define('SITE_URL', $scheme . '://' . $host . $basePath);
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Environment config (move to .env in production)
define('DB_HOST', $env('DB_HOST', 'localhost'));
define('DB_NAME', $env('DB_NAME', 'smart_parking'));
define('DB_USER', $env('DB_USER', 'root'));
define('DB_PASS', $env('DB_PASS'));

define('GOOGLE_CLIENT_ID', $env('GOOGLE_CLIENT_ID'));
define('GOOGLE_CLIENT_SECRET', $env('GOOGLE_CLIENT_SECRET'));
define('GOOGLE_REDIRECT_URI', SITE_URL . '/api/auth/google-login.php');

// Environmental assumptions (for impact calc)
define('FUEL_PER_KM', 0.08);          // Liters per km
define('CO2_PER_LITER', 2.31);         // kg CO2 per liter
define('AVG_SEARCH_DISTANCE', 1.5);    // km saved per search
define('AVG_SEARCH_TIME', 8);          // minutes saved

require_once __DIR__ . '/database.php';

date_default_timezone_set('Asia/Kolkata');
?>