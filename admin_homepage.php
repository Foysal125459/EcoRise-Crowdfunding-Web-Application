<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'admin') {
    // Redirect to the sign-in page if not authenticated or not an admin
    header("Location: signin.php");
    exit();
}

include 'config.php'; // Include database connection

// Fetch admin information from session
$admin_name = $_SESSION['name'];

// Fetch statistics from the database
// Total Campaigns
$total_campaigns = $conn->query("SELECT COUNT(*) AS total_campaigns FROM campaigns")->fetch_assoc()['total_campaigns'];

// Total Users
$total_users = $conn->query("SELECT COUNT(*) AS total_users FROM users WHERE user_type='user'")->fetch_assoc()['total_users'];

// Total Donations (sum of 'amount' column in 'support' table)
$total_donations = $conn->query("SELECT SUM(amount) AS total_donations FROM support")->fetch_assoc()['total_donations'];

// Fallback if no donations exist
$total_donations = $total_donations ? $total_donations : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_homepage.css"> <!-- Link to external CSS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js -->
</head>
<body>

<div class="admin-container">
    <!-- Admin Header -->
    <header class="admin-header">
        <h1>Welcome, <?php echo htmlspecialchars($admin_name); ?></h1>
        <a href="logout.php" class="logout-btn">Log Out</a>
    </header>

    <!-- Admin Navigation -->
    <nav class="admin-nav">
        <ul>
            <li><a href="manage_campaigns.php">Manage Campaigns</a></li>
            <li><a href="view_users.php">Manage Users</a></li>
            <li><a href="add_campaign.php">Add New Campaign</a></li>
        </ul>
    </nav>

    <!-- Main Dashboard Content -->
    <main class="admin-main">
        <h2>Dashboard Overview</h2>

        <!-- Dashboard Stats -->
        <div class="dashboard-stats">
            <div class="stat-box">
                <h3>Total Campaigns</h3>
                <p><?php echo $total_campaigns; ?></p>
            </div>
            <div class="stat-box">
                <h3>Total Users</h3>
                <p><?php echo $total_users; ?></p>
            </div>
            <div class="stat-box">
                <h3>Total Donations</h3>
                <p>$<?php echo number_format($total_donations, 2); ?></p>
            </div>
        </div>

        <!-- Chart.js Section -->
        <div class="chart-container">
    <!-- Total Campaigns Chart -->
    <div class="chart-wrapper">
        <canvas id="campaignsChart"></canvas>
        <p>Total Campaigns</p>
    </div>

    <!-- Total Users Chart -->
    <div class="chart-wrapper">
        <canvas id="usersChart"></canvas>
        <p>Total Users</p>
    </div>

    <!-- Total Donations Chart -->
    <div class="chart-wrapper">
        <canvas id="donationsChart"></canvas>
        <p>Total Donations</p>
    </div>
</div>

    </main>
</div>

<script>
    // Chart for Total Campaigns
    // Chart for Total Campaigns
// Chart for Total Campaigns
const campaignsCtx = document.getElementById('campaignsChart').getContext('2d');
new Chart(campaignsCtx, {
    type: 'bar', // Bar Chart
    data: {
        labels: ['Total Campaigns'],
        datasets: [{
            label: 'Total Campaigns',
            data: [<?php echo $total_campaigns; ?>],
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                max: 50 // Maximum limit for Total Campaigns
            }
        }
    }
});

// Chart for Total Users
const usersCtx = document.getElementById('usersChart').getContext('2d');
new Chart(usersCtx, {
    type: 'bar', // Bar Chart
    data: {
        labels: ['Total Users'],
        datasets: [{
            label: 'Total Users',
            data: [<?php echo $total_users; ?>],
            backgroundColor: 'rgba(75, 192, 192, 0.7)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                max: 50 // Maximum limit for Total Users
            }
        }
    }
});

// Chart for Total Donations
const donationsCtx = document.getElementById('donationsChart').getContext('2d');
new Chart(donationsCtx, {
    type: 'bar', // Bar Chart
    data: {
        labels: ['Total Donations'],
        datasets: [{
            label: 'Total Donations',
            data: [<?php echo $total_donations; ?>],
            backgroundColor: 'rgba(255, 99, 132, 0.7)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                max: 50000 // Maximum limit for Total Donations
            }
        }
    }
});

</script>

</body>
</html>
