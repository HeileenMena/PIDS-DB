<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json'); // Asegúrate de que el contenido sea JSON

// Leer los datos del cuerpo de la solicitud
parse_str(file_get_contents("php://input"), $data);

// Verificar si se recibieron los datos
if (isset($data['id']) && isset($data['page'])) {
    $id = $data['id'];
    $page = $data['page'];

    // Conexión a la base de datos
    $conn = new mysqli('localhost', 'root', '', 'pids');

    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']));
    }

    // Sanitizar el nombre de la tabla para evitar SQL Injection
    $tableNames = [
        'SErbu' => 'serburecords',
        'SFretta' => 'sfrettarecords',
        'SInsbu' => 'sinsburecords',
        'SPabu' => 'spaburecords',
        'TestingPathPabu' => 'testpathrecords'
    ];

    if (!array_key_exists($page, $tableNames)) {
        echo json_encode(['success' => false, 'message' => 'Página no válida.']);
        exit;
    }

    $tableName = $tableNames[$page]; // Obtener el nombre de la tabla

    // Realizar la consulta para obtener los datos del registro
    $query = "SELECT * FROM $tableName WHERE id = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $record = $result->fetch_assoc();

            // Depuración: registrar el contenido del registro
            error_log(print_r($record, true)); // Esto registrará en el log sin afectar la respuesta

            // Convertir solo si el valor es numérico
            foreach ($record as $key => $value) {
                if (is_numeric($value)) {
                    $record[$key] = (int)$value; // Convertir a entero solo si es numérico
                }
            }

            echo json_encode(['success' => true, 'record' => $record]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontraron los datos del registro.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error de preparación de consulta: ' . $conn->error]);
    }

    // Cerrar la conexión
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
}
?>
