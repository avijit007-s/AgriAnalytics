<?php 
include 'templates/header.php';

// Get filter parameters
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$selected_product = isset($_GET['product']) ? $_GET['product'] : '';

// Get products and years for filters
$products = $conn->query("SELECT product_id, name FROM products ORDER BY name");
$years = $conn->query("SELECT DISTINCT year FROM consumption_data ORDER BY year DESC");
?>

<style>
/* Consumption Patterns Page Specific Styles */
.consumption-page {
    background: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%);
    min-height: calc(100vh - 80px);
    padding: 0;
    margin: -30px;
    padding: 30px;
}

.consumption-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.consumption-header {
    background: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%);
    color: white;
    padding: 25px;
    border-radius: 15px 15px 0 0;
    margin: -25px -25px 25px -25px;
}

.consumption-btn {
    background: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%);
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
}

.consumption-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(33, 147, 176, 0.4);
}

.consumption-table th {
    background: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%);
    color: white;
}

.consumption-table tr:hover {
    background: rgba(33, 147, 176, 0.1);
    transform: scale(1.01);
    transition: all 0.3s ease;
}

.pattern-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.pattern-card {
    background: rgba(255, 255, 255, 0.9);
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    border-left: 4px solid #2193b0;
}

.pattern-title {
    font-size: 18px;
    font-weight: bold;
    color: #2193b0;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.pattern-metric {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #ecf0f1;
}

.pattern-metric:last-child {
    border-bottom: none;
}

.metric-label {
    color: #666;
    font-size: 14px;
}

.metric-value {
    font-weight: bold;
    color: #2193b0;
}

.elasticity-indicator {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}

.elastic {
    background: #d4edda;
    color: #155724;
}

.inelastic {
    background: #f8d7da;
    color: #721c24;
}

.unit-elastic {
    background: #fff3cd;
    color: #856404;
}

