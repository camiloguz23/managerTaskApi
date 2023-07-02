<?php

class DBconnect
{
    private $mysql;
    private $host;
    private $username;
    private $password;
    private $database;

    public function __construct()
    {
        
        $this->host = getenv('DB_HOST');
        $this->username = getenv('DB_USERNAME');
        $this->password = getenv('DB_PASSWORD');
        $this->database = getenv('DB_DATABASE');

        $this->mysql = mysqli_connect($this->host, $this->username, $this->password, $this->database);

        if ($this->mysql->connect_error) {
            die("Error de conexión: " . mysqli_connect_error());
        }
    }

    public function obtenerConexion()
    {
        return $this->mysql;
    }

    public function getStatus()
    {
        $query = "SELECT * FROM status";
        $stmt = mysqli_prepare($this->mysql, $query);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $response = array();

        while ($row = mysqli_fetch_assoc($result)) {
            $response[] = $row;
        }

        mysqli_stmt_close($stmt);

        http_response_code(200);
        echo json_encode($response);
    }

    public function getUser($iduser)
    {
        $response = array();

        if ($iduser) {
            $query = "SELECT u.document, u.name, u.email, r.nameRol
                      FROM user u
                      INNER JOIN rol r ON u.rol = r.id
                      WHERE u.document = ?";
        } else {
            $query = "SELECT u.document, u.name, u.email, r.nameRol
                      FROM user u
                      INNER JOIN rol r ON u.rol = r.id";
        }

        $stmt = mysqli_prepare($this->mysql, $query);

        if ($iduser) {
            mysqli_stmt_bind_param($stmt, "i", $iduser);
        }
        mysqli_stmt_execute($stmt);

        $resultQuery = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($resultQuery)) {
            $response[] = $row;
        }

        mysqli_stmt_close($stmt);

        if (!empty($response)) {
            http_response_code(200);
            echo json_encode($response);
        } else {
            http_response_code(404);
            echo 'User no encontrado';
        }
    }

    private function validationByEmail($email)
    {
        $email = $this->mysql->real_escape_string($email);
        $query = "SELECT COUNT(*) as count FROM user WHERE email = ?";
        $stmt = mysqli_prepare($this->mysql, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $count = $row['count'];

        mysqli_stmt_close($stmt);

        return $count > 0;
    }

    public function createUser($document, $name, $email, $password, $rol)
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $isValid = $this->validationByEmail($email);

        if ($isValid) {
            return 'Usuario ya existe';
        } else {
            $query = "INSERT INTO user (document, name, email, rol, password) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($this->mysql, $query);
            mysqli_stmt_bind_param($stmt, "sssss", $document, $name, $email, $rol, $hashedPassword);
            $result = mysqli_stmt_execute($stmt);

            if ($result) {
                mysqli_stmt_close($stmt);
                http_response_code(201);
                echo 'Inserción exitosa';
            } else {
                mysqli_stmt_close($stmt);
                http_response_code(422);
                throw new Exception("Error en la inserción: " . mysqli_error($this->mysql));
            }
        }
    }

    public function createTicket($document, $description, $status)
    {
        $query = "INSERT INTO tickets (user, description, idStatus) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($this->mysql, $query);
        mysqli_stmt_bind_param($stmt, "sss", $document, $description, $status);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            mysqli_stmt_close($stmt);
            http_response_code(201);
            echo "Inserción exitosa";
        } else {
            mysqli_stmt_close($stmt);
            http_response_code(422);
            throw new Exception("Error en la inserción: " . mysqli_error($this->mysql));
        }
    }

    public function getTicket($iduser)
    {
        $query = "";

        if ($iduser) {
            $query = "SELECT idTicket, user, user.name, description, nameStatus 
                      FROM tickets, user, status 
                      WHERE user.document = tickets.user 
                      AND status.idStatus = tickets.idStatus 
                      AND user = ?";
        } else {
            $query = "SELECT idTicket, user, user.name, description, nameStatus 
                      FROM tickets, user, status 
                      WHERE user.document = tickets.user 
                      AND status.idStatus = tickets.idStatus";
        }

        $stmt = mysqli_prepare($this->mysql, $query);
        if ($iduser) {
            mysqli_stmt_bind_param($stmt, "s", $iduser);
        }
        mysqli_stmt_execute($stmt);
        $resultQuery = mysqli_stmt_get_result($stmt);

        $response = [];
        while ($row = mysqli_fetch_assoc($resultQuery)) {
            $response[] = $row;
        }

        if (!empty($response)) {
            http_response_code(200);
            echo json_encode($response);
        } else {
            http_response_code(200);
            echo json_encode([]);
        }
    }

    public function login($email, $password)
    {
        $response = array();

        $query = "SELECT user.document, user.email , name, user.password, rol.nameRol 
                  FROM user
                  INNER JOIN rol  ON user.rol = rol.id
                  WHERE user.email = ?";

        $stmt = mysqli_prepare($this->mysql, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if ($result->num_rows > 0) {
            $row = mysqli_fetch_assoc($result);
            $passwordHash = $row['password'];

            if (password_verify($password, $passwordHash)) {
                $response = $row;
                http_response_code(200);
            } else {
                $response['error'] = "Contraseña incorrecta";
                http_response_code(401);
            }
        }

        mysqli_stmt_close($stmt);

        echo json_encode($response);
    }

    public function editTicket($id, $status) {
        $query = "UPDATE tickets SET idStatus = ? WHERE idTicket = ?";
        $stmt = mysqli_prepare($this->mysql,$query);
        mysqli_stmt_bind_param($stmt, "ss", $status,$id);
        mysqli_stmt_execute($stmt);
        
        http_response_code(200);
        echo json_encode("ok");
    }
    
}