<?php
include('../connect.php');

$query = "SELECT c.id, c.category_name, (SELECT COUNT(*) FROM assets WHERE category = c.category_name) AS asset_count 
          FROM categories c ORDER BY c.category_name ASC";
$result = mysqli_query($conn, $query);

while ($category = mysqli_fetch_assoc($result)) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($category['id']) . '</td>';
    echo '<td>' . htmlspecialchars($category['category_name']) . '</td>';
    echo '<td>' . htmlspecialchars($category['asset_count']) . '</td>';
    echo '<td>';
    
    if ($category['asset_count'] == 0) {
        echo '<button class="btn btn-sm btn-danger delete-category" data-id="' . $category['id'] . '">
                <i class="fas fa-trash"></i>
              </button>';
    } else {
        echo '<button class="btn btn-sm btn-secondary" disabled title="Cannot delete category in use">
                <i class="fas fa-trash"></i>
              </button>';
    }

    echo '</td>';
    echo '</tr>';
}
?>
