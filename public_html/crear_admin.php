<?php
require_once dirname(__DIR__) . '/private/db.php';

try {
    $pdo = db();
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Insertamos como 'master' según el ENUM
    $stmt = $pdo->prepare("INSERT INTO users (name, password_hash, role) VALUES ('admin', :hash, 'master')");
    $stmt->execute(['hash' => $hash]);
    
    echo "<h1>¡Usuario admin creado con éxito!</h1>";
    echo "<p>Usuario: <b>admin</b></p>";
    echo "<p>Contraseña: <b>admin123</b></p>";
    echo "<p><a href='/login'>Ir a Iniciar Sesión</a></p>";
    
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo "El usuario 'admin' ya existe en la base de datos.<br>";
        echo "<a href='/login'>Ir a Iniciar Sesión</a>";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
