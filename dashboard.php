<?php
// Start the session to get user info
session_start();

// Assuming user is already logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit();
}

// Database connection
require 'config.php'; // include your DB connection file

$user_id = $_SESSION['user_id'];

// Fetch user information
$sql_user = "SELECT  name, email FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user_info = $user_result->fetch_assoc();

// Fetch the campaigns the user has supported
$sql_support = "
    SELECT campaigns.id AS campaign_id, campaigns.goal, support.amount, campaigns.title
    FROM support
    JOIN campaigns ON support.campaign_id = campaigns.id
    WHERE support.user_id = ?
";
$stmt_support = $conn->prepare($sql_support);
$stmt_support->bind_param("i", $user_id);
$stmt_support->execute();
$support_result = $stmt_support->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="dashboard.css"> 
</head>
<body>
    <div class="header">
        <h1>Welcome, <?php echo $user_info['name']; ?>!</h1>
        <p>Email: <?php echo $user_info['email']; ?></p>
    </div>

    <div class="container">
        <div class="user-info">
            <h2>Your Supported Campaigns</h2>
            <table>
                <thead>
                    <tr>
                        <th>Campaign Goal</th>
                        <th>Title</th>
                        <th>Amount Donated</th>
                        <th>Support More</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($support_result->num_rows > 0) {
                        while ($row = $support_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['goal']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                            echo "<td>$" . htmlspecialchars($row['amount']) . "</td>";
                            echo "<td><button class='support-button'><a href='support.php?campaign_id=" . $row['campaign_id'] . "'>Support More</a></button></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>You have not supported any campaigns yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
$stmt_user->close();
$stmt_support->close();
$conn->close();
?>
