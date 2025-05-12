<?php
session_start(); 


session_regenerate_id(true);


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit; 
}


include 'config.php';


if (isset($_POST['campaign_id'], $_POST['amount']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id']; // Get the logged-in user's ID
    $campaign_id = $_POST['campaign_id']; // Campaign selected by the user
    $amount = $_POST['amount']; // Amount pledged by the user

    // Prepare the SQL statement to insert into the support table
    $sql = "INSERT INTO support (user_id, campaign_id, amount) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Bind parameters (i = integer, i = integer, d = double/decimal)
    $stmt->bind_param("iid", $user_id, $campaign_id, $amount);

    // Execute the statement and check for errors
    if ($stmt->execute()) {
        // Step 1: Fetch the current raised amount for the campaign
        $sql_fetch = "SELECT raised_amount FROM campaigns WHERE id = ?";
        $stmt_fetch = $conn->prepare($sql_fetch);
        $stmt_fetch->bind_param("i", $campaign_id);
        $stmt_fetch->execute();
        $stmt_fetch->bind_result($raised_amount);
        $stmt_fetch->fetch();
        $stmt_fetch->close();

        // Step 2: Add the new amount to the raised amount
        $new_raised_amount = $raised_amount + $amount;

        // Step 3: Update the campaign's raised amount
        $sql_update = "UPDATE campaigns SET raised_amount = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("di", $new_raised_amount, $campaign_id);

        if ($stmt_update->execute()) {
            // Redirect to thank you page after successful submission
            header("Location: thank_you.php");
            exit; // Ensure no further code is executed after redirection
        } else {
            // Error if updating the campaign fails
            echo "Error updating campaign: " . $stmt_update->error;
        }

        $stmt_update->close(); // Close the prepared statement
    } else {
        // For debugging purposes
        echo "Error: " . $stmt->error;
    }

    $stmt->close(); // Close the prepared statement
} else {
    // If form data or user is not logged in, show an error
    echo "All fields are required or user is not logged in.";
}

$conn->close(); // Close the database connection
?>
