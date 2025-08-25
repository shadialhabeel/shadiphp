<?php
// Start the session at the very beginning of the page
session_start();

// Check if the user is already logged in, redirect to view page
if (isset($_SESSION['user_id'])) {
    header("Location: view.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- Database connection ---
    $servername = "localhost";
    $username = "root"; // your database username
    $password = ""; // your database password
    $dbname = "university_project"; // your database name

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    // --- End of database connection ---
    
    // Get form data and sanitize it
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    // Basic validation
    if (empty($email) || empty($password)) {
        $error_message = "Please fill in all fields.";
    } else {
        // Use a prepared statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        // Check if a user with that email exists
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $username, $hashed_password);
            $stmt->fetch();

            // Verify the hashed password
            if (password_verify($password, $hashed_password)) {
                // Password is correct, start a session
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                
                // Redirect to the viewing page
                header("Location: view.php");
                exit();
            } else {
                $error_message = "Incorrect password.";
            }
        } else {
            $error_message = "No account found with that email.";
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #007bff;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 4px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .signup-link {
            margin-top: 15px;
            font-size: 14px;
        }
        .signup-link a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Login to Your Account</h2>

    <?php if (isset($error_message)): ?>
        <div class="message error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn">Login</button>
    </form>
    
    <div class="signup-link">
        Don't have an account? <a href="signup.php">Register here</a>
    </div>
</div>

</body>
</html>
