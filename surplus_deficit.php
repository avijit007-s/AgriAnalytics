<?php 
include 'templates/header.php';

// Get filter parameters
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$selected_product = isset($_GET['product']) ? $_GET['product'] : '';
$selected_location = isset($_GET['location']) ? $_GET['location'] : '';

// Build WHERE clause for filters
$where_conditions = ["ph.year = ?"];
$params = [$selected_year];
$param_types = "i";

if ($selected_product) {
    $where_conditions[] = "ph.product_id = ?";
    $params[] = $selected_product;
    $param_types .= "i";
}

if ($selected_location) {
    $where_conditions[] = "ph.location_id = ?";
    $params[] = $selected_location;
    $param_types .= "i";
}

$where_clause = implode(" AND ", $where_conditions);

// Get products and locations for filters
$products = $conn->query("SELECT product_id, name FROM products ORDER BY name");
$locations = $conn->query("SELECT location_id, district_name, division_name FROM locations ORDER BY district_name");
$years = $conn->query("SELECT DISTINCT year FROM production_history ORDER BY year DESC");
?>

<style>
/* Surplus/Deficit Page Specific Styles */
.surplus-page {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: calc(100vh - 80px);
    padding: 0;
    margin: -30px;
    padding: 30px;
}

.surplus-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.surplus-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 15px 15px 0 0;
    margin: -25px -25px 25px -25px;
}

.surplus-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
}

.surplus-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.surplus-table th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.surplus-table tr:hover {
    background: rgba(102, 126, 234, 0.1);
    transform: scale(1.01);
    transition: all 0.3s ease;
}

.filter-section {
    background: rgba(255, 255, 255, 0.9);
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    align-items: end;
}

.surplus-indicator {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 13px;
    font-weight: bold;
}

.surplus-positive {
    background: #d4edda;
    color: #155724;
}

.surplus-negative {
    background: #f8d7da;
    color: #721c24;
}

.surplus-neutral {
    background: #fff3cd;
    color: #856404;
}

.analysis-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.analysis-card {
    background: rgba(255, 255, 255, 0.9);
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    border-left: 4px solid #667eea;
}

.analysis-number {
    font-size: 32px;
    font-weight: bold;
    margin-bottom: 8px;
}

.analysis-label {
    color: #666;
    font-size: 14px;
    margin-bottom: 5px;
}

.analysis-change {
    font-size: 12px;
    font-weight: bold;
}
</style>

