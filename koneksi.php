<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error.log');

$conn = mysqli_connect("localhost", "root", "", "elearning");
if (!$conn) {
    die("Koneksi gagal");
}
