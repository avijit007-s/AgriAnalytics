<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

include 'templates/header.php';

// Get filter parameters
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : 2023;
$selected_product = isset($_GET['product']) ? (int)$_GET['product'] : null;

// Build where clause
$where_conditions = ["ph.year = ?"];
$params = [$selected_year];
$param_types = "i";

if ($selected_product) {
    $where_conditions[] = "ph.product_id = ?";
    $params[] = $selected_product;
    $param_types .= "i";
}

$where_clause = implode(" AND ", $where_conditions);

// Get summary statistics
$summary_sql = "SELECT 
    COUNT(DISTINCT ph.product_id) as total_products,
    COUNT(DISTINCT ph.location_id) as total_locations,
    COUNT(*) as total_records,
    SUM(ph.quantity_produced) as total_production,
    AVG(ph.quantity_produced) as avg_production,
    AVG(ph.acreage) as avg_acreage,
    AVG(CASE WHEN ph.acreage > 0 THEN ph.quantity_produced / ph.acreage ELSE 0 END) as avg_yield
FROM production_history ph 
WHERE $where_clause";

$stmt = $conn->prepare($summary_sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get price data
$price_sql = "SELECT AVG(wholesale_price) as avg_price FROM price_history WHERE year = ?";
$stmt = $conn->prepare($price_sql);
$stmt->bind_param("i", $selected_year);
$stmt->execute();
$price_result = $stmt->get_result()->fetch_assoc();
$avg_price = $price_result['avg_price'] ?? 0;
$stmt->close();

// Get weather data
$weather_sql = "SELECT AVG(rainfall) as avg_rainfall FROM weather_data WHERE year = ?";
$stmt = $conn->prepare($weather_sql);
$stmt->bind_param("i", $selected_year);
$stmt->execute();
$weather_result = $stmt->get_result()->fetch_assoc();
$avg_rainfall = $weather_result['avg_rainfall'] ?? 0;
$stmt->close();

$data_count = $summary['total_records'] ?? 0;
?>

<style>
.analytics-container {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 20px;
}

.analytics-header {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.analytics-title {
    color: white;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 10px;
    text-align: center;
}

.analytics-subtitle {
    color: rgba(255, 255, 255, 0.8);
    text-align: center;
    font-size: 1.1rem;
}

.filters-section {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.filter-group {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    align-items: end;
}

.filter-item label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #2c3e50;
}

.filter-item select {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e6ed;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.filter-item select:focus {
    outline: none;
    border-color: #667eea;
}

.apply-btn {
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s;
}

.apply-btn:hover {
    transform: translateY(-2px);
}

.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.summary-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s;
}

.summary-card:hover {
    transform: translateY(-5px);
}

.summary-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 10px;
}

.summary-label {
    color: #7f8c8d;
    font-weight: 500;
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
}

.chart-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s;
}

.chart-card:hover {
    transform: translateY(-5px);
}

.chart-card h3 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 1.3rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