.seasonal-chart {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
</style>

<div class="consumption-page">
    <div class="consumption-card card">
        <div class="consumption-header">
            <h2 class="page-title" style="color: white; margin: 0;">
                <i class="fas fa-chart-pie"></i> Consumption Patterns & Price Elasticity
            </h2>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">Analyze consumption patterns and price elasticity showing effects of weather, location, and season</p>
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
                <button onclick="applyFilters()" class="consumption-btn">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Pattern Analysis Cards -->
    <div class="pattern-grid">
        <?php
        // Build WHERE clause for filters
        $where_conditions = ["cd.year = ?"];
        $params = [$selected_year];
        $param_types = "i";

        if ($selected_product) {
            $where_conditions[] = "cd.product_id = ?";
            $params[] = $selected_product;
            $param_types .= "i";
        }

        $where_clause = implode(" AND ", $where_conditions);

        // Seasonal Analysis
        $seasonal_sql = "SELECT 
                            CASE 
                                WHEN cd.month IN (12, 1, 2) THEN 'Winter'
                                WHEN cd.month IN (3, 4, 5) THEN 'Spring'
                                WHEN cd.month IN (6, 7, 8) THEN 'Summer'
                                ELSE 'Autumn'
                            END as season,
                            AVG(cd.consumer_purchase_records) as avg_consumption,
                            AVG(cd.per_capita_income) as avg_income,
                            COUNT(*) as record_count
                        FROM consumption_data cd
                        WHERE $where_clause
                        GROUP BY season
                        ORDER BY avg_consumption DESC";
        
        $stmt = $conn->prepare($seasonal_sql);
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        $seasonal_result = $stmt->get_result();
        ?>
        
        <div class="pattern-card">
            <div class="pattern-title">
                <i class="fas fa-calendar-alt"></i> Seasonal Consumption Patterns
            </div>
            <?php while ($season = $seasonal_result->fetch_assoc()): ?>
                <div class="pattern-metric">
                    <span class="metric-label"><?php echo $season['season']; ?></span>
                    <span class="metric-value"><?php echo number_format($season['avg_consumption'], 2); ?> tons</span>
                </div>
            <?php endwhile; ?>
        </div>

        <?php
        // Location-based Analysis
        $location_sql = "SELECT 
                            l.district_name,
                            l.division_name,
                            AVG(cd.consumer_purchase_records) as avg_consumption,
                            AVG(cd.per_capita_income) as avg_income
                        FROM consumption_data cd
                        JOIN locations l ON cd.location_id = l.location_id
                        WHERE $where_clause
                        GROUP BY cd.location_id, l.district_name, l.division_name
                        ORDER BY avg_consumption DESC
                        LIMIT 5";
        
        $stmt = $conn->prepare($location_sql);
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        $location_result = $stmt->get_result();
        ?>
        
        <div class="pattern-card">
            <div class="pattern-title">
                <i class="fas fa-map-marker-alt"></i> Top Consuming Locations
            </div>
            <?php while ($location = $location_result->fetch_assoc()): ?>
                <div class="pattern-metric">
                    <span class="metric-label"><?php echo htmlspecialchars($location['district_name']); ?></span>
                    <span class="metric-value"><?php echo number_format($location['avg_consumption'], 2); ?> tons</span>
                </div>
            <?php endwhile; ?>
        </div>

        <?php
        // Income vs Consumption Analysis
        $income_sql = "SELECT 
                          CASE 
                              WHEN cd.per_capita_income < 20000 THEN 'Low Income'
                              WHEN cd.per_capita_income < 30000 THEN 'Middle Income'
                              ELSE 'High Income'
                          END as income_bracket,
                          AVG(cd.consumer_purchase_records) as avg_consumption,
                          AVG(cd.per_capita_nutrition_intake) as avg_nutrition,
                          COUNT(*) as record_count
                      FROM consumption_data cd
                      WHERE $where_clause
                      GROUP BY income_bracket
                      ORDER BY avg_consumption DESC";
        
        $stmt = $conn->prepare($income_sql);
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        $income_result = $stmt->get_result();
        ?>
        
        <div class="pattern-card">
            <div class="pattern-title">
                <i class="fas fa-dollar-sign"></i> Income-Based Consumption
            </div>
            <?php while ($income = $income_result->fetch_assoc()): ?>
                <div class="pattern-metric">
                    <span class="metric-label"><?php echo $income['income_bracket']; ?></span>
                    <span class="metric-value"><?php echo number_format($income['avg_consumption'], 2); ?> tons</span>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Price Elasticity Analysis -->
    <div class="consumption-card card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-line"></i> Price Elasticity Analysis
            </h3>
        </div>
        
        <div class="table-container">
            <table class="consumption-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Location</th>
                        <th>Avg Price</th>
                        <th>Avg Consumption</th>
                        <th>Price Elasticity</th>
                        <th>Elasticity Type</th>
                        <th>Weather Impact</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $elasticity_sql = "SELECT 
                                          p.name as product_name,
                                          l.district_name, l.division_name,
                                          AVG(ph.retail_price) as avg_price,
                                          AVG(cd.consumer_purchase_records) as avg_consumption,
                                          AVG(wh.rainfall_mm) as avg_rainfall,
                                          AVG(wh.temperature_celsius) as avg_temperature,
                                          STDDEV(ph.retail_price) as price_stddev,
                                          STDDEV(cd.consumer_purchase_records) as consumption_stddev
                                      FROM consumption_data cd
                                      JOIN products p ON cd.product_id = p.product_id
                                      JOIN locations l ON cd.location_id = l.location_id
                                      LEFT JOIN price_history ph ON cd.product_id = ph.product_id 
                                          AND cd.location_id = ph.location_id 
                                          AND YEAR(ph.date) = cd.year
                                      LEFT JOIN weather_history wh ON cd.location_id = wh.location_id 
                                          AND YEAR(wh.date) = cd.year
                                      WHERE $where_clause
                                      GROUP BY cd.product_id, cd.location_id, p.name, l.district_name, l.division_name
                                      HAVING avg_price IS NOT NULL AND avg_consumption IS NOT NULL
                                      ORDER BY p.name, l.district_name";
                    
                    $stmt = $conn->prepare($elasticity_sql);
                    $stmt->bind_param($param_types, ...$params);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            // Simple elasticity calculation (this is a simplified version)
                            $price_coefficient = ($row['price_stddev'] ?? 0) / ($row['avg_price'] ?? 1);
                            $consumption_coefficient = ($row['consumption_stddev'] ?? 0) / ($row['avg_consumption'] ?? 1);
                            $elasticity = $consumption_coefficient > 0 ? $price_coefficient / $consumption_coefficient : 0;
                            
                            // Determine elasticity type
                            $elasticity_class = 'unit-elastic';
                            $elasticity_text = 'Unit Elastic';
                            
                            if (abs($elasticity) > 1) {
                                $elasticity_class = 'elastic';
                                $elasticity_text = 'Elastic';
                            } elseif (abs($elasticity) < 1 && abs($elasticity) > 0) {
                                $elasticity_class = 'inelastic';
                                $elasticity_text = 'Inelastic';
                            }
                            
                            // Weather impact assessment
                            $weather_impact = 'Moderate';
                            if (($row['avg_rainfall'] ?? 0) > 50 || ($row['avg_temperature'] ?? 0) > 30) {
                                $weather_impact = 'High';
                            } elseif (($row['avg_rainfall'] ?? 0) < 10 || ($row['avg_temperature'] ?? 0) < 15) {
                                $weather_impact = 'Low';
                            }
                            
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['district_name'] . ', ' . $row['division_name']) . "</td>";
                            echo "<td>$" . number_format($row['avg_price'] ?? 0, 2) . "</td>";
                            echo "<td>" . number_format($row['avg_consumption'] ?? 0, 2) . " tons</td>";
                            echo "<td>" . number_format($elasticity, 3) . "</td>";
                            echo "<td><span class='elasticity-indicator $elasticity_class'>$elasticity_text</span></td>";
                            echo "<td>" . $weather_impact . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align: center;'>No data found for selected filters</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Monthly Consumption Chart -->
    <div class="seasonal-chart">
        <h3 style="color: #2193b0; margin-bottom: 20px;">
            <i class="fas fa-chart-area"></i> Monthly Consumption Trends
        </h3>
        <canvas id="monthlyChart" style="max-height: 400px;"></canvas>
    </div>
