<?php 
include 'templates/header.php';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $name = sanitizeInput($_POST['name']);
                $type = sanitizeInput($_POST['type']);
                $variety = sanitizeInput($_POST['variety']);
                $sowing_time = sanitizeInput($_POST['sowing_time']);
                $transplanting_time = sanitizeInput($_POST['transplanting_time']);
                $harvest_time = sanitizeInput($_POST['harvest_time']);
                $seed_per_acre = sanitizeInput($_POST['seed_per_acre']);
                
                if ($use_mysql) {
                    // Use mysqli for MySQL
                    $sql = "INSERT INTO products (name, type, variety, sowing_time, transplanting_time, harvest_time, seed_per_acre) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssssd", $name, $type, $variety, $sowing_time, $transplanting_time, $harvest_time, $seed_per_acre);
                    
                    if ($stmt->execute()) {
                        $success_message = "Product added successfully!";
                    } else {
                        $error_message = "Error adding product.";
                    }
                    $stmt->close();
                } else {
                    // Use PDO for SQLite
                    $sql = "INSERT INTO products (name, type, variety, sowing_time, transplanting_time, harvest_time, seed_per_acre) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    
                    if ($stmt->execute([$name, $type, $variety, $sowing_time, $transplanting_time, $harvest_time, $seed_per_acre])) {
                        $success_message = "Product added successfully!";
                    } else {
                        $error_message = "Error adding product.";
                    }
                }
                break;
                
            case 'update':
                $product_id = $_POST['product_id'];
                $name = sanitizeInput($_POST['name']);
                $type = sanitizeInput($_POST['type']);
                $variety = sanitizeInput($_POST['variety']);
                $sowing_time = sanitizeInput($_POST['sowing_time']);
                $transplanting_time = sanitizeInput($_POST['transplanting_time']);
                $harvest_time = sanitizeInput($_POST['harvest_time']);
                $seed_per_acre = sanitizeInput($_POST['seed_per_acre']);
                
                if ($use_mysql) {
                    // Use mysqli for MySQL
                    $sql = "UPDATE products SET name=?, type=?, variety=?, sowing_time=?, transplanting_time=?, harvest_time=?, seed_per_acre=? WHERE product_id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssssdi", $name, $type, $variety, $sowing_time, $transplanting_time, $harvest_time, $seed_per_acre, $product_id);
                    
                    if ($stmt->execute()) {
                        $success_message = "Product updated successfully!";
                    } else {
                        $error_message = "Error updating product.";
                    }
                    $stmt->close();
                } else {
                    // Use PDO for SQLite
                    $sql = "UPDATE products SET name=?, type=?, variety=?, sowing_time=?, transplanting_time=?, harvest_time=?, seed_per_acre=? WHERE product_id=?";
                    $stmt = $pdo->prepare($sql);
                    
                    if ($stmt->execute([$name, $type, $variety, $sowing_time, $transplanting_time, $harvest_time, $seed_per_acre, $product_id])) {
                        $success_message = "Product updated successfully!";
                    } else {
                        $error_message = "Error updating product.";
                    }
                }
                break;
                
            case 'delete':
                $product_id = $_POST['product_id'];
                
                if ($use_mysql) {
                    // Use mysqli for MySQL
                    $sql = "DELETE FROM products WHERE product_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $product_id);
                    
                    if ($stmt->execute()) {
                        $success_message = "Product deleted successfully!";
                    } else {
                        $error_message = "Error deleting product.";
                    }
                    $stmt->close();
                } else {
                    // Use PDO for SQLite
                    $sql = "DELETE FROM products WHERE product_id = ?";
                    $stmt = $pdo->prepare($sql);
                    
                    if ($stmt->execute([$product_id])) {
                        $success_message = "Product deleted successfully!";
                    } else {
                        $error_message = "Error deleting product.";
                    }
                }
                break;
        }
    }
}

