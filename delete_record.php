<?php
include 'db_connection.php';  // Incluye la conexión a la base de datos

// Verifica que la solicitud sea POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recupera el ID del registro a eliminar
    $id = $_POST['id'];  // ID del registro
    $page = $_POST['page'];  // Página actual

    // SQL para eliminar el registro
    $sql = "DELETE FROM serburecords WHERE id = '$id'";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Registro eliminado con éxito.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el registro: ' . $conn->error]);
    }

    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método de solicitud no permitido.']);
}
?>
