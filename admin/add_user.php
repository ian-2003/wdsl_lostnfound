<?php
require_once 'admin_header.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$school_id = $first_name = $last_name = $role = $password = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $school_id = trim($_POST['school_id']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $role = $_POST['role'];
    $password = trim($_POST['password']);

    if (empty($school_id) || empty($first_name) || empty($last_name) || empty($role) || empty($password)) { $error_msg = "All fields are required."; }
    elseif (strlen($school_id) != 10 || !ctype_digit($school_id)) { $error_msg = "School ID must be exactly 10 digits."; }
    elseif (strlen($password) < 6) { $error_msg = "Password must be at least 6 characters long."; }
    else {
        $sql_check = "SELECT user_id FROM users WHERE school_id = ?";
        if ($stmt_check = $conn->prepare($sql_check)) {
            $stmt_check->bind_param("s", $school_id);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows > 0) { $error_msg = "This School ID is already registered."; }
            $stmt_check->close();
        }
    }

    if (empty($error_msg)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql_insert = "INSERT INTO users (school_id, first_name, last_name, role, password) VALUES (?, ?, ?, ?, ?)";
        if ($stmt_insert = $conn->prepare($sql_insert)) {
            $stmt_insert->bind_param("sssss", $school_id, $first_name, $last_name, $role, $hashed_password);
            if ($stmt_insert->execute()) {
                $new_user_id = $stmt_insert->insert_id;
                log_system_action($conn, "Created new user: " . $first_name . " " . $last_name . " (User ID: " . $new_user_id . ")");
                $_SESSION['message'] = "User account created successfully!";
                $_SESSION['message_type'] = "success";
                header("location: manage_users.php");
                exit();
            } else { $error_msg = "Something went wrong. Please try again."; }
            $stmt_insert->close();
        }
    }
}
?>
<main class="container mt-4">
    <h1>Add New User</h1>
    <p>Create a new account for a student, staff, or administrator.</p>
    <div class="card">
        <div class="card-body">
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
            <?php endif; ?>
            <form action="add_user.php" method="POST">
                <div class="mb-3">
                    <label for="school_id" class="form-label">School ID (10 digits)</label>
                    <input type="text" class="form-control" id="school_id" name="school_id" required maxlength="10">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                </div>
                 <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <small class="form-text text-muted">Minimum 6 characters.</small>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="student">Student</option>
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <hr>
                <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create User</button>
            </form>
        </div>
    </div>
</main>
<?php
$conn->close();
require_once '../includes/footer.php';
?>