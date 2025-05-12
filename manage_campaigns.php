<?php
include('config.php'); // Include database connection

// Handle campaign deletion
if (isset($_GET['id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Delete the campaign from the database
    $deleteQuery = "DELETE FROM campaigns WHERE id = '$delete_id'";
    if (mysqli_query($conn, $deleteQuery)) {
        echo "<script>alert('Campaign deleted successfully!'); window.location.href = 'manage_campaigns.php';</script>";
    } else {
        echo "<script>alert('Error deleting campaign. Please try again.'); window.location.href = 'manage_campaigns.php';</script>";
    }
}

// Check if the form is submitted to update the campaign
if (isset($_POST['campaign_id'])) {
    // Get the values from the form
    $campaign_id = mysqli_real_escape_string($conn, $_POST['campaign_id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $goal = mysqli_real_escape_string($conn, $_POST['goal']);
    $target_amount = mysqli_real_escape_string($conn, $_POST['target_amount']);

    // Update the campaign in the database
    $updateQuery = "
        UPDATE campaigns
        SET title = '$title', goal = '$goal', target_amount = '$target_amount'
        WHERE id = '$campaign_id'
    ";
    if (mysqli_query($conn, $updateQuery)) {
        echo "<script>alert('Campaign updated successfully!'); window.location.href = 'manage_campaigns.php';</script>";
    } else {
        echo "<script>alert('Error updating campaign. Please try again.');</script>";
    }
}

// Search query if search is applied
$searchQuery = '';
if (isset($_POST['search'])) {
    $search = mysqli_real_escape_string($conn, $_POST['search']);
    $searchQuery = "WHERE title LIKE '%$search%' OR goal LIKE '%$search%' OR target_amount LIKE '%$search%'";
}

// Fetch campaigns
$query = "
    SELECT id, title, goal, target_amount
    FROM campaigns
    $searchQuery
    ORDER BY created_at DESC
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Campaigns</title>
    <link rel="stylesheet" href="manage_campaigns.css">
</head>

<body>
    <div class="admin-container">
        <!-- Header -->
        <div class="admin-header">
            <h1>Manage Campaigns</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <!-- Search Section -->
        <div class="search-container">
            <form method="POST">
                <input type="text" name="search" placeholder="Search by Title or Goal"
                    value="<?php echo isset($_POST['search']) ? $_POST['search'] : ''; ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <!-- Campaign Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Goal</th>
                        <th>Target Amount</th>
                        <th class="actions-column">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($campaign = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?php echo $campaign['id']; ?></td>
                            <td><?php echo $campaign['title']; ?></td>
                            <td><?php echo $campaign['goal']; ?></td>
                            <td>$<?php echo number_format($campaign['target_amount'], 2); ?></td>
                            <td>
                                <button class="edit-btn action-btn"
                                    onclick="openModal('<?php echo $campaign['id']; ?>', '<?php echo $campaign['title']; ?>', '<?php echo $campaign['goal']; ?>', '<?php echo $campaign['target_amount']; ?>')">Edit</button>
                                <a href="manage_campaigns.php?id=<?php echo $campaign['id']; ?>"
                                    class="delete-btn action-btn"
                                    onclick="return confirm('Are you sure you want to delete this campaign?');">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Campaign Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <div class="modal-header">
                Edit Campaign
            </div>
            <form id="editForm" action="manage_campaigns.php" method="POST">
                <input type="hidden" name="campaign_id" id="campaign_id">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title">

                <label for="goal">Goal:</label>
                <input type="text" id="goal" name="goal">

                <label for="target_amount">Target Amount:</label>
                <input type="text" id="target_amount" name="target_amount">

                <button type="submit">Done</button>
            </form>
        </div>
    </div>

    <script>
        // Open the modal and populate the form fields
        function openModal(id, title, goal, targetAmount) {
            document.getElementById("campaign_id").value = id;
            document.getElementById("title").value = title;
            document.getElementById("goal").value = goal;
            document.getElementById("target_amount").value = targetAmount;
            document.getElementById("editModal").style.display = "block";
        }

        // Close the modal
        function closeModal() {
            document.getElementById("editModal").style.display = "none";
        }

        // Close modal if clicked outside
        window.onclick = function (event) {
            if (event.target == document.getElementById("editModal")) {
                closeModal();
            }
        }
    </script>
</body>

</html>