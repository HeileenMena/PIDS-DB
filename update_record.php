<?php
// Mostrar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "pids";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['table'])) {
    $recordId = (int)$_POST['id'];
    $tableName = $_POST['table'];

    // Crear la consulta de actualización según la tabla
    $fields = [];
    switch ($tableName) {
        case 'SFretta':
            $fields = ['id', 'chasis', 'fan', 'power'];
            break;
        case 'SErbu':
            $fields = ['id', 'chasis', 'fan', 'power', 'rsp', 'fc'];
            break;
        case 'SInsbu':
            $fields = ['id', 'chasis', 'fan', 'power'];
            break;
        case 'Spabu':
            $fields = ['id', 'chasis', 'fan', 'power', 'rsp', 'ima'];
            break;
        case 'TestingPathPabu':
            $fields = ['id', 'pid', 'sysassy', 'syshipot', 'sysft', 'test_station'];
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Tabla no permitida']);
            exit;
    }

    // Construir el SQL dinámicamente según los campos
    $setClause = '';
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $setClause .= "$field = ?, ";
        }
    }
    $setClause = rtrim($setClause, ", ");  // Eliminar la coma final

    $sql = "UPDATE $tableName SET $setClause WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $types = str_repeat('s', count($fields)) . 'i'; // Asumiendo que los campos son strings y el ID es int
        $params = [];
        foreach ($fields as $field) {
            $params[] = $_POST[$field];
        }
        $params[] = $recordId; // Añadir el ID al final
        call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $params));

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Registro actualizado']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el registro']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Faltan parámetros']);
}

$conn->close();
?>
