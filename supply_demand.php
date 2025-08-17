<?php 
include 'templates/header.php';

// Get filter parameters
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$selected_product = isset($_GET['product']) ? $_GET['product'] : '';
$selected_location = isset($_GET['location']) ? $_GET['location'] : '';

// Get products, locations, and years for filters
$products = $conn->query("SELECT product_id, name FROM products ORDER BY name");
$locations = $conn->query("SELECT location_id, district_name, division_name FROM locations ORDER BY district_name");
$years = $conn->query("SELECT DISTINCT year FROM production_history ORDER BY year DESC");
?>

<style>
/* Supply vs Demand Page Specific Styles (unchanged) */
.supply-demand-page {
    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
    min-height: calc(100vh - 80px);
    padding: 0;
    margin: -30px;
    padding: 30px;
}

.supply-demand-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.supply-demand-header {
    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
    color: white;
    padding: 25px;
    border-radius: 15px 15px 0 0;
    margin: -25px -25px 25px -25px;
}

.supply-demand-btn {
    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
}

.supply-demand-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
}

.supply-demand-table th {
    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
    color: white;
}

.supply-demand-table tr:hover {
    background: rgba(52, 152, 219, 0.1);
    transform: scale(1.01);
    transition: all 0.3s ease;
}

.comparison-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.comparison-card {
    background: rgba(255, 255, 255, 0.9);
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    border-left: 4px solid #2c3e50;
    position: relative;
    overflow: hidden;
}

.comparison-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
}

.comparison-number {
    font-size: 32px;
    font-weight: bold;
    margin-bottom: 8px;
    color: #2c3e50;
}

.comparison-label {
    color: #666;
    font-size: 14px;
    margin-bottom: 5px;
}

.comparison-change {
    font-size: 12px;
    font-weight: bold;
}

