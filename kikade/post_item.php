<?php include 'config.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $location = $_POST['location'];
    $contact_email = $_POST['contact_email'];
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'images/';
        $file_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_path = $target_path;
        }
    }
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO items (title, description, price, category, location, image_path, contact_email) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdssss", $title, $description, $price, $category, $location, $image_path, $contact_email);
    
    if ($stmt->execute()) {
        header("Location: index.php?success=1");
        exit();
    } else {
        $error = "Error posting item: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post an Item - Second Hand Marketplace</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Post an Item for Sale</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="post_item.php">Sell Item</a>
        </nav>
    </header>

    <main>
        <form action="post_item.php" method="POST" enctype="multipart/form-data" class="item-form">
            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="title">Item Title*</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description*</label>
                <textarea id="description" name="description" rows="5" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="price">Price ($)*</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="category">Category*</label>
                <select id="category" name="category" required>
                    <option value="">Select a category</option>
                    <option value="Furniture">Furniture</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Clothing">Clothing</option>
                    <option value="Books">Books</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="location">Location*</label>
                <input type="text" id="location" name="location" required>
            </div>
            
            <div class="form-group">
                <label for="contact_email">Contact Email*</label>
                <input type="email" id="contact_email" name="contact_email" required>
            </div>
            
            <div class="form-group">
                <label for="image">Item Image</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>
            
            <button type="submit" class="submit-btn">Post Item</button>
        </form>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Second Hand Marketplace</p>
    </footer>
</body>
</html>