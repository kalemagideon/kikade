<?php include 'config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$sql = "SELECT * FROM items WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$item = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($item['title']); ?> - Second Hand Marketplace</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Second Hand Marketplace</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="post_item.php">Sell Item</a>
        </nav>
    </header>

    <main class="item-details">
        <div class="item-images">
            <?php if ($item['image_path']): ?>
                <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
            <?php else: ?>
                <div class="no-image-large">No Image Available</div>
            <?php endif; ?>
        </div>
        
        <div class="item-info">
            <h1><?php echo htmlspecialchars($item['title']); ?></h1>
            <p class="price">$<?php echo number_format($item['price'], 2); ?></p>
            <p class="location"><?php echo htmlspecialchars($item['location']); ?></p>
            <p class="category"><?php echo htmlspecialchars($item['category']); ?></p>
            <p class="posted-date">Posted on <?php echo date('F j, Y', strtotime($item['date_posted'])); ?></p>
            
            <h2>Description</h2>
            <p class="description"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
            
            <h2>Contact Seller</h2>
            <p>Email: <a href="mailto:<?php echo htmlspecialchars($item['contact_email']); ?>"><?php echo htmlspecialchars($item['contact_email']); ?></a></p>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Second Hand Marketplace</p>
    </footer>
</body>
</html>