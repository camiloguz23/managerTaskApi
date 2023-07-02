<?php
require_once "connection/database.php";
require_once "headers/header.php";
$conection = new DBconnect();
$conn = $conection->obtenerConexion();

$metodo = $_SERVER['REQUEST_METHOD'];
$ruta = $_SERVER['REQUEST_URI'];

if ($metodo === 'GET') {
    $params = explode('/', $ruta);
    if (isset($params[3])) {
        $conection->getTicket($params[3]);
    } else {
        $conection->getTicket('');
    }
}

if ($metodo === 'POST') {

    $action = $_POST['action'];


    if ($action === 'create') {
        $description = $_POST['description'];
        $document = $_POST['document'];
        $status = $_POST['status'];
        $conection->createTicket($document, $description, $status);
    } else {
        $id = $_POST['id'];
        $status = $_POST['status'];
        $conection->editTicket($id, $status);
    }
}