<?php
// Initialize the session
session_start();
 
// Check if the user is already logged in, if yes then redirect them to the main page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}
 
require_once 'includes/db_connect.php';
$login_err = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(empty(trim($_POST["school_id"]))){
        $login_err = "Please enter your School ID.";
    } else{
        $school_id = trim($_POST["school_id"]);
    }
    
    if(empty(trim($_POST["password"]))){
        $login_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    if(empty($login_err)){
        $sql = "SELECT user_id, school_id, password, role, first_name FROM users WHERE school_id = ?";
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("s", $school_id);
            if($stmt->execute()){
                $stmt->store_result();
                if($stmt->num_rows == 1){                    
                    $stmt->bind_result($id, $sid, $hashed_password, $role, $first_name);
                    if($stmt->fetch()){
                        if(password_verify($password, $hashed_password)){
                            session_regenerate_id(true);
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["school_id"] = $sid;
                            $_SESSION["role"] = $role;
                            $_SESSION["first_name"] = $first_name;
                            header("location: index.php");
                            exit;
                        } else{ $login_err = "Invalid School ID or password."; }
                    }
                } else{ $login_err = "Invalid School ID or password."; }
            } else{ echo "Oops! Something went wrong. Please try again later."; }
            $stmt->close();
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SPCF Lost and Found</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- LINK TO OUR NEW STYLESHEET -->
    <link href="assets/css/style.css" rel="stylesheet"> 
</head>
<body>
    <div class="login-container">
        <div class="card login-card p-4">
            <div class="card-body text-center">
                <img src="assets/img/logo.png" alt="SPCF Logo">
                <h3 class="card-title mt-3">SPCF Lost & Found</h3>
                <p class="text-muted">Please sign in to continue</p>

                <?php 
                if(!empty($login_err)){
                    echo '<div class="alert alert-danger">' . $login_err . '</div>';
                }        
                ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="text-start">
                    <div class="mb-3">
                        <label for="school_id" class="form-label">School ID Number</label>
                        <input type="text" name="school_id" class="form-control" id="school_id" required maxlength="10">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" id="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>