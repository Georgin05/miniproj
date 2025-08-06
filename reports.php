<?php
include 'config/db.php';
session_start();

// Get last 7 days of inbound
$inbound_data = $conn->query("
    SELECT DATE(received_at) AS date, SUM(quantity) AS total 
    FROM inbound 
    GROUP BY DATE(received_at) 
    ORDER BY DATE(received_at) DESC LIMIT 7
");

// Get last 7 days of outbound
$outbound_data = $conn->query("
    SELECT DATE(dispatched_at) AS date, SUM(quantity) AS total 
    FROM outbound 
    GROUP BY DATE(dispatched_at) 
    ORDER BY DATE(dispatched_at) DESC LIMIT 7
");

$dates = [];
$inbound = [];
$outbound = [];

while ($row = $inbound_data->fetch_assoc()) {
    $dates[] = $row['date'];
    $inbound[] = $row['total'];
}

while ($row = $outbound_data->fetch_assoc()) {
    $outbound[] = $row['total'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Warehouse Reports</title>
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="container mt-4">
    <h2>📊 Warehouse Reports</h2>

    <canvas id="reportChart" width="600" height="300"></canvas>

    <script>
        const ctx = document.getElementById('reportChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_reverse($dates)) ?>,
                datasets: [
                    {
                        label: 'Inbound',
                        backgroundColor: 'green',
                        data: <?= json_encode(array_reverse($inbound)) ?>
                    },
                    {
                        label: 'Outbound',
                        backgroundColor: 'red',
                        data: <?= json_encode(array_reverse($outbound)) ?>
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>

    <a href="dashboard.php" class="btn btn-secondary mt-3">← Back to Dashboard</a>
</body>
</html>
