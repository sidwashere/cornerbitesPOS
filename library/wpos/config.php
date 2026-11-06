<?php
/*
 * WallacePOS API configuration file
 */
// Extend session lifetime to 12 hours (43200 seconds) so Admin/POS stay logged in for a full shift
ini_set('session.gc_maxlifetime', 43200);
ini_set('session.cookie_lifetime', 43200);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 1000);
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(43200, '/');
}
$wposConfig = [];

// Paths
$_SERVER['APP_ROOT'] = "/";
if (!isset($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = "/app"; // this is what dokku uses as docroot, for some reason it's not set
}
// load timezone config if available
// TODO: cache this somehow
$timezone = "Australia/Sydney";
if (file_exists($_SERVER['DOCUMENT_ROOT'].$_SERVER['APP_ROOT']."docs/.config.json")){
    $GLOBALS['config'] = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'].$_SERVER['APP_ROOT']."docs/.config.json"));
    if (isset($GLOBALS['config']->timezone))
        $timezone = $GLOBALS['config']->timezone;
}
// Date & Time
ini_set('date.timezone', $timezone);

// Error handling
ini_set('display_errors', 'Off');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

/**
 * Php error handler, sets & returns json result object
 * @param $errorno
 * @param $errstr
 * @param $errfile
 * @param $errline
 */
function errorHandler($errorno, $errstr, $errfile, $errline){
    global $result;

    $result['errorCode'] = "phperr";

    if ($result['error'] == "OK") $result['error'] = "";

    $result['error'] =  "ERROR: " . ": " . $errstr . " " . $errfile . " on line " . $errline . "\n";

    die(json_encode($result));
}

/**
 * Php warning handler
 * @param $errorno
 * @param $errstr
 * @param $errfile
 * @param $errline
 */
function warningHandler($errorno, $errstr, $errfile, $errline){
    global $result;
    if (!is_array($result)) {
        $result = ["errorCode" => "OK", "error" => "OK", "data" => "", "warning" => ""];
    }
    if (!isset($result['warning']) || $result['warning'] === null) {
        $result['warning'] = "";
    }
    $result['warning'] .= "WARNING: " . $errstr . " " . $errfile . " on line " . $errline . "\n";
}

/**
 * Php exception handler, sets & returns json result object
 * @param Exception $ex
 */
function exceptionHandler(Throwable $ex){
    global $result;

    $result['errorCode'] = "phpexc";

    if ($result['error'] == "OK") $result['error'] = "";

    $result['error'] .= "EXCEPTION: " .$ex->getMessage() . "\nFile: " . $ex->getFile() . " line " . $ex->getLine();

    die(json_encode($result));
}