// Handle edit mode
$edit_product = null;
if (isset($_GET['edit'])) {
    $product_id = $_GET['edit'];
    
    if ($use_mysql) {
        // Use mysqli for MySQL
        $sql = "SELECT * FROM products WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $edit_product = $result->fetch_assoc();
        $stmt->close();
    } else {
        // Use PDO for SQLite
        $sql = "SELECT * FROM products WHERE product_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$product_id]);
        $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Get statistics
try {
    $stats_query = "SELECT 
        COUNT(*) as total_products,
        COUNT(DISTINCT type) as total_types,
        AVG(seed_per_acre) as avg_seed_per_acre
        FROM products";
    
    if ($use_mysql) {
        // Use mysqli for MySQL
        $stats_result = $conn->query($stats_query);
        $stats = $stats_result->fetch_assoc();
    } else {
        // Use PDO for SQLite
        $stats_result = $pdo->query($stats_query);
        $stats = $stats_result->fetch(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    // Fallback values if query fails
    $stats = [
        'total_products' => 0,
        'total_types' => 0,
        'avg_seed_per_acre' => 0
    ];
}
?>

<div class="dashboard-container">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-seedling"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['total_products']; ?></h3>
                <p>Total Products</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['total_types']; ?></h3>
                <p>Product Types</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-weight-hanging"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['avg_seed_per_acre'], 2); ?></h3>
                <p>Avg Seed/Acre (kg)</p>
            </div>
        </div>
    </div>

    <!-- Add/Edit Product Form -->
    <div class="product-card card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-plus"></i> <?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?>
            </h3>
            <p class="card-subtitle">Comprehensive product information including sowing, transplanting, and harvest details</p>
        </div>
        
        <div class="card-body">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="product-form">
                <input type="hidden" name="action" value="<?php echo $edit_product ? 'update' : 'create'; ?>">
                <?php if ($edit_product): ?>
                    <input type="hidden" name="product_id" value="<?php echo $edit_product['product_id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" id="name" name="name" class="form-control" required
                           value="<?php echo $edit_product ? $edit_product['name'] : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="type">Product Type</label>
                    <select id="type" name="type" class="form-control" required>
                        <option value="">Select Type</option>
                        <option value="Grain" <?php echo ($edit_product && $edit_product['type'] == 'Grain') ? 'selected' : ''; ?>>Grain</option>
                        <option value="Vegetable" <?php echo ($edit_product && $edit_product['type'] == 'Vegetable') ? 'selected' : ''; ?>>Vegetable</option>
                        <option value="Fruit" <?php echo ($edit_product && $edit_product['type'] == 'Fruit') ? 'selected' : ''; ?>>Fruit</option>
                        <option value="Cash Crop" <?php echo ($edit_product && $edit_product['type'] == 'Cash Crop') ? 'selected' : ''; ?>>Cash Crop</option>
                        <option value="Fiber" <?php echo ($edit_product && $edit_product['type'] == 'Fiber') ? 'selected' : ''; ?>>Fiber</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="variety">Variety</label>
                    <input type="text" id="variety" name="variety" class="form-control" 
                           value="<?php echo $edit_product ? $edit_product['variety'] : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="sowing_time">Sowing Time</label>
                    <input type="date" id="sowing_time" name="sowing_time" class="form-control" 
                           value="<?php echo $edit_product ? $edit_product['sowing_time'] : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="transplanting_time">Transplanting Time</label>
                    <input type="date" id="transplanting_time" name="transplanting_time" class="form-control" 
                           value="<?php echo $edit_product ? $edit_product['transplanting_time'] : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="harvest_time">Harvest Time</label>
                    <input type="date" id="harvest_time" name="harvest_time" class="form-control" 
                           value="<?php echo $edit_product ? $edit_product['harvest_time'] : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="seed_per_acre">Seed per Acre (kg)</label>
                    <input type="number" step="0.01" id="seed_per_acre" name="seed_per_acre" class="form-control" 
                           value="<?php echo $edit_product ? $edit_product['seed_per_acre'] : ''; ?>">
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <button type="submit" class="product-btn">
                        <i class="fas fa-save"></i> <?php echo $edit_product ? 'Update Product' : 'Add Product'; ?>
                    </button>
                    <?php if ($edit_product): ?>
                        <a href="product_info.php" class="btn btn-secondary" style="margin-left: 10px;">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Products List -->
    <div class="product-card card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Products List
            </h3>
            <div>
                <input type="text" id="searchInput" placeholder="Search products..." 
                       style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                <button onclick="searchProducts()" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <button onclick="exportTableToCSV('productsTable', 'products.csv')" class="btn btn-success">
                    <i class="fas fa-download"></i> Export CSV
                </button>
                <a href="pdf_export.php?report=products" class="btn btn-danger" target="_blank">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
            </div>
        </div>
        
        <div class="table-container">
            <table id="productsTable" class="product-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Variety</th>
                        <th>Sowing Time</th>
                        <th>Transplanting Time</th>
                        <th>Harvest Time</th>
                        <th>Seed/Acre (kg)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        if ($use_mysql) {
                            // Use mysqli for MySQL
                            $sql = "SELECT * FROM products ORDER BY product_id DESC";
                            $result = $conn->query($sql);
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row['product_id'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['type']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['variety']) . "</td>";
                                    echo "<td>" . ($row['sowing_time'] ? date('M d, Y', strtotime($row['sowing_time'])) : 'N/A') . "</td>";
                                    echo "<td>" . ($row['transplanting_time'] ? date('M d, Y', strtotime($row['transplanting_time'])) : 'N/A') . "</td>";
                                    echo "<td>" . ($row['harvest_time'] ? date('M d, Y', strtotime($row['harvest_time'])) : 'N/A') . "</td>";
                                    echo "<td>" . number_format($row['seed_per_acre'], 2) . "</td>";
                                    echo "<td>";
                                    echo "<a href='product_info.php?edit=" . $row['product_id'] . "' class='btn btn-warning' style='margin-right: 5px;'>";
                                    echo "<i class='fas fa-edit'></i> Edit</a>";
                                    echo "<form method='POST' style='display: inline;' onsubmit='return confirmDelete(\"Are you sure you want to delete this product?\")'>";
                                    echo "<input type='hidden' name='action' value='delete'>";
                                    echo "<input type='hidden' name='product_id' value='" . $row['product_id'] . "'>";
                                    echo "<button type='submit' class='btn btn-danger'><i class='fas fa-trash'></i> Delete</button>";
                                    echo "</form>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9' style='text-align: center;'>No products found</td></tr>";
                            }
                        } else {
                            // Use PDO for SQLite
                            $sql = "SELECT * FROM products ORDER BY product_id DESC";
                            $result = $pdo->query($sql);
                            
                            if ($result) {
                                $rows = $result->fetchAll(PDO::FETCH_ASSOC);
                                if (count($rows) > 0) {
                                    foreach ($rows as $row) {
                                        echo "<tr>";
                                        echo "<td>" . $row['product_id'] . "</td>";
                                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['type']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['variety']) . "</td>";
                                        echo "<td>" . ($row['sowing_time'] ? date('M d, Y', strtotime($row['sowing_time'])) : 'N/A') . "</td>";
                                        echo "<td>" . ($row['transplanting_time'] ? date('M d, Y', strtotime($row['transplanting_time'])) : 'N/A') . "</td>";
                                        echo "<td>" . ($row['harvest_time'] ? date('M d, Y', strtotime($row['harvest_time'])) : 'N/A') . "</td>";
                                        echo "<td>" . number_format($row['seed_per_acre'], 2) . "</td>";
                                        echo "<td>";
                                        echo "<a href='product_info.php?edit=" . $row['product_id'] . "' class='btn btn-warning' style='margin-right: 5px;'>";
                                        echo "<i class='fas fa-edit'></i> Edit</a>";
                                        echo "<form method='POST' style='display: inline;' onsubmit='return confirmDelete(\"Are you sure you want to delete this product?\")'>";
                                        echo "<input type='hidden' name='action' value='delete'>";
                                        echo "<input type='hidden' name='product_id' value='" . $row['product_id'] . "'>";
                                        echo "<button type='submit' class='btn btn-danger'><i class='fas fa-trash'></i> Delete</button>";
                                        echo "</form>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='9' style='text-align: center;'>No products found</td></tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9' style='text-align: center;'>Error loading products</td></tr>";
                            }
                        }
                    } catch (Exception $e) {
                        echo "<tr><td colspan='9' style='text-align: center;'>Error loading products: " . $e->getMessage() . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Product Analytics Charts -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-bar"></i> Product Analytics
            </h3>
        </div>
        
        <div class="charts-grid">
            <div class="chart-card">
                <h4><i class="fas fa-chart-pie"></i> Products by Type Distribution</h4>
                <div class="chart-container">
                    <canvas id="productTypeChart" width="400" height="300"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <h4><i class="fas fa-chart-bar"></i> Seed Requirements by Product</h4>
                <div class="chart-container">
                    <canvas id="seedRequirementChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
function searchProducts() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toUpperCase();
    const table = document.getElementById('productsTable');
    const tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        let td = tr[i].getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < td.length - 1; j++) {
            if (td[j] && td[j].innerHTML.toUpperCase().indexOf(filter) > -1) {
                found = true;
                break;
            }
        }
        
        tr[i].style.display = found ? '' : 'none';
    }
}

// Real-time search
document.getElementById('searchInput').addEventListener('keyup', searchProducts);

// Confirmation dialog
function confirmDelete(message) {
    return confirm(message);
}

// CSV Export function
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const cols = row.querySelectorAll('td, th');
        let csvRow = [];
        
        for (let j = 0; j < cols.length - 1; j++) { // Exclude actions column
            csvRow.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
        }
        
        csv.push(csvRow.join(','));
    }
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
}

