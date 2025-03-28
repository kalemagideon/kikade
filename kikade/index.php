<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Second Hand Marketplace</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Kikade</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="post_item.php">Sell Item</a>
        </nav>
    </header>

    <main>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search items by title, description, or location...">
            <select id="categoryFilter">
                <option value="">All Categories</option>
                <option value="Furniture">Furniture</option>
                <option value="Electronics">Electronics</option>
                <option value="Clothing">Clothing</option>
                <option value="Books">Books</option>
                <option value="Other">Other</option>
            </select>
            <button id="resetFilters">Reset Filters</button>
        </div>

        <div class="items-container">
            <?php
            $sql = "SELECT * FROM items ORDER BY date_posted DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '<div class="item-card" data-category="' . htmlspecialchars($row['category']) . '">';
                    echo '<a href="item_details.php?id=' . $row['id'] . '">';
                    if ($row['image_path']) {
                        echo '<img src="' . htmlspecialchars($row['image_path']) . '" alt="' . htmlspecialchars($row['title']) . '">';
                    } else {
                        echo '<div class="no-image">No Image</div>';
                    }
                    echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
                    echo '<p class="price">$' . number_format($row['price'], 2) . '</p>';
                    echo '<p class="location">' . htmlspecialchars($row['location']) . '</p>';
                    echo '<div class="description-text" style="display:none;">' . htmlspecialchars($row['description']) . '</div>';
                    echo '</a>';
                    echo '</div>';
                }
            } else {
                echo '<p class="no-items">No items posted yet. Be the first to <a href="post_item.php">post an item</a>!</p>';
            }
            ?>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Second Hand Marketplace</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const resetFilters = document.getElementById('resetFilters');
            const itemCards = document.querySelectorAll('.item-card');

            function filterItems() {
                const searchText = searchInput.value.toLowerCase();
                const category = categoryFilter.value;

                itemCards.forEach(card => {
                    const title = card.querySelector('h3').textContent.toLowerCase();
                    const description = card.querySelector('.description-text').textContent.toLowerCase();
                    const location = card.querySelector('.location').textContent.toLowerCase();
                    const itemCategory = card.dataset.category.toLowerCase();

                    const isVisible =
                        (title.includes(searchText) || description.includes(searchText) || location.includes(searchText)) &&
                        (category === '' || itemCategory === category.toLowerCase());

                    card.style.display = isVisible ? 'block' : 'none';
                });
            }

            searchInput.addEventListener('input', filterItems);
            categoryFilter.addEventListener('change', filterItems);

            resetFilters.addEventListener('click', function() {
                searchInput.value = '';
                categoryFilter.value = '';
                filterItems();
            });

            // Initial filtering on page load
            filterItems();
        });
    </script>
</body>
</html>