<div class="surplus-page">
    <div class="surplus-card card">
        <div class="surplus-header">
            <h2 class="page-title" style="color: white; margin: 0;">
                <i class="fas fa-balance-scale"></i> Surplus/Deficit Analysis
            </h2>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">Track surplus/deficit based on production data, per capita income, and nutrition intake</p>
        </div>

        <!-- Filters -->
        <div class="filter-section">
            <div class="form-group">
                <label for="year">Year</label>
                <select id="year" name="year" class="form-control">
                    <?php
                    $years->data_seek(0);
                    while ($year = $years->fetch_assoc()) {
                        $selected = ($selected_year == $year['year']) ? 'selected' : '';
                        echo "<option value='" . $year['year'] . "' $selected>" . $year['year'] . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="product">Product (Optional)</label>
                <select id="product" name="product" class="form-control">
                    <option value="">All Products</option>
                    <?php
                    $products->data_seek(0);
                    while ($product = $products->fetch_assoc()) {
                        $selected = ($selected_product == $product['product_id']) ? 'selected' : '';
                        echo "<option value='" . $product['product_id'] . "' $selected>" . htmlspecialchars($product['name']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="location">Location (Optional)</label>
                <select id="location" name="location" class="form-control">
                    <option value="">All Locations</option>
                    <?php
                    $locations->data_seek(0);
                    while ($location = $locations->fetch_assoc()) {
                        $selected = ($selected_location == $location['location_id']) ? 'selected' : '';
                        echo "<option value='" . $location['location_id'] . "' $selected>" . 
                             htmlspecialchars($location['district_name'] . ', ' . $location['division_name']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <button onclick="applyFilters()" class="surplus-btn">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Analysis Summary -->
    <div class="analysis-grid">
        <?php
        // Calculate overall statistics
        $total_production_sql = "SELECT SUM(ph.quantity_produced) as total_production 
                                FROM production_history ph 
                                WHERE $where_clause";
        $stmt = $conn->prepare($total_production_sql);
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        $total_production = $stmt->get_result()->fetch_assoc()['total_production'] ?? 0;

        $total_consumption_sql = "SELECT SUM(cd.consumer_purchase_records) as total_consumption 
                                 FROM consumption_data cd 
                                 JOIN production_history ph ON cd.product_id = ph.product_id AND cd.location_id = ph.location_id 
                                 WHERE $where_clause";
        $stmt = $conn->prepare($total_consumption_sql);
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        $total_consumption = $stmt->get_result()->fetch_assoc()['total_consumption'] ?? 0;

        $surplus_deficit = $total_production - $total_consumption;
        $surplus_percentage = $total_production > 0 ? ($surplus_deficit / $total_production) * 100 : 0;
        ?>
        
        <div class="analysis-card">
            <div class="analysis-number" style="color: #27ae60;">
                <?php echo number_format($total_production, 2); ?>
            </div>
            <div class="analysis-label">Total Production (tons)</div>
            <div class="analysis-change" style="color: #27ae60;">
                <i class="fas fa-arrow-up"></i> Production Data
            </div>
        </div>

        <div class="analysis-card">
            <div class="analysis-number" style="color: #e74c3c;">
                <?php echo number_format($total_consumption, 2); ?>
            </div>
            <div class="analysis-label">Total Consumption (tons)</div>
            <div class="analysis-change" style="color: #e74c3c;">
                <i class="fas fa-arrow-down"></i> Consumption Data
            </div>
        </div>

        <div class="analysis-card">
            <div class="analysis-number" style="color: <?php echo $surplus_deficit >= 0 ? '#27ae60' : '#e74c3c'; ?>;">
                <?php echo ($surplus_deficit >= 0 ? '+' : '') . number_format($surplus_deficit, 2); ?>
            </div>
            <div class="analysis-label">Surplus/Deficit (tons)</div>
            <div class="analysis-change" style="color: <?php echo $surplus_deficit >= 0 ? '#27ae60' : '#e74c3c'; ?>;">
                <i class="fas fa-<?php echo $surplus_deficit >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i> 
                <?php echo number_format(abs($surplus_percentage), 1); ?>%
            </div>
        </div>

        <div class="analysis-card">
            <div class="analysis-number" style="color: #3498db;">
                <?php
                $avg_income_sql = "SELECT AVG(cd.per_capita_income) as avg_income 
                                  FROM consumption_data cd 
                                  JOIN production_history ph ON cd.product_id = ph.product_id AND cd.location_id = ph.location_id 
                                  WHERE $where_clause";
                $stmt = $conn->prepare($avg_income_sql);
                $stmt->bind_param($param_types, ...$params);
                $stmt->execute();
                $avg_income = $stmt->get_result()->fetch_assoc()['avg_income'] ?? 0;
                echo '$' . number_format($avg_income, 0);
                ?>
            </div>
            <div class="analysis-label">Avg Per Capita Income</div>
            <div class="analysis-change" style="color: #3498db;">
                <i class="fas fa-dollar-sign"></i> Income Data
            </div>
        </div>
    </div>

    <!-- Detailed Analysis Table -->
    <div class="surplus-card card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-bar"></i> Detailed Surplus/Deficit Analysis
            </h3>
            <div>
                <input type="text" id="searchInput" placeholder="Search analysis..." 
                       style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                <button onclick="searchTable('searchInput', 'analysisTable')" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <button onclick="exportTableToCSV('analysisTable', 'surplus_deficit_analysis.csv')" class="btn btn-success">
                    <i class="fas fa-download"></i> Export CSV
                </button>
            </div>
        </div>
        
        <div class="table-container">
            <table id="analysisTable" class="surplus-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Location</th>
                        <th>Month</th>
                        <th>Production (tons)</th>
                        <th>Consumption (tons)</th>
                        <th>Surplus/Deficit</th>
                        <th>Per Capita Income</th>
                        <th>Nutrition Intake</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $detailed_sql = "SELECT 
                                        p.name as product_name,
                                        l.district_name, l.division_name,
                                        cd.month,
                                        ph.quantity_produced,
                                        cd.consumer_purchase_records,
                                        cd.per_capita_income,
                                        cd.per_capita_nutrition_intake,
                                        (ph.quantity_produced - cd.consumer_purchase_records) as surplus_deficit
                                    FROM production_history ph
                                    JOIN products p ON ph.product_id = p.product_id
                                    JOIN locations l ON ph.location_id = l.location_id
                                    LEFT JOIN consumption_data cd ON ph.product_id = cd.product_id 
                                        AND ph.location_id = cd.location_id 
                                        AND ph.year = cd.year
                                    WHERE $where_clause
                                    ORDER BY p.name, l.district_name, cd.month";
                    
                    $stmt = $conn->prepare($detailed_sql);
                    $stmt->bind_param($param_types, ...$params);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $surplus_deficit = $row['surplus_deficit'] ?? 0;
                            $consumption = $row['consumer_purchase_records'] ?? 0;
                            $production = $row['quantity_produced'] ?? 0;
                            
                            // Determine status
                            $status_class = 'surplus-neutral';
                            $status_icon = 'fas fa-minus';
                            $status_text = 'Balanced';
                            
                            if ($surplus_deficit > 0) {
                                $status_class = 'surplus-positive';
                                $status_icon = 'fas fa-arrow-up';
                                $status_text = 'Surplus';
                            } elseif ($surplus_deficit < 0) {
                                $status_class = 'surplus-negative';
                                $status_icon = 'fas fa-arrow-down';
                                $status_text = 'Deficit';
                            }
                            
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['district_name'] . ', ' . $row['division_name']) . "</td>";
                            echo "<td>" . ($row['month'] ? date('F', mktime(0, 0, 0, $row['month'], 1)) : 'N/A') . "</td>";
                            echo "<td>" . number_format($production, 2) . "</td>";
                            echo "<td>" . number_format($consumption, 2) . "</td>";
                            echo "<td>" . ($surplus_deficit >= 0 ? '+' : '') . number_format($surplus_deficit, 2) . "</td>";
                            echo "<td>$" . number_format($row['per_capita_income'] ?? 0, 2) . "</td>";
                            echo "<td>" . number_format($row['per_capita_nutrition_intake'] ?? 0, 2) . "</td>";
                            echo "<td><span class='surplus-indicator $status_class'><i class='$status_icon'></i> $status_text</span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9' style='text-align: center;'>No data found for selected filters</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Initialize search functionality
searchTable('searchInput', 'analysisTable');

// Apply filters function
function applyFilters() {
    const year = document.getElementById('year').value;
    const product = document.getElementById('product').value;
    const location = document.getElementById('location').value;
    
    let url = 'surplus_deficit.php?year=' + year;
    if (product) url += '&product=' + product;
    if (location) url += '&location=' + location;
    
    window.location.href = url;
}
</script>


<button onclick="downloadPDF()" class="pdf-download-btn">
    <i class="fas fa-file-pdf"></i> Download PDF
</button>

<script>
// Download PDF function
function downloadPDF() {
    const year = document.getElementById('year') ? document.getElementById('year').value : new Date().getFullYear();
    const product = document.getElementById('product') ? document.getElementById('product').value : '';
    
    let url = 'pdf_export.php?page=surplus_deficit&year=' + year;
    if (product) url += '&product=' + product;
    
    window.open(url, '_blank');
}
</script>

<?php include 'templates/footer.php'; ?>

