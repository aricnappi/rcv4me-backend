<?php
// Error reporting when debugging
//error_reporting(E_ALL | E_STRICT);
//Error reporting for production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log','../logs/error_log');

// Set mysqli error reporting for debugging (comment out for production)
//mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Include constants
include 'constants.php';

// Connect to the database
$mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);
