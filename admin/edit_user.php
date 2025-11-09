<?php
require_once 'admin_header.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user = null;
$error_msg = "";

if ($user_id <= 0) { header("location: manage_users.php"); exit(); }

$sql_fetch = "SELECT school_id, first_name, last_name, role FROM users WHERE user_id = ?";
if ($stmt_fetch = $conn->prepare($sql_fetch)) {
$stmt_fetch->bind_param("i", $user_id);
$stmt_fetch->execute();
$result = $stmt_fetch->get_result();
if ($result->num_rows == 1) { $user = $result->fetch_assoc(); } else { header("location: manage_users.php"); exit(); }
$stmt_fetch->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$role = $_POST['role'];
$password = trim($_POST['password']);

if (empty($first_name) || empty($last_name) || empty($role)) { $error_msg = "Name and role fields cannot be empty."; }

if (empty($error_msg)) {
$sql_update = "UPDATE users SET first_name = ?, last_name = ?, role = ? WHERE user_id = ?";
if ($stmt_update = $conn->prepare($sql_update)) {
$stmt_update->bind_param("sssi", $first_name, $last_name, $role, $user_id);
if($stmt_update->execute()) { log_system_action($conn, "Updated details for user: " . $first_name . " " . $last_name . " (User ID: " . $user_id . ")"); }
$stmt_update->close();
}

if (!empty($password)) {
if (strlen($password) < 6) { $error_msg = "Password must be at least 6 characters long."; } else {
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$sql_pass_update = "UPDATE users SET password = ? WHERE user_id = ?";
if ($stmt_pass = $conn->prepare($sql_pass_update)) {
$stmt_pass->bind_param("si", $hashed_password, $user_id);
if($stmt_pass->execute()){ log_system_action($conn, "Reset password for user: " . $first_name . " " . $last_name . " (User ID: " . $user_id . ")"); }
$stmt_pass->close();
}
}
}

if (empty($error_msg)) {
$_SESSION['message'] = "User details updated successfully!";
$_SESSION['message_type'] = "success";
header("location: manage_users.php");
exit();
}
}
}
?>
<main class="container mt-4">
<h1>Edit User: <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
<div class="card">
<div class="card-body">
<?php if (!empty($error_msg)): ?><div class="alert alert-danger"><?php echo $error_msg; ?></div><?php endif; ?>
<form action="edit_user.php?id=<?php echo $user_id; ?>" method="POST">
<div class="mb-3">
<label class="form-label">School ID</label>
<input type="text" class="form-control" value="<?php echo htmlspecialchars($user['school_id']); ?>" readonly>
</div>
<div class="row">
<div class="col-md-6 mb-3">
<label for="first_name" class="form-label">First Name</label>
<input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
</div>
<div class="col-md-6 mb-3">
<label for="last_name" class="form-label">Last Name</label>
<input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
</div>
</div>
<div class="mb-3">
<label for="password" class="form-label">New Password</label>
<input type="password" class="form-control" id="password" name="password">
<small class="form-text text-muted">Leave blank to keep the current password.</small>
</div>
<div class="mb-3">
<label for="role" class="form-label">Role</label>
<select class="form-select" id="role" name="role" required>
<option value="student" <?php if($user['role'] == 'student') echo 'selected'; ?>>Student</option>
<option value="staff" <?php if($user['role'] == 'staff') echo 'selected'; ?>>Staff</option>
<option value="admin" <?php if($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
</select>
</div>
<hr>
<a href="manage_users.php" class="btn btn-secondary">Cancel</a>
<button type="submit" class="btn btn-primary">Save Changes</button>
</form>
</div>
</div>
</main>
<?php
$conn->close();
require_once '../includes/footer.php';
?>