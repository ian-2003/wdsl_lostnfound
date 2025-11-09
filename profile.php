<?php
require_once 'includes/header.php';
require_once 'includes/db_connect.php';

$user_id = $_SESSION['id'];

// Define variables for messages
$details_msg = $password_msg = $picture_msg = '';
$details_msg_type = $password_msg_type = $picture_msg_type = 'danger';

// --- LOGIC 1: Handle Profile Details Update ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_details'])) {
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $contact_number = trim($_POST['contact_number']);

    if (empty($first_name) || empty($last_name)) {
        $details_msg = "First and Last Name cannot be empty.";
    } else {
        $sql = "UPDATE users SET first_name = ?, middle_name = ?, last_name = ?, contact_number = ? WHERE user_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssi", $first_name, $middle_name, $last_name, $contact_number, $user_id);
            if ($stmt->execute()) {
                // Update session variable for immediate display in header
                $_SESSION['first_name'] = $first_name;
                $details_msg = "Profile details updated successfully!";
                $details_msg_type = 'success';
            } else {
                $details_msg = "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
}

// --- LOGIC 2: Handle Password Update ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch current hashed password from DB
    $sql_pass = "SELECT password FROM users WHERE user_id = ?";
    if($stmt_pass = $conn->prepare($sql_pass)) {
        $stmt_pass->bind_param("i", $user_id);
        $stmt_pass->execute();
        $stmt_pass->store_result();
        $stmt_pass->bind_result($hashed_password);
        $stmt_pass->fetch();

        if (password_verify($current_password, $hashed_password)) {
            if (strlen($new_password) < 6) {
                $password_msg = "Password must have at least 6 characters.";
            } elseif ($new_password != $confirm_password) {
                $password_msg = "New passwords do not match.";
            } else {
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql_update_pass = "UPDATE users SET password = ? WHERE user_id = ?";
                if ($stmt_update = $conn->prepare($sql_update_pass)) {
                    $stmt_update->bind_param("si", $new_hashed_password, $user_id);
                    if ($stmt_update->execute()) {
                        $password_msg = "Password updated successfully.";
                        $password_msg_type = 'success';
                    } else {
                        $password_msg = "Oops! Something went wrong.";
                    }
                    $stmt_update->close();
                }
            }
        } else {
            $password_msg = "The current password you entered is incorrect.";
        }
        $stmt_pass->close();
    }
}

// --- LOGIC 3: Handle Profile Picture Update ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_picture'])) {
    if (isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] == 0) {
        $target_dir = "uploads/profile_pictures/";
        $file_extension = pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION);
        $new_filename = "user_" . $user_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Validation checks
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check === false) { $picture_msg = "File is not a valid image."; }
        elseif ($_FILES["profile_picture"]["size"] > 2000000) { $picture_msg = "Image size cannot exceed 2MB."; }
        elseif (!in_array(strtolower($file_extension), ['jpg', 'jpeg', 'png', 'gif'])) { $picture_msg = "Only JPG, JPEG, PNG, & GIF files are allowed."; }
        
        if (empty($picture_msg) && move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $sql = "UPDATE users SET profile_picture = ? WHERE user_id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("si", $new_filename, $user_id);
                if ($stmt->execute()) {
                    $picture_msg = "Profile picture updated successfully!";
                    $picture_msg_type = 'success';
                } else {
                    $picture_msg = "Failed to update database record.";
                }
            }
        } elseif(empty($picture_msg)) {
            $picture_msg = "There was an error uploading your file.";
        }
    } else {
        $picture_msg = "Please choose a file to upload.";
    }
}

// Fetch the latest user data for display
$sql_user = "SELECT school_id, first_name, middle_name, last_name, contact_number, department, year_level, section, position, role, profile_picture FROM users WHERE user_id = ?";
$user = null;
if ($stmt = $conn->prepare($sql_user)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

if (!$user) { die("Error: User not found."); }
?>

<main class="container mt-4 mb-5">
    <div class="row">
        <div class="col-md-4">
            <!-- Profile Picture and Basic Info Card -->
            <div class="card text-center">
                <div class="card-body">
                    <img src="uploads/profile_pictures/<?php echo htmlspecialchars($user['profile_picture'] ? $user['profile_picture'] : 'default.png'); ?>" alt="Profile Picture" class="rounded-circle img-fluid" style="width: 150px; height: 150px; object-fit: cover;">
                    <h4 class="card-title mt-3"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                    <p class="text-muted mb-1"><?php echo ucfirst(htmlspecialchars($user['role'])); ?></p>
                    <p class="text-muted"><?php echo htmlspecialchars($user['school_id']); ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Settings Tabs -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#edit">Edit Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#password">Change Password</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#picture">Change Picture</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body tab-content">
                    <!-- Edit Profile Tab -->
                    <div class="tab-pane active" id="edit">
                        <h5 class="card-title">Personal Information</h5>
                        <?php if ($details_msg): ?>
                            <div class="alert alert-<?php echo $details_msg_type; ?>"><?php echo $details_msg; ?></div>
                        <?php endif; ?>
                        <form action="profile.php" method="post">
                            <input type="hidden" name="update_details" value="1">
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">First Name</label><input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Last Name</label><input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required></div>
                            </div>
                            <div class="mb-3"><label class="form-label">Middle Name</label><input type="text" class="form-control" name="middle_name" value="<?php echo htmlspecialchars($user['middle_name']); ?>"></div>
                            <div class="mb-3"><label class="form-label">Contact Number</label><input type="text" class="form-control" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number']); ?>"></div>
                            <hr>
                            <h5 class="card-title">School Information (Read-Only)</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">Department</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($user['department']); ?>" readonly></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Position/Year Level</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($user['position'] ? $user['position'] : $user['year_level']); ?>" readonly></div>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                    <!-- Change Password Tab -->
                    <div class="tab-pane" id="password">
                        <h5 class="card-title">Change Your Password</h5>
                        <?php if ($password_msg): ?>
                            <div class="alert alert-<?php echo $password_msg_type; ?>"><?php echo $password_msg; ?></div>
                        <?php endif; ?>
                        <form action="profile.php" method="post">
                            <input type="hidden" name="update_password" value="1">
                            <div class="mb-3"><label class="form-label">Current Password</label><input type="password" class="form-control" name="current_password" required></div>
                            <div class="mb-3"><label class="form-label">New Password</label><input type="password" class="form-control" name="new_password" required></div>
                            <div class="mb-3"><label class="form-label">Confirm New Password</label><input type="password" class="form-control" name="confirm_password" required></div>
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </form>
                    </div>
                    <!-- Change Picture Tab -->
                    <div class="tab-pane" id="picture">
                        <h5 class="card-title">Update Profile Picture</h5>
                        <?php if ($picture_msg): ?>
                            <div class="alert alert-<?php echo $picture_msg_type; ?>"><?php echo $picture_msg; ?></div>
                        <?php endif; ?>
                        <form action="profile.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="update_picture" value="1">
                            <div class="mb-3">
                                <label for="profile_picture" class="form-label">Choose a new photo</label>
                                <input class="form-control" type="file" name="profile_picture" id="profile_picture" required>
                                <small class="form-text text-muted">Max file size: 2MB. Allowed formats: JPG, PNG, GIF.</small>
                            </div>
                            <button type="submit" class="btn btn-primary">Upload Picture</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
$conn->close();
require_once 'includes/footer.php';
?>