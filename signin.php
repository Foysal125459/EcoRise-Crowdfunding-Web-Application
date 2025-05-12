<?php
session_start(); // Start the session


if(isset($_SESSION['signup_success'])) 
{
    $signup_message = $_SESSION['signup_success'];
    unset($_SESSION['signup_success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'config.php'; // Database connection

    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare SQL query to get user data
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check if the account is inactive first
        if ($user['user_status'] === 'inactive') {
            $error = "Your account is inactive. Please contact support.";
        } else {
            // Verify password only if account is active
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user['id']; // Store user ID
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];

                // Redirect to appropriate page based on user type
                if ($user['user_type'] === 'admin') {
                    header("Location: admin_homepage.php");
                } else {
                    header("Location: homepage.php");
                }
                exit();
            } else {
                $error = "Invalid password. Please try again.";
            }
        }
    } else {
        $error = "No account found with that email address.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="stylesheet" href="signin.css"> <!-- Link external CSS -->
</head>
<body>

<!-- Success Message Popup (Hidden by Default) -->
<div id="signupSuccessPopup" style="display:none; position:fixed; top:10px; left:50%; transform:translateX(-50%); background-color:rgb(40, 168, 70); color: white; padding: 15px; border-radius: 5px;">
    <?php echo $signup_message; ?>
</div>

<script>
// Check if the PHP variable has content
<?php if (!empty($signup_message)) { ?>
    // Show the success message popup
    document.getElementById('signupSuccessPopup').style.display = 'block';
    
    // Hide the popup after 5 seconds
    setTimeout(function() {
        document.getElementById('signupSuccessPopup').style.display = 'none';
    }, 3000);
<?php } ?>
</script>

<div class="logo-container">
        <img src="assets/logo/eco.png" alt="EcoRise Logo"> <!-- Add your logo here -->
</div>

<div class="signin-container">
    <h2>Sign In</h2>

    <?php if (!empty($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="signin.php" method="POST">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required>

        <button type="submit">Sign In</button>

        <div class="form-footer">
            <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
        </div>
    </form>
</div>
</body>
</html>
