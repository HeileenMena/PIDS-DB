<?php
// fetch_data1.php

// Leer los datos del cuerpo de la solicitud
parse_str(file_get_contents("php://input"), $data);

// Verificar si se recibieron los datos
if (isset($data['id']) && isset($data['page'])) {
    $id = $data['id'];
    $page = $data['page'];

    // Conexión a la base de datos (ajusta según tus parámetros de conexión)
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

    // Obtener el nombre de la tabla correspondiente
    $tableName = $tableNames[$page];
    // Depuración
    error_log("ID recibido: $id");
    error_log("Página recibida: $page");

    
    // Realizar la consulta para obtener los datos del registro
    $query = "SELECT * FROM $tableName WHERE id = ?";

    // Preparar la consulta
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param('i', $id);  // El ID debe ser un entero
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $record = $result->fetch_assoc();  // Obtener los datos del registro
            // Asegurarse de que los booleanos sean representados como enteros
            $record['SYSASSY'] = (int)$record['SYSASSY'];
            $record['SYSHIPOT'] = (int)$record['SYSHIPOT'];
            $record['SYSFT'] = (int)$record['SYSFT'];
            
            echo json_encode(['success' => true, 'record' => $record]);  // Devolver el registro en JSON
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontraron los datos del registro.']);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error de preparación de consulta.']);
    }

    // Cerrar la conexión
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
}
?>
