<?php 
include 'templates/header.php';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $product_id = sanitizeInput($_POST['product_id']);
                $location_id = sanitizeInput($_POST['location_id']);
                $date = sanitizeInput($_POST['date']);
                $wholesale_price = sanitizeInput($_POST['wholesale_price']);
                $retail_price = sanitizeInput($_POST['retail_price']);
                
                $sql = "INSERT INTO price_history (product_id, location_id, date, wholesale_price, retail_price) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iisdd", $product_id, $location_id, $date, $wholesale_price, $retail_price);
                
                if ($stmt->execute()) {
                    $success_message = "Price record added successfully!";
                } else {
                    $error_message = "Error adding price record: " . $conn->error;
                }
                break;
                
            case 'update':
                $price_id = $_POST['price_id'];
                $product_id = sanitizeInput($_POST['product_id']);
                $location_id = sanitizeInput($_POST['location_id']);
                $date = sanitizeInput($_POST['date']);
                $wholesale_price = sanitizeInput($_POST['wholesale_price']);
                $retail_price = sanitizeInput($_POST['retail_price']);
                
                $sql = "UPDATE price_history SET product_id=?, location_id=?, date=?, wholesale_price=?, retail_price=? WHERE price_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iisddi", $product_id, $location_id, $date, $wholesale_price, $retail_price, $price_id);
                
                if ($stmt->execute()) {
                    $success_message = "Price record updated successfully!";
                } else {
                    $error_message = "Error updating price record: " . $conn->error;
                }
                break;
                
            case 'delete':
                $price_id = $_POST['price_id'];
                $sql = "DELETE FROM price_history WHERE price_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $price_id);
                
                if ($stmt->execute()) {
                    $success_message = "Price record deleted successfully!";
                } else {
                    $error_message = "Error deleting price record: " . $conn->error;
                }
                break;
        }
    }
}

// Get record for editing
$edit_record = null;
if (isset($_GET['edit'])) {
    $price_id = $_GET['edit'];
    $sql = "SELECT * FROM price_history WHERE price_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $price_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_record = $result->fetch_assoc();
}

// Get products and locations for dropdowns
$products = $conn->query("SELECT product_id, name FROM products ORDER BY name");
$locations = $conn->query("SELECT location_id, district_name, division_name FROM locations ORDER BY district_name");
?>

<style>
/* Price Trends Page Specific Styles */
.price-page {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    min-height: calc(100vh - 80px);
    padding: 0;
    margin: -30px;
    padding: 30px;
}

.price-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.price-header {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
    padding: 25px;
    border-radius: 15px 15px 0 0;
    margin: -25px -25px 25px -25px;
}

.price-btn {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
}

.price-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(243, 156, 18, 0.4);
}

.price-table th {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
}

.price-table tr:hover {
    background: rgba(243, 156, 18, 0.1);
    transform: scale(1.01);
    transition: all 0.3s ease;
}

.price-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.price-stat-card {
    background: rgba(255, 255, 255, 0.9);
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    border-left: 4px solid #f093fb;
}

.price-stat-number {
    font-size: 24px;
    font-weight: bold;
    color: #f5576c;
    margin-bottom: 5px;
}

.price-stat-label {
    color: #666;
    font-size: 14px;
}

.trend-indicator {
    display: inline-flex;
    align-items: center;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}

.trend-up {
    background: #d4edda;
    color: #155724;
}

.trend-down {
    background: #f8d7da;
    color: #721c24;
}

.trend-stable {
    background: #fff3cd;
    color: #856404;
}
</style>

