<?php
// public/includes/config.php
declare(strict_types=1);

const DB_HOST = 'localhost';
const DB_NAME = 'ecopoints';
const DB_USER = 'root';
const DB_PASS = '';

const APP_NAME = 'EcoPoints';

ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', '0'); // set to 1 when HTTPS

session_start();
