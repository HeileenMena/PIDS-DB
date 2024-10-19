<?php
// Mostrar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Parámetros de conexión a la base de datos
$servername = "localhost"; // o la dirección de tu servidor
$username = "root"; 
$password = ""; 
$dbname = "pids";

// Crear conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

// Comprobar si la conexión fue exitosa
if ($conn->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
}

// Verificar si los parámetros necesarios están presentes en la solicitud POST
if (isset($_POST['id']) && isset($_POST['table'])) {
    // Obtener el ID y el nombre de la tabla desde los parámetros POST
    $recordId = (int)$_POST['id'];
    $tableName = $_POST['table'];

    // Mapeo de páginas a tablas
    $tableMap = [
        'SErbu' => 'serburecords',
        'SFretta' => 'sfrettarecords',
        'SInsbu' => 'sinsburecords',
        'SPabu' => 'spaburecords',
        'TestingPathPabu' => 'testpathrecords'
    ];

    // Verificar que el nombre de la tabla es válido
    if (array_key_exists($tableName, $tableMap)) {
        // Obtener el nombre real de la tabla a partir del mapeo
        $realTableName = $tableMap[$tableName];

        // Validar el nombre de la tabla para evitar inyecciones SQL (caracteres alfanuméricos y guiones bajos)
        if (preg_match('/^[a-zA-Z0-9_]+$/', $realTableName)) {
            // Crear la consulta SQL para eliminar el registro de la tabla correspondiente
            $sql = "DELETE FROM $realTableName WHERE id = ?";

            // Preparar la consulta
            if ($stmt = $conn->prepare($sql)) {
                // Vincular el parámetro (recordId) a la consulta
                $stmt->bind_param('i', $recordId);

                // Ejecutar la consulta
                if ($stmt->execute()) {
                    // Verificar si se eliminó algún registro
                    if ($stmt->affected_rows > 0) {
                        // Responder con éxito
                        echo json_encode(['success' => true, 'message' => 'Registro eliminado con éxito']);
                    } else {
                        // Si no se eliminó ningún registro, responder con error
                        echo json_encode(['success' => false, 'message' => 'No se encontró el registro para eliminar']);
                    }
                } else {
                    // Error al ejecutar la consulta
                    echo json_encode(['success' => false, 'message' => 'Error al ejecutar la consulta']);
                }

                // Cerrar la declaración
                $stmt->close();
            } else {
                // Error al preparar la consulta
                echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta']);
            }
        } else {
            // Nombre de tabla no válido
            echo json_encode(['success' => false, 'message' => 'Nombre de tabla no válido']);
        }
    } else {
        // Si el nombre de la tabla no está en el mapeo
        echo json_encode(['success' => false, 'message' => 'Tabla no permitida']);
    }
} else {
    // Si faltan parámetros
    echo json_encode(['success' => false, 'message' => 'Faltan parámetros']);
}

// Cerrar la conexión
$conn->close();
?>