.stakeholder-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.stakeholder-card {
    background: rgba(255, 255, 255, 0.9);
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.stakeholder-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.stakeholder-metric {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #ecf0f1;
}

.stakeholder-metric:last-child {
    border-bottom: none;
}

.balance-indicator {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 13px;
    font-weight: bold;
}

.oversupply {
    background: #d1ecf1;
    color: #0c5460;
}

.balanced {
    background: #d4edda;
    color: #155724;
}

.shortage {
    background: #f8d7da;
    color: #721c24;
}

.chart-container {
    background: white;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
</style>

<div class="supply-demand-page">
    <div class="supply-demand-card card">
        <div class="supply-demand-header">
            <h2 class="page-title" style="color: white; margin: 0;">
                <i class="fas fa-exchange-alt"></i> Supply vs Demand Comparison
            </h2>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">Compare supply and demand for producers, wholesalers, and retailers based on price and production data</p>
        </div>

        <!-- Filters -->
        <div class="filter-section" style="background: rgba(255, 255, 255, 0.9); padding: 20px; border-radius: 12px; margin-bottom: 25px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
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
                <button onclick="applyFilters()" class="supply-demand-btn">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>
        </div>
    </div>

    <?php
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

    // Calculate overall supply and demand metrics
    $supply_sql = "SELECT SUM(ph.quantity_produced) as total_supply FROM production_history ph WHERE $where_clause";
    $stmt = $conn->prepare($supply_sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $total_supply = $stmt->get_result()->fetch_assoc()['total_supply'] ?? 0;

    $demand_sql = "SELECT SUM(cd.consumer_purchase_records) as total_demand 
                   FROM consumption_data cd 
                   JOIN production_history ph ON cd.product_id = ph.product_id AND cd.location_id = ph.location_id 
                   WHERE $where_clause AND cd.year = ph.year";
    $stmt = $conn->prepare($demand_sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $total_demand = $stmt->get_result()->fetch_assoc()['total_demand'] ?? 0;

    $supply_demand_ratio = $total_demand > 0 ? $total_supply / $total_demand : 0;
    $balance_status = 'balanced';
    $balance_text = 'Balanced';
    
    if ($supply_demand_ratio > 1.2) {
        $balance_status = 'oversupply';
        $balance_text = 'Oversupply';
    } elseif ($supply_demand_ratio < 0.8) {
        $balance_status = 'shortage';
        $balance_text = 'Shortage';
    }
    ?>

    <!-- Overall Comparison Metrics -->
    <div class="comparison-grid">
        <div class="comparison-card">
            <div class="comparison-number" style="color: #27ae60;">
                <?php echo number_format($total_supply, 2); ?>
            </div>
            <div class="comparison-label">Total Supply (tons)</div>
            <div class="comparison-change" style="color: #27ae60;">
                <i class="fas fa-arrow-up"></i> Production Data
            </div>
        </div>

        <div class="comparison-card">
            <div class="comparison-number" style="color: #e74c3c;">
                <?php echo number_format($total_demand, 2); ?>
            </div>
            <div class="comparison-label">Total Demand (tons)</div>
            <div class="comparison-change" style="color: #e74c3c;">
                <i class="fas fa-arrow-down"></i> Consumption Data
            </div>
        </div>

        <div class="comparison-card">
            <div class="comparison-number" style="color: #ff9a9e;">
                <?php echo number_format($supply_demand_ratio, 2); ?>
            </div>
            <div class="comparison-label">Supply/Demand Ratio</div>
            <div class="comparison-change">
                <span class="balance-indicator <?php echo $balance_status; ?>">
                    <?php echo $balance_text; ?>
                </span>
            </div>
        </div>

        <div class="comparison-card">
            <div class="comparison-number" style="color: #3498db;">
                <?php
                $avg_price_sql = "SELECT AVG(ph.wholesale_price) as avg_wholesale, AVG(ph.retail_price) as avg_retail 
                                 FROM price_history ph 
                                 JOIN production_history pr ON ph.product_id = pr.product_id AND ph.location_id = pr.location_id 
                                 WHERE pr.year = ? " . ($selected_product ? "AND ph.product_id = ?" : "") . ($selected_location ? "AND ph.location_id = ?" : "");
                $stmt = $conn->prepare($avg_price_sql);
                $stmt->bind_param($param_types, ...$params);
                $stmt->execute();
                $price_result = $stmt->get_result()->fetch_assoc();
                $avg_margin = ($price_result['avg_retail'] ?? 0) - ($price_result['avg_wholesale'] ?? 0);
                echo '$' . number_format($avg_margin, 2);
                ?>
            </div>
            <div class="comparison-label">Avg Price Margin</div>
            <div class="comparison-change" style="color: #3498db;">
                <i class="fas fa-dollar-sign"></i> Retail - Wholesale
            </div>
        </div>
    </div>

    <!-- Stakeholder Analysis -->
    <div class="stakeholder-section">
        <div class="stakeholder-card">
            <div class="stakeholder-title" style="color: #27ae60;">
                <i class="fas fa-tractor"></i> Producers Analysis
            </div>
            <?php
            $producer_sql = "SELECT 
                                AVG(ph.quantity_produced) as avg_production,
                                AVG(ph.acreage) as avg_acreage,
                                AVG(ph.quantity_produced / ph.acreage) as avg_yield,
                                COUNT(*) as producer_count
                            FROM production_history ph
                            WHERE $where_clause";
            $stmt = $conn->prepare($producer_sql);
            $stmt->bind_param($param_types, ...$params);
            $stmt->execute();
            $producer_data = $stmt->get_result()->fetch_assoc();
            ?>
            <div class="stakeholder-metric">
                <span class="metric-label">Average Production</span>
                <span class="metric-value"><?php echo number_format($producer_data['avg_production'] ?? 0, 2); ?> tons</span>
            </div>
            <div class="stakeholder-metric">
                <span class="metric-label">Average Acreage</span>
                <span class="metric-value"><?php echo number_format($producer_data['avg_acreage'] ?? 0, 2); ?> acres</span>
            </div>
            <div class="stakeholder-metric">
                <span class="metric-label">Average Yield</span>
                <span class="metric-value"><?php echo number_format($producer_data['avg_yield'] ?? 0, 2); ?> tons/acre</span>
            </div>
            <div class="stakeholder-metric">
                <span class="metric-label">Active Producers</span>
                <span class="metric-value"><?php echo $producer_data['producer_count']; ?></span>
            </div>
        </div>

        <div class="stakeholder-card">
            <div class="stakeholder-title" style="color: #f39c12;">
                <i class="fas fa-warehouse"></i> Wholesalers Analysis
            </div>
            <?php
            $wholesale_sql = "SELECT 
                                 AVG(ph.wholesale_price) as avg_wholesale_price,
                                 MIN(ph.wholesale_price) as min_wholesale_price,
                                 MAX(ph.wholesale_price) as max_wholesale_price,
                                 COUNT(*) as price_records
                             FROM price_history ph
                             JOIN production_history pr ON ph.product_id = pr.product_id AND ph.location_id = pr.location_id
                             WHERE pr.year = ? " . ($selected_product ? "AND ph.product_id = ?" : "") . ($selected_location ? "AND ph.location_id = ?" : "");
            $stmt = $conn->prepare($wholesale_sql);
            $stmt->bind_param($param_types, ...$params);
            $stmt->execute();
            $wholesale_data = $stmt->get_result()->fetch_assoc();
            ?>
            <div class="stakeholder-metric">
                <span class="metric-label">Average Price</span>
                <span class="metric-value">$<?php echo number_format($wholesale_data['avg_wholesale_price'] ?? 0, 2); ?></span>
            </div>
            <div class="stakeholder-metric">
                <span class="metric-label">Min Price</span>
                <span class="metric-value">$<?php echo number_format($wholesale_data['min_wholesale_price'] ?? 0, 2); ?></span>
            </div>
            <div class="stakeholder-metric">
                <span class="metric-label">Max Price</span>
                <span class="metric-value">$<?php echo number_format($wholesale_data['max_wholesale_price'] ?? 0, 2); ?></span>
            </div>
            <div class="stakeholder-metric">
                <span class="metric-label">Price Records</span>
                <span class="metric-value"><?php echo $wholesale_data['price_records']; ?></span>
            </div>
        </div>

        <div class="stakeholder-card">
            <div class="stakeholder-title" style="color: #e74c3c;">
                <i class="fas fa-store"></i> Retailers Analysis
            </div>
            <?php
            $retail_sql = "SELECT 
                              AVG(ph.retail_price) as avg_retail_price,
                              MIN(ph.retail_price) as min_retail_price,
                              MAX(ph.retail_price) as max_retail_price,
                              AVG(ph.retail_price - ph.wholesale_price) as avg_markup
                          FROM price_history ph
                          JOIN production_history pr ON ph.product_id = pr.product_id AND ph.location_id = pr.location_id
                          WHERE pr.year = ? " . ($selected_product ? "AND ph.product_id = ?" : "") . ($selected_location ? "AND ph.location_id = ?" : "");
            $stmt = $conn->prepare($retail_sql);
            $stmt->bind_param($param_types, ...$params);
            $stmt->execute();
            $retail_data = $stmt->get_result()->fetch_assoc();
            ?>
            <div class="stakeholder-metric">
                <span class="metric-label">Average Price</span>
                <span class="metric-value">$<?php echo number_format($retail_data['avg_retail_price'] ?? 0, 2); ?></span>
            </div>
            <div class="stakeholder-metric">
                <span class="metric-label">Min Price</span>
                <span class="metric-value">$<?php echo number_format($retail_data['min_retail_price'] ?? 0, 2); ?></span>
            </div>
            <div class="stakeholder-metric">
                <span class="metric-label">Max Price</span>
                <span class="metric-value">$<?php echo number_format($retail_data['max_retail_price'] ?? 0, 2); ?></span>
            </div>
            <div class="stakeholder-metric">
                <span class="metric-label">Average Markup</span>
                <span class="metric-value">$<?php echo number_format($retail_data['avg_markup'] ?? 0, 2); ?></span>
            </div>
        </div>
    </div>

    <!-- Supply vs Demand Chart -->
    <div class="chart-container">
        <h3 style="color: #ff9a9e; margin-bottom: 20px;">
            <i class="fas fa-chart-bar"></i> Supply vs Demand Comparison by Product
        </h3>
        <canvas id="supplyDemandChart" style="max-height: 400px;"></canvas>
        <?php if (empty($product_names)) echo "<p style='color: red;'>No chart data available.</p>"; ?>
    </div>

    <!-- Detailed Comparison Table -->
    <div class="supply-demand-card card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-table"></i> Detailed Supply vs Demand Analysis
            </h3>
            <div>
                <input type="text" id="searchInput" placeholder="Search analysis..." 
                       style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                <button onclick="filterTable()" class="btn btn-primary" style="margin-left: 10px;"><i class="fas fa-search"></i> Search</button>
                <button onclick="exportTableToCSV('comparisonTable', 'supply_demand_comparison.csv')" class="btn btn-success">
                    <i class="fas fa-download"></i> Export CSV
                </button>
            </div>
        </div>
        
        <div class="table-container">
            <table id="comparisonTable" class="supply-demand-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Location</th>
                        <th>Supply (tons)</th>
                        <th>Demand (tons)</th>
                        <th>Balance</th>
                        <th>Wholesale Price</th>
                        <th>Retail Price</th>
                        <th>Margin</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $detailed_sql = "SELECT 
                                        p.name as product_name,
                                        l.district_name, l.division_name,
                                        ph.quantity_produced as supply,
                                        COALESCE(SUM(cd.consumer_purchase_records), 0) as demand,
                                        AVG(pr.wholesale_price) as avg_wholesale,
                                        AVG(pr.retail_price) as avg_retail
                                    FROM production_history ph
                                    JOIN products p ON ph.product_id = p.product_id
                                    JOIN locations l ON ph.location_id = l.location_id
                                    LEFT JOIN consumption_data cd ON ph.product_id = cd.product_id 
                                        AND ph.location_id = cd.location_id 
                                        AND ph.year = cd.year
                                    LEFT JOIN price_history pr ON ph.product_id = pr.product_id 
                                        AND ph.location_id = pr.location_id 
                                        AND YEAR(pr.date) = ph.year
                                    WHERE $where_clause
                                    GROUP BY ph.production_id, p.name, l.district_name, l.division_name
                                    ORDER BY p.name, l.district_name";
                    
                    $stmt = $conn->prepare($detailed_sql);
                    if ($stmt === false) {
                        die("Prepare failed: " . $conn->error);
                    }
                    $stmt->bind_param($param_types, ...$params);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $supply = $row['supply'] ?? 0;
                            $demand = $row['demand'] ?? 0;
                            $balance = $supply - $demand;
                            $wholesale = $row['avg_wholesale'] ?? 0;
                            $retail = $row['avg_retail'] ?? 0;
                            $margin = $retail - $wholesale;
                            
                            // Determine status
                            $status_class = 'balanced';
                            $status_text = 'Balanced';
                            if ($demand > 0) {
                                $ratio = $supply / $demand;
                                if ($ratio > 1.2) {
                                    $status_class = 'oversupply';
                                    $status_text = 'Oversupply';
                                } elseif ($ratio < 0.8) {
                                    $status_class = 'shortage';
                                    $status_text = 'Shortage';
                                }
                            }
                            
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['district_name'] . ', ' . $row['division_name']) . "</td>";
                            echo "<td>" . number_format($supply, 2) . "</td>";
                            echo "<td>" . number_format($demand, 2) . "</td>";
                            echo "<td>" . ($balance >= 0 ? '+' : '') . number_format($balance, 2) . "</td>";
                            echo "<td>$" . number_format($wholesale, 2) . "</td>";
                            echo "<td>$" . number_format($retail, 2) . "</td>";
                            echo "<td>$" . number_format($margin, 2) . "</td>";
                            echo "<td><span class='balance-indicator $status_class'>$status_text</span></td>";
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

<?php
// Prepare chart data
$chart_sql = "SELECT 
                 p.name as product_name,
                 SUM(ph.quantity_produced) as total_supply,
                 COALESCE(SUM(cd.consumer_purchase_records), 0) as total_demand
             FROM production_history ph
             JOIN products p ON ph.product_id = p.product_id
             LEFT JOIN consumption_data cd ON ph.product_id = cd.product_id 
                 AND ph.location_id = cd.location_id 
                 AND ph.year = cd.year
             WHERE $where_clause
             GROUP BY ph.product_id, p.name
             ORDER BY p.name
             LIMIT 10";

$stmt = $conn->prepare($chart_sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$chart_result = $stmt->get_result();

$product_names = [];
$supply_data = [];
$demand_data = [];

while ($row = $chart_result->fetch_assoc()) {
    $product_names[] = $row['product_name'];
    $supply_data[] = round($row['total_supply'] ?? 0, 2);
    $demand_data[] = round($row['total_demand'] ?? 0, 2);
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Initialize search functionality
function searchTable(inputId, tableId) {
    var input = document.getElementById(inputId);
    var table = document.getElementById(tableId);
    var tr = table.getElementsByTagName("tr");

    input.addEventListener("keyup", function() {
        var filter = input.value.toUpperCase();
        for (var i = 0; i < tr.length; i++) {
            var td = tr[i].getElementsByTagName("td")[0]; // Search by Product
            if (td) {
                var txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    });
}
searchTable('searchInput', 'comparisonTable');

// Apply filters function
function applyFilters() {
    const year = document.getElementById('year').value;
    const product = document.getElementById('product').value;
    const location = document.getElementById('location').value;
    
    let url = 'supply_demand.php?year=' + year;
    if (product) url += '&product=' + product;
    if (location) url += '&location=' + location;
    
    window.location.href = url;
}

// Supply vs Demand Chart
document.addEventListener('DOMContentLoaded', function() {
    const chartCtx = document.getElementById('supplyDemandChart');
    if (!chartCtx) {
        console.error('Canvas element with id "supplyDemandChart" not found.');
        return;
    }

    const data = {
        labels: <?php echo json_encode($product_names); ?>,
        datasets: [{
            label: 'Supply (tons)',
            data: <?php echo json_encode($supply_data); ?>,
            backgroundColor: 'rgba(39, 174, 96, 0.8)',
            borderColor: '#27ae60',
            borderWidth: 2
        }, {
            label: 'Demand (tons)',
            data: <?php echo json_encode($demand_data); ?>,
            backgroundColor: 'rgba(231, 76, 60, 0.8)',
            borderColor: '#e74c3c',
            borderWidth: 2
        }]
    };

    if (data.labels.length === 0) {
        console.warn('No data available to render the chart.');
        return;
    }

    new Chart(chartCtx.getContext('2d'), {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Quantity (tons)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Products'
                    }
                }
            }
        }
    });
});
</script>

<button onclick="downloadPDF()" class="pdf-download-btn">
    <i class="fas fa-file-pdf"></i> Download PDF
</script>

<script>
// Download PDF function
function downloadPDF() {
    const year = document.getElementById('year') ? document.getElementById('year').value : new Date().getFullYear();
    const product = document.getElementById('product') ? document.getElementById('product').value : '';
    
    let url = 'pdf_export.php?page=supply_demand&year=' + year;
    if (product) url += '&product=' + product;
    
    window.open(url, '_blank');
}
</script>

<?php include 'templates/footer.php'; ?>