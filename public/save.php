<?php
include "./header.php";

require_once './handlers/_common.php';

$action = $_POST['action'] ?? '';

$handlers = [
    'register_single' => './handlers/register_single.php',
    'register_tenolix' => './handlers/register_tenolix.php',
    'register_bulk'   => './handlers/register_bulk.php',
    'register_shifts' => './handlers/register_shifts.php',
    'register_ipsc'   => './handlers/register_ipsc.php',
    'register_mcr'    => './handlers/register_mcr.php',
    'cancel_shooter'  => './handlers/cancel_shooter.php',
    'change_password' => './handlers/change_password.php',
];

if (isset($handlers[$action])) {
    require $handlers[$action];
} else {
    http_response_code(400);
    exit('Neznámá akce.');
}