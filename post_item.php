<?php
require_once 'includes/header.php';
require_once 'includes/db_connect.php';

// Define variables and initialize with empty values
$item_name = $description = $category = $location = $date_found = $post_type = "";
$item_name_err = $description_err = $category_err = $location_err = $date_found_err = $post_type_err = $photo_err = "";
$photo_name = null;

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate item name
    if (empty(trim($_POST["item_name"]))) {
        $item_name_err = "Please enter the item's name.";
    } else {
        $item_name = trim($_POST["item_name"]);
    }

    // Validate description
    if (empty(trim($_POST["description"]))) {
        $description_err = "Please provide a brief description.";
    } else {
        $description = trim($_POST["description"]);
    }
    
    // Validate category
    if (empty($_POST["category"])) {
        $category_err = "Please select a category.";
    } else {
        $category = $_POST["category"];
    }
    
    // Validate location
    if (empty(trim($_POST["location"]))) {
        $location_err = "Please enter the location where the item was lost/found.";
    } else {
        $location = trim($_POST["location"]);
    }

    // Validate date
    if (empty($_POST["date_found"])) {
        $date_found_err = "Please select a date.";
    } else {
        $date_found = $_POST["date_found"];
    }

    // Validate post type
    if (empty($_POST["post_type"])) {
        $post_type_err = "Please specify if the item is lost or found.";
    } else {
        $post_type = $_POST["post_type"];
    }

    // Handle file upload
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
        $target_dir = "uploads/item_photos/";
        // Create a unique filename to prevent overwriting
        $file_extension = pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);
        $photo_name = uniqid() . '_' . time() . '.' . $file_extension;
        $target_file = $target_dir . $photo_name;
        $imageFileType = strtolower($file_extension);

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["photo"]["tmp_name"]);
        if ($check === false) {
            $photo_err = "File is not an image.";
        }

        // Check file size (e.g., 5MB limit)
        if ($_FILES["photo"]["size"] > 5000000) {
            $photo_err = "Sorry, your file is too large.";
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            $photo_err = "Sorry, only JPG, JPEG, & PNG files are allowed.";
        }
        
        // Move the file if no errors
        if (empty($photo_err)) {
            if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                $photo_err = "Sorry, there was an error uploading your file.";
            }
        }
    }

    // Check input errors before inserting into database
    if (empty($item_name_err) && empty($description_err) && empty($category_err) && empty($location_err) && empty($date_found_err) && empty($post_type_err) && empty($photo_err)) {
        
        $sql = "INSERT INTO items (user_id, item_name, description, photo, category, location_found, date_found, post_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
         
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("isssssss", $param_user_id, $param_item_name, $param_description, $param_photo, $param_category, $param_location, $param_date, $param_post_type);
            
            // Set parameters
            $param_user_id = $_SESSION["id"];
            $param_item_name = $item_name;
            $param_description = $description;
            $param_photo = $photo_name;
            $param_category = $category;
            $param_location = $location;
            $param_date = $date_found;
            $param_post_type = $post_type;
            
            if ($stmt->execute()) {
                // Set a success message and redirect
                $_SESSION['success_message'] = "Your item has been submitted for review. It will be visible once approved by an administrator.";
                header("location: index.php");
                exit();
            } else {
                echo "Something went wrong. Please try again later.";
            }

            $stmt->close();
        }
    }
    
    $conn->close();
}
?>

<main class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3>Report a Lost or Found Item</h3>
                    <p class="text-muted">Fill out the form below. All submissions are reviewed by an admin before posting.</p>
                </div>
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                        
                        <div class="mb-3">
                            <label for="item_name" class="form-label">Item Name</label>
                            <input type="text" name="item_name" id="item_name" class="form-control <?php echo (!empty($item_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $item_name; ?>">
                            <span class="invalid-feedback"><?php echo $item_name_err; ?></span>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control <?php echo (!empty($description_err)) ? 'is-invalid' : ''; ?>" rows="3"><?php echo $description; ?></textarea>
                            <span class="invalid-feedback"><?php echo $description_err; ?></span>
                        </div>
                        
                        <div class="mb-3">
                            <label for="photo" class="form-label">Upload Photo (Optional)</label>
                            <input class="form-control <?php echo (!empty($photo_err)) ? 'is-invalid' : ''; ?>" type="file" id="photo" name="photo">
                            <span class="invalid-feedback"><?php echo $photo_err; ?></span>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select name="category" id="category" class="form-select <?php echo (!empty($category_err)) ? 'is-invalid' : ''; ?>">
                                    <option value="">Choose...</option>
                                    <option value="Electronics" <?php if($category == 'Electronics') echo 'selected'; ?>>Electronics</option>
                                    <option value="IDs & Cards" <?php if($category == 'IDs & Cards') echo 'selected'; ?>>IDs & Cards</option>
                                    <option value="Clothing" <?php if($category == 'Clothing') echo 'selected'; ?>>Clothing</option>
                                    <option value="Books" <?php if($category == 'Books') echo 'selected'; ?>>Books</option>
                                    <option value="Keys" <?php if($category == 'Keys') echo 'selected'; ?>>Keys</option>
                                    <option value="Others" <?php if($category == 'Others') echo 'selected'; ?>>Others</option>
                                </select>
                                <span class="invalid-feedback"><?php echo $category_err; ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" name="location" id="location" class="form-control <?php echo (!empty($location_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $location; ?>" placeholder="e.g., SPCF Library">
                                <span class="invalid-feedback"><?php echo $location_err; ?></span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_found" class="form-label">Date Lost/Found</label>
                                <input type="date" name="date_found" id="date_found" class="form-control <?php echo (!empty($date_found_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $date_found; ?>">
                                <span class="invalid-feedback"><?php echo $date_found_err; ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">I am reporting an item I...</label>
                                <div class="form-check">
                                    <input class="form-check-input <?php echo (!empty($post_type_err)) ? 'is-invalid' : ''; ?>" type="radio" name="post_type" id="lostRadio" value="lost" <?php if($post_type == 'lost') echo 'checked'; ?>>
                                    <label class="form-check-label" for="lostRadio">Lost</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input <?php echo (!empty($post_type_err)) ? 'is-invalid' : ''; ?>" type="radio" name="post_type" id="foundRadio" value="found" <?php if($post_type == 'found') echo 'checked'; ?>>
                                    <label class="form-check-label" for="foundRadio">Found</label>
                                    <span class="invalid-feedback d-block"><?php echo $post_type_err; ?></span>
                                </div>
                            </div>
                        </div>

                        <hr>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Submit for Review</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>