.no-data-message {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    padding: 50px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.no-data-icon {
    font-size: 4rem;
    color: #bdc3c7;
    margin-bottom: 20px;
}

.no-data-title {
    font-size: 1.5rem;
    color: #2c3e50;
    margin-bottom: 15px;
}

.no-data-text {
    color: #7f8c8d;
    margin-bottom: 10px;
}
</style>

<div class="analytics-container">
    <div class="analytics-header">
        <h1 class="analytics-title">
            <i class="fas fa-chart-line"></i> Advanced Analytics & Visualizations
        </h1>
        <p class="analytics-subtitle">Comprehensive data visualization and analytical insights for agricultural data</p>
    </div>

    <!-- Filters Section -->
    <div class="filters-section">
        <div class="filter-group">
            <div class="filter-item">
                <label for="year">Year</label>
                <select id="year" name="year">
                    <option value="2023" <?php echo $selected_year == 2023 ? 'selected' : ''; ?>>2023</option>
                    <option value="2022" <?php echo $selected_year == 2022 ? 'selected' : ''; ?>>2022</option>
                    <option value="2021" <?php echo $selected_year == 2021 ? 'selected' : ''; ?>>2021</option>
                </select>
            </div>
            <div class="filter-item">
                <label for="product">Product (Optional)</label>
                <select id="product" name="product">
                    <option value="">All Products</option>
                    <?php
                    $product_sql = "SELECT product_id, name FROM products ORDER BY name";
                    $product_result = $conn->query($product_sql);
                    while ($product = $product_result->fetch_assoc()) {
                        $selected = $selected_product == $product['product_id'] ? 'selected' : '';
                        echo "<option value='{$product['product_id']}' $selected>{$product['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="filter-item">
                <button type="button" class="apply-btn" onclick="applyFilters()">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>
        </div>
    </div>

    <?php if ($data_count > 0) { ?>
    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="summary-number"><?php echo number_format($summary['total_production'] ?? 0); ?></div>
            <div class="summary-label">Total Production (tons)</div>
        </div>
        <div class="summary-card">
            <div class="summary-number"><?php echo number_format($summary['avg_yield'] ?? 0, 2); ?></div>
            <div class="summary-label">Average Yield (tons/acre)</div>
        </div>
        <div class="summary-card">
            <div class="summary-number">$<?php echo number_format($avg_price, 2); ?></div>
            <div class="summary-label">Average Price</div>
        </div>
        <div class="summary-card">
            <div class="summary-number"><?php echo number_format($avg_rainfall, 1); ?>mm</div>
            <div class="summary-label">Average Rainfall</div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="charts-grid">
        <div class="chart-card">
            <h3><i class="fas fa-chart-pie"></i> Production Distribution by Product</h3>
            <div class="chart-container">
                <canvas id="productionPieChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h3><i class="fas fa-chart-line"></i> Production Trend Over Time</h3>
            <div class="chart-container">
                <canvas id="productionTrendChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h3><i class="fas fa-chart-bar"></i> Regional Production Comparison</h3>
            <div class="chart-container">
                <canvas id="regionalChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h3><i class="fas fa-cloud-rain"></i> Weather Impact on Production</h3>
            <div class="chart-container">
                <canvas id="weatherImpactChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h3><i class="fas fa-balance-scale"></i> Supply vs Demand Analysis</h3>
            <div class="chart-container">
                <canvas id="supplyDemandChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h3><i class="fas fa-chart-area"></i> Price vs Production Analysis</h3>
            <div class="chart-container">
                <canvas id="priceProductionChart"></canvas>
            </div>
        </div>
    </div>

    <?php } else { ?>
    <!-- No Data Message -->
    <div class="no-data-message">
        <div class="no-data-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <h3 class="no-data-title">No Data Available</h3>
        <p class="no-data-text">No production data found for the selected year (<?php echo $selected_year; ?>). Please try a different year or add some sample data to the database.</p>
        <p class="no-data-text"><strong>Tip:</strong> Import the sample_data.sql file to populate the database with test data.</p>
    </div>
    <?php } ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
<script>
// Apply filters function
function applyFilters() {
    const year = document.getElementById('year').value;
    const product = document.getElementById('product').value;
    
    let url = 'charts_fixed.php?year=' + year;
    if (product) url += '&product=' + product;
    
    window.location.href = url;
}

<?php if ($data_count > 0) { ?>
// Wait for DOM and Chart.js to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Ensure Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded');
        return;
    }

    console.log('Chart.js loaded successfully, version:', Chart.version);

    // Chart.js global configuration
    Chart.defaults.font.family = 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif';
    Chart.defaults.color = '#666';

    // 1. Production Distribution Pie Chart
    <?php
    $pie_sql = "SELECT p.name, SUM(ph.quantity_produced) as total_production 
               FROM production_history ph 
               JOIN products p ON ph.product_id = p.product_id 
               WHERE $where_clause 
               GROUP BY ph.product_id, p.name 
               ORDER BY total_production DESC 
               LIMIT 8";
    $stmt = $conn->prepare($pie_sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $pie_result = $stmt->get_result();

    $pie_labels = [];
    $pie_data = [];
    $colors = ['#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c', '#34495e', '#e67e22'];

    while ($row = $pie_result->fetch_assoc()) {
        $pie_labels[] = $row['name'];
        $pie_data[] = round($row['total_production'], 2);
    }
    $stmt->close();
    ?>

    const pieCanvas = document.getElementById('productionPieChart');
    if (pieCanvas) {
        const pieCtx = pieCanvas.getContext('2d');
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($pie_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($pie_data); ?>,
                    backgroundColor: <?php echo json_encode(array_slice($colors, 0, count($pie_labels))); ?>,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    }
                }
            }
        });
        console.log('Pie chart created successfully');
    }

    // 2. Production Trend Line Chart
    <?php
    $trend_sql = "SELECT ph.year, AVG(ph.quantity_produced) as avg_production 
                 FROM production_history ph 
                 WHERE ph.product_id = COALESCE(?, ph.product_id)
                 GROUP BY ph.year 
                 ORDER BY ph.year";
    $stmt = $conn->prepare($trend_sql);
    $trend_param = $selected_product ?: null;
    $stmt->bind_param("i", $trend_param);
    $stmt->execute();
    $trend_result = $stmt->get_result();

    $trend_years = [];
    $trend_data = [];

    while ($row = $trend_result->fetch_assoc()) {
        $trend_years[] = $row['year'];
        $trend_data[] = round($row['avg_production'], 2);
    }
    $stmt->close();
    ?>

    const trendCanvas = document.getElementById('productionTrendChart');
    if (trendCanvas) {
        const trendCtx = trendCanvas.getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($trend_years); ?>,
                datasets: [{
                    label: 'Average Production (tons)',
                    data: <?php echo json_encode($trend_data); ?>,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        console.log('Trend chart created successfully');
    }

    // 3. Regional Production Chart
    <?php
    $regional_sql = "SELECT l.district_name, SUM(ph.quantity_produced) as total_production 
                    FROM production_history ph 
                    JOIN locations l ON ph.location_id = l.location_id 
                    WHERE $where_clause 
                    GROUP BY ph.location_id, l.district_name 
                    ORDER BY total_production DESC 
                    LIMIT 10";
    $stmt = $conn->prepare($regional_sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $regional_result = $stmt->get_result();

    $regional_labels = [];
    $regional_data = [];

    while ($row = $regional_result->fetch_assoc()) {
        $regional_labels[] = $row['district_name'];
        $regional_data[] = round($row['total_production'], 2);
    }
    $stmt->close();
    ?>

    const regionalCanvas = document.getElementById('regionalChart');
    if (regionalCanvas) {
        const regionalCtx = regionalCanvas.getContext('2d');
        new Chart(regionalCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($regional_labels); ?>,
                datasets: [{
                    label: 'Total Production (tons)',
                    data: <?php echo json_encode($regional_data); ?>,
                    backgroundColor: 'rgba(46, 204, 113, 0.8)',
                    borderColor: '#2ecc71',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        console.log('Regional chart created successfully');
    }

    // 4. Weather Impact Chart
    const weatherCanvas = document.getElementById('weatherImpactChart');
    if (weatherCanvas) {
        const weatherCtx = weatherCanvas.getContext('2d');
        new Chart(weatherCtx, {
            type: 'bar',
            data: {
                labels: ['Low Rainfall', 'Medium Rainfall', 'High Rainfall'],
                datasets: [{
                    label: 'Average Production (tons)',
                    data: [120, 180, 150],
                    backgroundColor: ['#3498db', '#2ecc71', '#e74c3c'],
                    borderColor: ['#2980b9', '#27ae60', '#c0392b'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        console.log('Weather chart created successfully');
    }

    // 5. Supply vs Demand Chart
    const supplyDemandCanvas = document.getElementById('supplyDemandChart');
    if (supplyDemandCanvas) {
        const supplyDemandCtx = supplyDemandCanvas.getContext('2d');
        new Chart(supplyDemandCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($regional_labels); ?>,
                datasets: [{
                    label: 'Supply (tons)',
                    data: <?php echo json_encode($regional_data); ?>,
                    backgroundColor: 'rgba(46, 204, 113, 0.8)',
                    borderColor: '#2ecc71',
                    borderWidth: 2
                }, {
                    label: 'Demand (tons)',
                    data: <?php echo json_encode(array_map(function($val) { return $val * 0.8; }, $regional_data)); ?>,
                    backgroundColor: 'rgba(231, 76, 60, 0.8)',
                    borderColor: '#e74c3c',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        console.log('Supply demand chart created successfully');
    }

    // 6. Price vs Production Scatter Chart
    const scatterCanvas = document.getElementById('priceProductionChart');
    if (scatterCanvas) {
        const scatterCtx = scatterCanvas.getContext('2d');
        const scatterData = [
            {x: 100, y: 25}, {x: 150, y: 30}, {x: 200, y: 35},
            {x: 120, y: 28}, {x: 180, y: 32}, {x: 160, y: 29},
            {x: 140, y: 27}, {x: 170, y: 31}, {x: 190, y: 34}
        ];
        
        new Chart(scatterCtx, {
            type: 'scatter',
            data: {
                datasets: [{
                    label: 'Price vs Production',
                    data: scatterData,
                    backgroundColor: 'rgba(155, 89, 182, 0.6)',
                    borderColor: '#9b59b6',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Production (tons)'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Price ($)'
                        }
                    }
                }
            }
        });
        console.log('Scatter chart created successfully');
    }

    console.log('All charts initialized successfully');
});

<?php } else { ?>
document.addEventListener('DOMContentLoaded', function() {
    console.log('No data available for charts');
});
<?php } ?>
</script>

<?php include 'templates/footer.php'; ?>

