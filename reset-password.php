<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include config file
require_once "conn.php";

// Define variables and initialize with empty values
$new_password = $confirm_password = $newpassword = "";
$new_password_err = $confirm_password_err = "";


// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    //$new_password = $_POST["new_password"];
    
    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Please enter the new password.";
    } elseif (strlen(trim($_POST["new_password"])) < 6) {
        $new_password_err = "Password must have atleast 6 characters.";
    } //elseif (empty($new_password_err) ) {
        //$new_password_err = "Please enter another password.";} 
    else {
        $new_password = trim($_POST["new_password"]);
    }
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm the password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($new_password_err) && ($new_password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    
    }
    // Check input errors before updating the database
    if (empty($new_password_err) && empty($confirm_password_err)) {
        // Prepare an update statement
        $sq1 = "UPDATE users SET password = ? WHERE no = ?";
		$sq2 = "SELECT no, username, password FROM users WHERE no = ?";
		
		
		if ($stmt = mysqli_prepare($link, $sq2)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $param_id);

            // Set parameters
            $param_id = $_SESSION["id"];

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Store result
                mysqli_stmt_store_result($stmt);

                // Check if username exists, if yes then verify password
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($new_password, $hashed_password)) {
                            // Redirect user to welcome page
							echo "Passwordnya sama bambang!";
							// Close statement
							mysqli_stmt_close($stmt);
                        } elseif ($stmt = mysqli_prepare($link, $sq1)) {
							// Bind variables to the prepared statement as parameters
							mysqli_stmt_bind_param($stmt, "si", $param_password, $param_id);

							// Set parameters
							$param_password = password_hash($new_password, PASSWORD_DEFAULT);
							$param_id = $_SESSION["id"];

							// Attempt to execute the prepared statement
							if (mysqli_stmt_execute($stmt)) {
								// Password updated successfully. Destroy the session, and redirect to login page
								session_destroy();
								header("location: login.php");
								exit();
							} else {
								echo "Oops! Something went wrong. Please try again later.";
							}
						}
                    }
                }
            }
        }
    }
    // Close connection
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body {
            font: 14px sans-serif;
        }

        .wrapper {
            width: 350px;
            padding: 20px;
        }
    </style>
</head>

<body>
    <center>
    <div class="wrapper">
        <h2>Reset Password</h2>
        <p style="float: left;">Please fill out this form to reset your password.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
           <!-- <div class="form-group <?php echo (!empty($new_password_err)) ? 'has-error' : ''; ?>">
                <label style="float: left;">Old Password</label>
                <input type="password" name="old_password" class="form-control">
                <span class="help-block"><?php echo $new_password_err; ?></span>
            </div> -->
            <div class="form-group <?php echo (!empty($new_password_err)) ? 'has-error' : ''; ?>">
                <label style="float: left;">New Password</label>
                <input type="password" name="new_password" class="form-control" value="<?php echo $new_password; ?>">
                <span class="help-block"><?php echo $new_password_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                <label style="float: left;">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control">
                <span class="help-block"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <a class="btn btn-link" href="welcome.php">Cancel</a>
            </div>
        </form>
    </div>
    </center>
</body>

</html>
