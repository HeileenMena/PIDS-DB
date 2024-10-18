<?php
include 'db_connection.php';  // Conexión a la base de datos

// Verifica que la solicitud sea POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recupera los valores enviados por el JavaScript
    $id = $_POST['id'];  // ID del registro
    $page = $_POST['page'];  // Página actual
    $data = [];

    // Dependiendo de la página, recibimos diferentes campos
    switch ($page) {
        case 'SErbu':
            $table = 'serburecords';
            $data['chasis'] = $_POST['chasis'];
            $data['fan'] = $_POST['fan'];
            $data['power'] = $_POST['power'];
            $data['rsp'] = $_POST['rsp'];
            $data['fc'] = $_POST['fc'];
            break;
        case 'SFretta':
            $table = 'sfrettarecords';
            $data['chasis'] = $_POST['chasis'];
            $data['fan'] = $_POST['fan'];
            $data['power'] = $_POST['power'];
            break;
        case 'SInsbu':
            $table = 'sinsburecords';
            $data['chasis'] = $_POST['chasis'];
            $data['fan'] = $_POST['fan'];
            $data['power'] = $_POST['power'];
            break;
        case 'SPabu':
            $table = 'spaburecords';
            $data['chasis'] = $_POST['chasis'];
            $data['fan'] = $_POST['fan'];
            $data['power'] = $_POST['power'];
            $data['rsp'] = $_POST['rsp'];
            $data['ima'] = $_POST['ima'];
            break;
        case 'TestingPathPabu':
            $table = 'testpathrecords';
            $data['pid'] = $_POST['pid'];
            $data['sysassy'] = $_POST['sysassy'];
            $data['syshipot'] = $_POST['syshipot'];
            $data['sysft'] = $_POST['sysft'];
            $data['test_station'] = $_POST['test_station'];
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Página no soportada.']);
            exit;
    }

    // Realiza la consulta SQL de actualización
    $setClause = "";
    foreach ($data as $key => $value) {
        // Aseguramos de escapar los valores para evitar inyecciones SQL
        $value = $conn->real_escape_string($value);
        $setClause .= "$key = '$value', ";
    }
    $setClause = rtrim($setClause, ', ');

    $sql = "UPDATE $table SET $setClause WHERE id = '$id'";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Registro actualizado con éxito.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el registro: ' . $conn->error]);
    }

    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método de solicitud no permitido.']);
}
?>