// Product Analytics Charts
document.addEventListener('DOMContentLoaded', function() {
    // Get product data for charts
    <?php
    try {
        // Get product type distribution
        if ($use_mysql) {
            $type_query = "SELECT type, COUNT(*) as count FROM products GROUP BY type";
            $type_result = $conn->query($type_query);
            $type_labels = [];
            $type_data = [];
            if ($type_result && $type_result->num_rows > 0) {
                while ($row = $type_result->fetch_assoc()) {
                    $type_labels[] = $row['type'] ?: 'Unknown';
                    $type_data[] = $row['count'];
                }
            }

            // Get seed requirements
            $seed_query = "SELECT name, seed_per_acre FROM products WHERE seed_per_acre > 0 ORDER BY seed_per_acre DESC LIMIT 10";
            $seed_result = $conn->query($seed_query);
            $seed_labels = [];
            $seed_data = [];
            if ($seed_result && $seed_result->num_rows > 0) {
                while ($row = $seed_result->fetch_assoc()) {
                    $seed_labels[] = $row['name'];
                    $seed_data[] = $row['seed_per_acre'];
                }
            }
        } else {
            $type_query = "SELECT type, COUNT(*) as count FROM products GROUP BY type";
            $type_result = $pdo->query($type_query);
            $type_labels = [];
            $type_data = [];
            if ($type_result) {
                while ($row = $type_result->fetch(PDO::FETCH_ASSOC)) {
                    $type_labels[] = $row['type'] ?: 'Unknown';
                    $type_data[] = $row['count'];
                }
            }

            // Get seed requirements
            $seed_query = "SELECT name, seed_per_acre FROM products WHERE seed_per_acre > 0 ORDER BY seed_per_acre DESC LIMIT 10";
            $seed_result = $pdo->query($seed_query);
            $seed_labels = [];
            $seed_data = [];
            if ($seed_result) {
                while ($row = $seed_result->fetch(PDO::FETCH_ASSOC)) {
                    $seed_labels[] = $row['name'];
                    $seed_data[] = $row['seed_per_acre'];
                }
            }
        }
    } catch (Exception $e) {
        // Fallback data
        $type_labels = ['Grain', 'Vegetable', 'Fruit'];
        $type_data = [3, 2, 1];
        $seed_labels = ['Rice', 'Wheat', 'Potato'];
        $seed_data = [25, 40, 1500];
    }
    ?>

    // Check if Chart.js is loaded
    if (typeof Chart !== 'undefined') {
        // Product Type Distribution Chart
        const typeCtx = document.getElementById('productTypeChart');
        if (typeCtx) {
            new Chart(typeCtx, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode($type_labels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($type_data); ?>,
                        backgroundColor: [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                            '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        title: {
                            display: true,
                            text: 'Distribution of Products by Type'
                        }
                    }
                }
            });
        }

        // Seed Requirement Chart
        const seedCtx = document.getElementById('seedRequirementChart');
        if (seedCtx) {
            new Chart(seedCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($seed_labels); ?>,
                    datasets: [{
                        label: 'Seed per Acre (kg)',
                        data: <?php echo json_encode($seed_data); ?>,
                        backgroundColor: '#36A2EB',
                        borderColor: '#2E86AB',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Seed Quantity (kg per acre)'
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Seed Requirements by Product'
                        }
                    }
                }
            });
        }
    } else {
        console.error('Chart.js library not loaded');
    }
});
</script>

<button onclick="downloadPDF()" class="pdf-download-btn">
    <i class="fas fa-file-pdf"></i> Download PDF
</button>

<script>
// Download PDF function
function downloadPDF() {
    const year = document.getElementById('year') ? document.getElementById('year').value : new Date().getFullYear();
    const product = document.getElementById('product') ? document.getElementById('product').value : '';
    
    let url = 'pdf_export.php?page=products&year=' + year;
    if (product) url += '&product=' + product;
    
    window.open(url, '_blank');
}
</script>

<?php include 'templates/footer.php'; ?>

