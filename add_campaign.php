<?php
// Include database connection
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form input data
    $title = $_POST['title']; // Get the campaign title
    $goal = $_POST['goal'];
    $target_amount = $_POST['target_amount'];
    $raised_amount = $_POST['raised_amount'];
    
    // Check for duplicate campaign
    $check_sql = "SELECT COUNT(*) FROM campaigns WHERE title = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $title);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        echo '<div class="message-box error">Campaign with this title already exists.</div>';
    } else {
        // Image upload handling
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            // Get image details
            $image = $_FILES['image'];
            $image_name = uniqid('image', true) . '.' . pathinfo($image['name'], PATHINFO_EXTENSION);
            $image_tmp_name = $image['tmp_name'];
            $image_size = $image['size'];
            
            // Define allowed file types
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

            // Check if the file type is allowed
            if (in_array($image_ext, $allowed_types)) {
                // Check if the file size is within the limit (5MB)
                if ($image_size <= 5000000) { // 5MB
                    // Set the upload directory
                    $upload_dir = 'assets/'; // Corrected folder name
                    
                    // Create the directory if it doesn't exist
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    // Move the uploaded file to the assets folder
                    if (move_uploaded_file($image_tmp_name, $upload_dir . $image_name)) {
                        // Success, store the image URL in the database
                        $image_url = $upload_dir . $image_name; // e.g., 'assets/image12345.png'

                        // Prepare SQL query to insert data
                        $sql = "INSERT INTO campaigns (title, goal, target_amount, raised_amount, image_url, created_at) 
                                VALUES (?, ?, ?, ?, ?, NOW())";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ssdds", $title, $goal, $target_amount, $raised_amount, $image_url);

                        // Execute query
                        if ($stmt->execute()) {
                            echo '<div class="message-box success">Campaign added successfully!</div>';
                        } else {
                            echo '<div class="message-box error">Error: ' . htmlspecialchars($stmt->error) . '</div>';
                        }

                        $stmt->close();
                    } else {
                        echo '<div class="message-box error">Failed to upload image.</div>';
                    }
                } else {
                    echo '<div class="message-box error">Image size exceeds the limit of 5MB.</div>';
                }
            } else {
                echo '<div class="message-box error">Invalid image type. Only JPG, JPEG, PNG, and GIF files are allowed.</div>';
            }
        } else {
            echo '<div class="message-box error">No image uploaded or an error occurred during upload.</div>';
        }
    }

    // Close the connection
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Campaign</title>
    <link rel="stylesheet" href="add_campaign.css"> <!-- Link to the CSS file -->
</head>
<body>
    <h1>Add New Campaign</h1>
    <form action="add_campaign.php" method="POST" enctype="multipart/form-data">
        <label for="title">Campaign Title:</label>
        <input type="text" name="title" id="title" required><br><br>

        <label for="goal">Goal:</label>
        <input type="text" name="goal" id="goal" required><br><br>

        <label for="target_amount">Target Amount:</label>
        <input type="number" name="target_amount" id="target_amount" required><br><br>

        <label for="raised_amount">Raised Amount:</label>
        <input type="number" name="raised_amount" id="raised_amount" required><br><br>

        <label for="image">Campaign Image:</label>
        <input type="file" name="image" id="image" accept="image/*" required><br><br>

        <button type="submit">Add Campaign</button>
    </form>
</body>
</html>
