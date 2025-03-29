<?php
// Database connection
$host = 'sql109.infinityfree.com';
$dbname = 'if0_38595302_ecommerce_app';
$username = 'if0_38595302';
$password = '6uK4j3ta2qQr';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Handle CRUD operations
$message = '';
$item = null;

// Create or Update Item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_item'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $seller = trim($_POST['seller']);
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;

    // Validate inputs
    if (empty($title) || empty($description) || $price <= 0) {
        $message = '<div class="alert alert-danger">Please fill all required fields correctly</div>';
    } else {
        try {
            // Handle image upload
            $imagePath = $_POST['existing_image'] ?? '';
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                
                // Validate image
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($fileInfo, $_FILES['image']['tmp_name']);
                
                if (in_array($mimeType, $allowedTypes)) {
                    $filename = uniqid() . '_' . basename($_FILES['image']['name']);
                    $targetPath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                        // Delete old image if exists
                        if (!empty($imagePath) && file_exists($imagePath)) {
                            unlink($imagePath);
                        }
                        $imagePath = $targetPath;
                    }
                }
            }

            // Save to database
            if ($id) {
                // Update existing item
                $stmt = $pdo->prepare("UPDATE items SET title = ?, description = ?, price = ?, seller = ?, image_path = ? WHERE id = ?");
                $stmt->execute([$title, $description, $price, $seller, $imagePath, $id]);
                $message = '<div class="alert alert-success">Item updated successfully</div>';
            } else {
                // Create new item
                $stmt = $pdo->prepare("INSERT INTO items (title, description, price, seller, image_path, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$title, $description, $price, $seller, $imagePath]);
                $message = '<div class="alert alert-success">Item added successfully</div>';
            }
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Delete Item
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    try {
        // Get image path first
        $stmt = $pdo->prepare("SELECT image_path FROM items WHERE id = ?");
        $stmt->execute([$id]);
        $itemToDelete = $stmt->fetch();
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
        $stmt->execute([$id]);
        
        // Delete image file if exists
        if ($itemToDelete && !empty($itemToDelete['image_path']) && file_exists($itemToDelete['image_path'])) {
            unlink($itemToDelete['image_path']);
        }
        
        $message = '<div class="alert alert-success">Item deleted successfully</div>';
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Error deleting item: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Edit Item - Load data
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
}

// Fetch all items
$stmt = $pdo->query("SELECT * FROM items ORDER BY created_at DESC");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        .action-btns {
            white-space: nowrap;
        }
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            display: block;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <h1 class="mb-4">Item Management</h1>
        
        <?php echo $message; ?>
        
        <div class="card mb-4">
            <div class="card-header">
                <h2><?php echo isset($item) ? 'Edit Item' : 'Add New Item'; ?></h2>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" class="form-container">
                    <?php if (isset($item)): ?>
                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                        <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($item['image_path'] ?? ''); ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo htmlspecialchars($item['title'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?php 
                            echo htmlspecialchars($item['description'] ?? ''); 
                        ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price (UGX) *</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" 
                                   value="<?php echo htmlspecialchars($item['price'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="seller" class="form-label">Seller</label>
                            <input type="text" class="form-control" id="seller" name="seller" 
                                   value="<?php echo htmlspecialchars($item['seller'] ?? 'Anonymous'); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Item Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        
                        <?php if (isset($item) && !empty($item['image_path'])): ?>
                            <div class="mt-2">
                                <p>Current Image:</p>
                                <img src="<?php echo htmlspecialchars($item['image_path']); ?>" class="preview-image">
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="save_item" class="btn btn-primary">
                            <i class="material-icons align-middle">save</i> 
                            <?php echo isset($item) ? 'Update Item' : 'Add Item'; ?>
                        </button>
                        
                        <?php if (isset($item)): ?>
                            <a href="master.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2>All Items</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Price (UGX)</th>
                                <th>Seller</th>
                                <th>Views</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($item['image_path'])): ?>
                                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" class="item-image">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                                    <td><?php echo number_format($item['price']); ?></td>
                                    <td><?php echo htmlspecialchars($item['seller']); ?></td>
                                    <td><?php echo $item['views']; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($item['created_at'])); ?></td>
                                    <td class="action-btns">
                                        <a href="?edit=<?php echo $item['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="material-icons align-middle">edit</i>
                                        </a>
                                        <a href="?delete=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this item?')">
                                            <i class="material-icons align-middle">delete</i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image preview functionality
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    let preview = document.querySelector('.preview-image');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.className = 'preview-image';
                        document.querySelector('.mb-3:last-child').appendChild(preview);
                    }
                    preview.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>