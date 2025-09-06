<?php
// admin/queue-logs.php - Simple log viewer

$logFile = __DIR__ . '/../storage/logs/queue-cron.log';

if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    echo "<h3>Queue Processing Logs</h3>";
    echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
    echo htmlspecialchars($logs);
    echo "</pre>";
    
    echo "<br><a href='?clear=1'>Clear Logs</a>";
    
    if (isset($_GET['clear'])) {
        file_put_contents($logFile, '');
        echo "<br><strong>Logs cleared!</strong>";
    }
} else {
    echo "No log file found.";
}
?>
