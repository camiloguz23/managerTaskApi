<?php

require_once "connection/database.php";
require_once "headers/header.php";
$conection = new DBconnect();
$conn = $conection->obtenerConexion();
$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $conection->login($email, $password);
}