</div>

<script>
// Apply filters function
function applyFilters() {
    const year = document.getElementById('year').value;
    const product = document.getElementById('product').value;
    
    let url = 'consumption_patterns.php?year=' + year;
    if (product) url += '&product=' + product;
    
    window.location.href = url;
}

// Monthly consumption chart
<?php
$monthly_sql = "SELECT 
                   cd.month,
                   AVG(cd.consumer_purchase_records) as avg_consumption
               FROM consumption_data cd
               WHERE $where_clause
               GROUP BY cd.month
               ORDER BY cd.month";

$stmt = $conn->prepare($monthly_sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$monthly_result = $stmt->get_result();

$months = [];
$consumption_data = [];

while ($row = $monthly_result->fetch_assoc()) {
    $months[] = date('F', mktime(0, 0, 0, $row['month'], 1));
    $consumption_data[] = round($row['avg_consumption'], 2);
}
?>

const ctx = document.getElementById('monthlyChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            label: 'Average Consumption (tons)',
            data: <?php echo json_encode($consumption_data); ?>,
            borderColor: '#2193b0',
            backgroundColor: 'rgba(33, 147, 176, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
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
                    text: 'Consumption (tons)'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Month'
                }
            }
        }
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
    
    let url = 'pdf_export.php?page=consumption&year=' + year;
    if (product) url += '&product=' + product;
    
    window.open(url, '_blank');
}
</script>

<?php include 'templates/footer.php'; ?>

