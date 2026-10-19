<?php
/**
 * Database Auto-Installer
 * Visit: http://localhost/smart-parking-system/config/db-init.php
 * 
 * WARNING: Delete this file after installation in production!
 */

$host = 'localhost';
$user = 'root';
$pass = '';

function executeSqlScript(PDO $pdo, string $sql): void {
    if (!preg_match('/^\s*DELIMITER\s+(\S+)\s*$/mi', $sql, $matches)) {
        $pdo->exec($sql);
        return;
    }

    $delimiter = $matches[1];
    $sql = preg_replace('/^\s*DELIMITER\s+\S+\s*$/mi', '', $sql);
    foreach (explode($delimiter, $sql) as $statement) {
        if (trim($statement) !== '') {
            $result = $pdo->query($statement);
            if ($result !== false) {
                do {
                    if ($result->columnCount() > 0) {
                        $result->fetchAll();
                    }
                } while ($result->nextRowset());
                $result->closeCursor();
            }
        }
    }
}

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $schemaFile = __DIR__ . '/../database/schema.sql';
    $seedFile = __DIR__ . '/../database/seed.sql';
    $viewsFile = __DIR__ . '/../database/views.sql';
    $procFile = __DIR__ . '/../database/procedures.sql';
    $trigFile = __DIR__ . '/../database/triggers.sql';

    echo "<pre style='font-family: monospace; padding: 20px; background: #f4f4f4;'>";
    echo "🚀 Smart Parking DB Installer\n";
    echo "================================\n\n";

    // Read and execute schema
    echo "📦 Creating schema...\n";
    $sql = file_get_contents($schemaFile);
    executeSqlScript($pdo, $sql);
    echo "✅ Schema created successfully\n\n";

    // Reconnect to smart_parking DB
    $pdo = new PDO("mysql:host=$host;dbname=smart_parking", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Seed data
    echo "🌱 Inserting seed data...\n";
    $sql = file_get_contents($seedFile);
    executeSqlScript($pdo, $sql);
    echo "✅ Seed data inserted\n\n";

    // Views
    echo "👁️  Creating views...\n";
    $sql = file_get_contents($viewsFile);
    executeSqlScript($pdo, $sql);
    echo "✅ Views created\n\n";

    // Procedures
    echo "⚙️  Creating stored procedures...\n";
    $sql = file_get_contents($procFile);
    executeSqlScript($pdo, $sql);
    echo "✅ Stored procedures created\n\n";

    // Triggers
    echo "🔔 Creating triggers...\n";
    $sql = file_get_contents($trigFile);
    executeSqlScript($pdo, $sql);
    echo "✅ Triggers created\n\n";

    // Verify
    echo "📊 Database Summary:\n";
    echo "--------------------\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo sprintf("  %-35s %5d rows\n", $t, $count);
    }

    echo "\n================================\n";
    echo "🎉 Installation Complete!\n";
    echo "================================\n\n";
    echo "Admin Login:\n";
    echo "  Email:    admin@smartpark.com\n";
    echo "  Password: password123\n\n";
    echo "User Login:\n";
    echo "  Email:    rahul@example.com\n";
    echo "  Password: password123\n\n";
    echo "⚠️  DELETE this file now for security.\n";
    echo "</pre>";

} catch (PDOException $e) {
    echo "<pre style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</pre>";
}
?>