<div class="price-page">
    <!-- Price Statistics -->
    <div class="price-stats">
        <div class="price-stat-card">
            <div class="price-stat-number">
                ৳<?php
                $result = $conn->query("SELECT AVG(wholesale_price) as avg_price FROM price_history WHERE wholesale_price IS NOT NULL");
                $avg = $result->fetch_assoc()['avg_price'];
                echo number_format($avg ? $avg : 0, 2);
                ?>
            </div>
            <div class="price-stat-label">Avg Wholesale Price</div>
        </div>
        <div class="price-stat-card">
            <div class="price-stat-number">
                ৳<?php
                $result = $conn->query("SELECT AVG(retail_price) as avg_price FROM price_history WHERE retail_price IS NOT NULL");
                $avg = $result->fetch_assoc()['avg_price'];
                echo number_format($avg ? $avg : 0, 2);
                ?>
            </div>
            <div class="price-stat-label">Avg Retail Price</div>
        </div>
        <div class="price-stat-card">
            <div class="price-stat-number">
                ৳<?php
                $result = $conn->query("SELECT MAX(retail_price) as max_price FROM price_history WHERE retail_price IS NOT NULL");
                $max = $result->fetch_assoc()['max_price'];
                echo number_format($max ? $max : 0, 2);
                ?>
            </div>
            <div class="price-stat-label">Highest Price</div>
        </div>
        <div class="price-stat-card">
            <div class="price-stat-number">
                <?php
                $result = $conn->query("SELECT COUNT(*) as count FROM price_history");
                echo $result->fetch_assoc()['count'];
                ?>
            </div>
            <div class="price-stat-label">Total Records</div>
        </div>
    </div>

    <div class="price-card card">
        <div class="price-header">
            <h2 class="page-title" style="color: white; margin: 0;">
                <i class="fas fa-dollar-sign"></i> Price Trends Management
            </h2>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">Track wholesale and retail prices and analyze price trends for various crops</p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Add/Edit Price Record Form -->
        <div class="form-container">
            <h3><?php echo $edit_record ? 'Edit Price Record' : 'Add New Price Record'; ?></h3>
            <form method="POST" class="price-form">
                <input type="hidden" name="action" value="<?php echo $edit_record ? 'update' : 'create'; ?>">
                <?php if ($edit_record): ?>
                    <input type="hidden" name="price_id" value="<?php echo $edit_record['price_id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="product_id">Product</label>
                    <select id="product_id" name="product_id" class="form-control" required>
                        <option value="">Select Product</option>
                        <?php
                        $products->data_seek(0);
                        while ($product = $products->fetch_assoc()) {
                            $selected = ($edit_record && $edit_record['product_id'] == $product['product_id']) ? 'selected' : '';
                            echo "<option value='" . $product['product_id'] . "' $selected>" . htmlspecialchars($product['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="location_id">Location</label>
                    <select id="location_id" name="location_id" class="form-control" required>
                        <option value="">Select Location</option>
                        <?php
                        $locations->data_seek(0);
                        while ($location = $locations->fetch_assoc()) {
                            $selected = ($edit_record && $edit_record['location_id'] == $location['location_id']) ? 'selected' : '';
                            echo "<option value='" . $location['location_id'] . "' $selected>" . 
                                 htmlspecialchars($location['district_name'] . ', ' . $location['division_name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date" class="form-control" 
                           value="<?php echo $edit_record ? $edit_record['date'] : date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="wholesale_price">Wholesale Price (৳)</label>
                    <input type="number" step="0.01" id="wholesale_price" name="wholesale_price" class="form-control" 
                           value="<?php echo $edit_record ? $edit_record['wholesale_price'] : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="retail_price">Retail Price (৳)</label>
                    <input type="number" step="0.01" id="retail_price" name="retail_price" class="form-control" 
                           value="<?php echo $edit_record ? $edit_record['retail_price'] : ''; ?>" required>
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <button type="submit" class="price-btn">
                        <i class="fas fa-save"></i> <?php echo $edit_record ? 'Update Record' : 'Add Record'; ?>
                    </button>
                    <?php if ($edit_record): ?>
                        <a href="price_trends.php" class="btn btn-secondary" style="margin-left: 10px;">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Price Records List -->
    <div class="price-card card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Price Records
            </h3>
            <div>
                <input type="text" id="searchInput" placeholder="Search records..." 
                       style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                <button onclick="searchTable('searchInput', 'priceTable')" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <button onclick="exportTableToCSV('priceTable', 'price_trends.csv')" class="btn btn-success">
                    <i class="fas fa-download"></i> Export CSV
                </button>
                <a href="pdf_export.php?report=prices" class="btn btn-danger" target="_blank">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
            </div>
        </div>
        
        <div class="table-container">
            <table id="priceTable" class="price-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th>Wholesale Price (৳)</th>
                        <th>Retail Price (৳)</th>
                        <th>Margin (৳)</th>
                        <th>Trend</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT ph.*, p.name as product_name, l.district_name, l.division_name 
                            FROM price_history ph 
                            JOIN products p ON ph.product_id = p.product_id 
                            JOIN locations l ON ph.location_id = l.location_id 
                            ORDER BY ph.date DESC, ph.price_id DESC";
                    $result = $conn->query($sql);
                    
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $margin = $row['retail_price'] - $row['wholesale_price'];
                            $margin_percent = $row['wholesale_price'] > 0 ? ($margin / $row['wholesale_price']) * 100 : 0;
                            
                            // Simple trend calculation (you can make this more sophisticated)
                            $trend_class = 'trend-stable';
                            $trend_icon = 'fas fa-minus';
                            $trend_text = 'Stable';
                            
                            if ($margin_percent > 20) {
                                $trend_class = 'trend-up';
                                $trend_icon = 'fas fa-arrow-up';
                                $trend_text = 'High Margin';
                            } elseif ($margin_percent < 10) {
                                $trend_class = 'trend-down';
                                $trend_icon = 'fas fa-arrow-down';
                                $trend_text = 'Low Margin';
                            }
                            
                            echo "<tr>";
                            echo "<td>" . $row['price_id'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['district_name'] . ', ' . $row['division_name']) . "</td>";
                            echo "<td>" . date('M d, Y', strtotime($row['date'])) . "</td>";
                            echo "<td>৳" . number_format($row['wholesale_price'], 2) . "</td>";
                            echo "<td>৳" . number_format($row['retail_price'], 2) . "</td>";
                            echo "<td>৳" . number_format($margin, 2) . " (" . number_format($margin_percent, 1) . "%)</td>";
                            echo "<td><span class='trend-indicator $trend_class'><i class='$trend_icon'></i> $trend_text</span></td>";
                            echo "<td>";
                            echo "<a href='price_trends.php?edit=" . $row['price_id'] . "' class='btn btn-warning' style='margin-right: 5px;'>";
                            echo "<i class='fas fa-edit'></i> Edit</a>";
                            echo "<form method='POST' style='display: inline;' onsubmit='return confirmDelete(\"Are you sure you want to delete this record?\")'>";
                            echo "<input type='hidden' name='action' value='delete'>";
                            echo "<input type='hidden' name='price_id' value='" . $row['price_id'] . "'>";
                            echo "<button type='submit' class='btn btn-danger'><i class='fas fa-trash'></i> Delete</button>";
                            echo "</form>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9' style='text-align: center;'>No price records found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Initialize search functionality
searchTable('searchInput', 'priceTable');
</script>


<button onclick="downloadPDF()" class="pdf-download-btn">
    <i class="fas fa-file-pdf"></i> Download PDF
</button>

<script>
// Download PDF function
function downloadPDF() {
    const year = document.getElementById('year') ? document.getElementById('year').value : new Date().getFullYear();
    const product = document.getElementById('product') ? document.getElementById('product').value : '';
    
    let url = 'pdf_export.php?page=prices&year=' + year;
    if (product) url += '&product=' + product;
    
    window.open(url, '_blank');
}
</script>

<?php include 'templates/footer.php'; ?>

