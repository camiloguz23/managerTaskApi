<?php 
require_once "connection/database.php";
require_once "headers/header.php";
$conection = new DBconnect();
$conn = $conection->obtenerConexion();

$metodo = $_SERVER['REQUEST_METHOD'];
$ruta = $_SERVER['REQUEST_URI'];

if ($metodo === 'GET') {
    $params = explode('/', $ruta);
    if ($params[3]) {
        $conection->getUser($params[3]);
    } else {
        $conection->getUser('');
    }
}

if ($metodo === 'POST') {
    $name = $_POST['name'];
    $document = $_POST['document'];
    $email = $_POST['email'];
    $rol = $_POST['rol'];
    $password = $_POST['password'];

    $conection->createUser($document,$name,$email,$password,$rol);
}
?>