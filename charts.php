<?php include 'templates/header.php'; ?>

<div class="analytics-container">
    <div class="analytics-header">
        <h1 class="analytics-title">
            <i class="fas fa-chart-bar"></i> Advanced Agricultural Analytics
        </h1>
        <p class="analytics-subtitle">Comprehensive data visualization and insights for agricultural production, pricing, and trends</p>
    </div>

    <!-- Filters Section -->
    <div class="filters-section">
        <h3><i class="fas fa-filter"></i> Data Filters</h3>
        <form method="GET" class="filters-form">
            <div class="filter-group">
                <label for="year">Year:</label>
                <select id="year" name="year" class="filter-select">
                    <?php
                    $current_year = date('Y');
                    for ($i = $current_year; $i >= $current_year - 10; $i--) {
                        $selected = (isset($_GET['year']) && $_GET['year'] == $i) ? 'selected' : '';
                        echo "<option value='$i' $selected>$i</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="product">Product:</label>
                <select id="product" name="product" class="filter-select">
                    <option value="">All Products</option>
                    <?php
                    try {
                        $products = $conn->query("SELECT product_id, name FROM products ORDER BY name");
                        if ($products && $products->num_rows > 0) {
                            while ($product = $products->fetch_assoc()) {
                                $selected = (isset($_GET['product']) && $_GET['product'] == $product['product_id']) ? 'selected' : '';
                                echo "<option value='" . $product['product_id'] . "' $selected>" . htmlspecialchars($product['name']) . "</option>";
                            }
                        }
                    } catch (Exception $e) {
                        echo "<option value=''>No products available</option>";
                    }
                    ?>
                </select>
            </div>
            
            <button type="submit" class="filter-btn">
                <i class="fas fa-search"></i> Apply Filters
            </button>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-seedling"></i>
            </div>
            <div class="stat-content">
                <h3>
                    <?php
                    try {
                        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
                        $result = $conn->query("SELECT COUNT(*) as count FROM products");
                        echo $result ? $result->fetch_assoc()['count'] : '0';
                    } catch (Exception $e) {
                        echo '0';
                    }
                    ?>
                </h3>
                <p>Total Products</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <h3>
                    <?php
                    try {
                        $result = $conn->query("SELECT COUNT(*) as count FROM production");
                        echo $result ? $result->fetch_assoc()['count'] : '0';
                    } catch (Exception $e) {
                        echo '0';
                    }
                    ?>
                </h3>
                <p>Production Records</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <h3>
                    <?php
                    try {
                        $result = $conn->query("SELECT COUNT(*) as count FROM prices");
                        echo $result ? $result->fetch_assoc()['count'] : '0';
                    } catch (Exception $e) {
                        echo '0';
                    }
                    ?>
                </h3>
                <p>Price Records</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-cloud-rain"></i>
            </div>
            <div class="stat-content">
                <h3>
                    <?php
                    try {
                        $result = $conn->query("SELECT COUNT(*) as count FROM weather");
                        echo $result ? $result->fetch_assoc()['count'] : '0';
                    } catch (Exception $e) {
                        echo '0';
                    }
                    ?>
                </h3>
                <p>Weather Records</p>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="charts-grid">
        <!-- Production by Product Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-chart-pie"></i> Production by Product</h3>
            </div>
            <div class="chart-container">
                <canvas id="productionChart" width="400" height="300"></canvas>
            </div>
        </div>

        <!-- Price Trends Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-chart-line"></i> Price Trends</h3>
            </div>
            <div class="chart-container">
                <canvas id="priceChart" width="400" height="300"></canvas>
            </div>
        </div>

        <!-- Regional Production Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-map-marker-alt"></i> Production by Region</h3>
            </div>
            <div class="chart-container">
                <canvas id="regionalChart" width="400" height="300"></canvas>
            </div>
        </div>

        <!-- Weather Impact Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-cloud-sun"></i> Weather Patterns</h3>
            </div>
            <div class="chart-container">
                <canvas id="weatherChart" width="400" height="300"></canvas>
            </div>
        </div>

        <!-- Supply vs Demand Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-balance-scale"></i> Supply vs Demand</h3>
            </div>
            <div class="chart-container">
                <canvas id="supplyDemandChart" width="400" height="300"></canvas>
            </div>
        </div>

        <!-- Yield Analysis Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-chart-area"></i> Yield Analysis</h3>
            </div>
            <div class="chart-container">
                <canvas id="yieldChart" width="400" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js library not loaded');
        return;
    }

    // Get chart data from PHP
    <?php
    // Get production data
    try {
        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
        $product_filter = isset($_GET['product']) ? $_GET['product'] : '';
        
        // Production by product
        $production_query = "SELECT p.name, SUM(pr.quantity_produced) as total 
                           FROM products p 
                           LEFT JOIN production pr ON p.product_id = pr.product_id 
                           WHERE pr.year = ?";
        if ($product_filter) {
            $production_query .= " AND p.product_id = ?";
        }
        $production_query .= " GROUP BY p.product_id, p.name ORDER BY total DESC LIMIT 10";
        
        $stmt = $conn->prepare($production_query);
        if ($product_filter) {
            $stmt->bind_param("ii", $year, $product_filter);
        } else {
            $stmt->bind_param("i", $year);
        }
        $stmt->execute();
        $production_result = $stmt->get_result();
        
        $production_labels = [];
        $production_data = [];
        while ($row = $production_result->fetch_assoc()) {
            $production_labels[] = $row['name'];
            $production_data[] = $row['total'] ?: 0;
        }
        
        // Price trends
        $price_query = "SELECT p.name, AVG(pr.retail_price) as avg_price 
                       FROM products p 
                       LEFT JOIN prices pr ON p.product_id = pr.product_id 
                       WHERE YEAR(pr.date) = ?";
        if ($product_filter) {
            $price_query .= " AND p.product_id = ?";
        }
        $price_query .= " GROUP BY p.product_id, p.name ORDER BY avg_price DESC LIMIT 10";
        
        $stmt = $conn->prepare($price_query);
        if ($product_filter) {
            $stmt->bind_param("ii", $year, $product_filter);
        } else {
            $stmt->bind_param("i", $year);
        }
        $stmt->execute();
        $price_result = $stmt->get_result();
        
        $price_labels = [];
        $price_data = [];
        while ($row = $price_result->fetch_assoc()) {
            $price_labels[] = $row['name'];
            $price_data[] = $row['avg_price'] ?: 0;
        }
        
        // Regional production
        $regional_query = "SELECT location, SUM(quantity_produced) as total 
                          FROM production 
                          WHERE year = ?";
        if ($product_filter) {
            $regional_query .= " AND product_id = ?";
        }
        $regional_query .= " GROUP BY location ORDER BY total DESC LIMIT 10";
        
        $stmt = $conn->prepare($regional_query);
        if ($product_filter) {
            $stmt->bind_param("ii", $year, $product_filter);
        } else {
            $stmt->bind_param("i", $year);
        }
        $stmt->execute();
        $regional_result = $stmt->get_result();
        
        $regional_labels = [];
        $regional_data = [];
        while ($row = $regional_result->fetch_assoc()) {
            $regional_labels[] = $row['location'];
            $regional_data[] = $row['total'] ?: 0;
        }
        
        // Weather data
        $weather_query = "SELECT location, AVG(rainfall) as avg_rainfall, AVG(temperature) as avg_temp 
                         FROM weather 
                         WHERE YEAR(date) = ? 
                         GROUP BY location 
                         ORDER BY avg_rainfall DESC LIMIT 10";
        
        $stmt = $conn->prepare($weather_query);
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $weather_result = $stmt->get_result();
        
        $weather_labels = [];
        $weather_rainfall = [];
        $weather_temperature = [];
        while ($row = $weather_result->fetch_assoc()) {
            $weather_labels[] = $row['location'];
            $weather_rainfall[] = $row['avg_rainfall'] ?: 0;
            $weather_temperature[] = $row['avg_temp'] ?: 0;
        }
        
        // Supply vs Demand
        $supply_demand_query = "SELECT product, supply_quantity, demand_quantity 
                               FROM supply_demand 
                               WHERE year = ?";
        if ($product_filter) {
            $supply_demand_query .= " AND product_id = ?";
        }
        $supply_demand_query .= " ORDER BY supply_quantity DESC LIMIT 10";
        
        $stmt = $conn->prepare($supply_demand_query);
        if ($product_filter) {
            $stmt->bind_param("ii", $year, $product_filter);
        } else {
            $stmt->bind_param("i", $year);
        }
        $stmt->execute();
        $supply_demand_result = $stmt->get_result();
        
        $supply_demand_labels = [];
        $supply_data = [];
        $demand_data = [];
        while ($row = $supply_demand_result->fetch_assoc()) {
            $supply_demand_labels[] = $row['product'];
            $supply_data[] = $row['supply_quantity'] ?: 0;
            $demand_data[] = $row['demand_quantity'] ?: 0;
        }
        
    } catch (Exception $e) {
        // Fallback data
        $production_labels = ['Rice', 'Wheat', 'Potato'];
        $production_data = [100, 80, 60];
        $price_labels = ['Rice', 'Wheat', 'Potato'];
        $price_data = [45, 35, 25];
        $regional_labels = ['Dhaka', 'Chittagong', 'Rajshahi'];
        $regional_data = [150, 120, 100];
        $weather_labels = ['Dhaka', 'Chittagong', 'Rajshahi'];
        $weather_rainfall = [120, 150, 80];
        $weather_temperature = [25, 28, 24];
        $supply_demand_labels = ['Rice', 'Wheat', 'Potato'];
        $supply_data = [100, 80, 120];
        $demand_data = [110, 75, 100];
    }
    ?>

    const productionLabels = <?php echo json_encode($production_labels); ?>;
    const productionData = <?php echo json_encode($production_data); ?>;
    const priceLabels = <?php echo json_encode($price_labels); ?>;
    const priceData = <?php echo json_encode($price_data); ?>;
    const regionalLabels = <?php echo json_encode($regional_labels); ?>;
    const regionalData = <?php echo json_encode($regional_data); ?>;
    const weatherLabels = <?php echo json_encode($weather_labels); ?>;
    const weatherRainfall = <?php echo json_encode($weather_rainfall); ?>;
    const weatherTemperature = <?php echo json_encode($weather_temperature); ?>;
    const supplyDemandLabels = <?php echo json_encode($supply_demand_labels); ?>;
    const supplyData = <?php echo json_encode($supply_data); ?>;
    const demandData = <?php echo json_encode($demand_data); ?>;

    // Chart configurations
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    };

    // Production Chart
    const productionCtx = document.getElementById('productionChart');
    if (productionCtx) {
        new Chart(productionCtx, {
            type: 'pie',
            data: {
                labels: productionLabels,
                datasets: [{
                    data: productionData,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                        '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
                    ]
                }]
            },
            options: chartOptions
        });
    }

    // Price Chart
    const priceCtx = document.getElementById('priceChart');
    if (priceCtx) {
        new Chart(priceCtx, {
            type: 'line',
            data: {
                labels: priceLabels,
                datasets: [{
                    label: 'Average Price (৳)',
                    data: priceData,
                    borderColor: '#36A2EB',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    fill: true
                }]
            },
            options: chartOptions
        });
    }

    // Regional Chart
    const regionalCtx = document.getElementById('regionalChart');
    if (regionalCtx) {
        new Chart(regionalCtx, {
            type: 'bar',
            data: {
                labels: regionalLabels,
                datasets: [{
                    label: 'Production (tons)',
                    data: regionalData,
                    backgroundColor: '#4BC0C0'
                }]
            },
            options: chartOptions
        });
    }

    // Weather Chart
    const weatherCtx = document.getElementById('weatherChart');
    if (weatherCtx) {
        new Chart(weatherCtx, {
            type: 'bar',
            data: {
                labels: weatherLabels,
                datasets: [{
                    label: 'Rainfall (mm)',
                    data: weatherRainfall,
                    backgroundColor: '#36A2EB',
                    yAxisID: 'y'
                }, {
                    label: 'Temperature (°C)',
                    data: weatherTemperature,
                    backgroundColor: '#FF6384',
                    yAxisID: 'y1'
                }]
            },
            options: {
                ...chartOptions,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    }

    // Supply vs Demand Chart
    const supplyDemandCtx = document.getElementById('supplyDemandChart');
    if (supplyDemandCtx) {
        new Chart(supplyDemandCtx, {
            type: 'bar',
            data: {
                labels: supplyDemandLabels,
                datasets: [{
                    label: 'Supply',
                    data: supplyData,
                    backgroundColor: '#4BC0C0'
                }, {
                    label: 'Demand',
                    data: demandData,
                    backgroundColor: '#FF6384'
                }]
            },
            options: chartOptions
        });
    }

    // Yield Analysis Chart
    const yieldCtx = document.getElementById('yieldChart');
    if (yieldCtx) {
        new Chart(yieldCtx, {
            type: 'radar',
            data: {
                labels: productionLabels.slice(0, 6),
                datasets: [{
                    label: 'Yield Performance',
                    data: productionData.slice(0, 6),
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: '#36A2EB',
                    pointBackgroundColor: '#36A2EB'
                }]
            },
            options: chartOptions
        });
    }
});
</script>

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
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.filters-form {
    display: flex;
    gap: 20px;
    align-items: end;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-group label {
    font-weight: 600;
    color: #333;
}

.filter-select {
    padding: 10px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    min-width: 150px;
}

.filter-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: transform 0.2s;
}

.filter-btn:hover {
    transform: translateY(-2px);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    padding: 25px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    font-size: 2.5rem;
    color: #667eea;
}

.stat-content h3 {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    color: #333;
}

.stat-content p {
    margin: 5px 0 0 0;
    color: #666;
    font-weight: 500;
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 30px;
}

.chart-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.chart-card:hover {
    transform: translateY(-5px);
}

.chart-header {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.chart-header h3 {
    color: #333;
    font-size: 1.3rem;
    font-weight: 600;
    margin: 0;
}

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

@media (max-width: 768px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
    
    .filters-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-select {
        min-width: auto;
    }
}
</style>

<?php include 'templates/footer.php'; ?>
    $stmt = $conn->prepare($pie_query);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $pie_result = $stmt->get_result();
    while ($row = $pie_result->fetch_assoc()) {
        $pie_labels[] = $row['name'];
        $pie_data[] = floatval($row['total_production']);
    }
    $stmt->close();

    // Get trend data
    $trend_query = "SELECT year, SUM(quantity_produced) as total_production 
                    FROM production_history 
                    WHERE year BETWEEN ? AND ? 
                    GROUP BY year 
                    ORDER BY year";
    $start_year = $selected_year - 3;
    $end_year = $selected_year;
    $stmt = $conn->prepare($trend_query);
    $stmt->bind_param("ii", $start_year, $end_year);
    $stmt->execute();
    $trend_result = $stmt->get_result();
    while ($row = $trend_result->fetch_assoc()) {
        $trend_years[] = $row['year'];
        $trend_data[] = floatval($row['total_production']);
    }
    $stmt->close();

    // Get regional data
    $regional_query = "SELECT l.district_name, SUM(ph.quantity_produced) as total_production 
                       FROM production_history ph 
                       JOIN locations l ON ph.location_id = l.location_id 
                       WHERE $where_clause 
                       GROUP BY l.location_id, l.district_name 
                       ORDER BY total_production DESC";
    $stmt = $conn->prepare($regional_query);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $regional_result = $stmt->get_result();
    while ($row = $regional_result->fetch_assoc()) {
        $regional_labels[] = $row['district_name'];
        $regional_data[] = floatval($row['total_production']);
    }
    $stmt->close();

    // Get price vs production data
    $price_production_query = "SELECT ph.quantity_produced, pr.retail_price 
                               FROM production_history ph 
                               JOIN price_history pr ON ph.product_id = pr.product_id 
                               WHERE ph.year = ? 
                               LIMIT 20";
    $stmt = $conn->prepare($price_production_query);
    $stmt->bind_param("i", $selected_year);
    $stmt->execute();
    $price_production_result = $stmt->get_result();
    while ($row = $price_production_result->fetch_assoc()) {
        $price_production_data[] = [
            'x' => floatval($row['quantity_produced']),
            'y' => floatval($row['retail_price'])
        ];
    }
    $stmt->close();

    // Get weather data for chart
    $weather_query = "SELECT l.district_name, AVG(wh.rainfall_mm) as avg_rainfall, AVG(wh.temperature_celsius) as avg_temp 
                      FROM weather_history wh 
                      JOIN locations l ON wh.location_id = l.location_id 
                      WHERE strftime('%Y', wh.date) = ? 
                      GROUP BY l.location_id, l.district_name 
                      ORDER BY l.district_name";
    $stmt = $conn->prepare($weather_query);
    $stmt->bind_param("s", $selected_year);
    $stmt->execute();
    $weather_result = $stmt->get_result();
    while ($row = $weather_result->fetch_assoc()) {
        $weather_labels[] = $row['district_name'];
        $weather_rainfall[] = floatval($row['avg_rainfall']);
        $weather_temperature[] = floatval($row['avg_temp']);
    }
    $stmt->close();

    // Get supply vs demand data (using production as supply and consumption as demand)
    foreach ($regional_labels as $i => $label) {
        $supply_demand_labels[] = $label;
        $supply_demand_supply[] = $regional_data[$i];
        $supply_demand_demand[] = $regional_data[$i] * 0.85; // Simulate demand as 85% of supply
    }
}
?>

<style>
/* Charts Page Specific Styles */
.charts-page {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    min-height: calc(100vh - 80px);
    padding: 30px;
    margin: -30px;
}

.charts-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    margin-bottom: 25px;
    padding: 25px;
}

.charts-header {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    padding: 25px;
    border-radius: 15px 15px 0 0;
    margin: -25px -25px 25px -25px;
}

.filters-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 25px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 5px;
    font-weight: bold;
    color: #2c3e50;
}

.form-control {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.analytics-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.summary-card {
    background: rgba(255, 255, 255, 0.9);
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    border-left: 4px solid #2c3e50;
}

.summary-number {
    font-size: 28px;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 8px;
}

.summary-label {
    color: #666;
    font-size: 14px;
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 25px;
}

.chart-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.chart-card h3 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    font-size: 18px;
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
    text-align: center;
    padding: 40px;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 12px;
    margin: 20px 0;
}

.no-data-message i {
    font-size: 48px;
    color: #bdc3c7;
    margin-bottom: 15px;
}

.no-data-message h3 {
    color: #7f8c8d;
    margin-bottom: 10px;
}

.no-data-message p {
    color: #95a5a6;
}

.pdf-download-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: #e74c3c;
    color: white;
    border: none;
    border-radius: 50px;
    padding: 15px 25px;
    cursor: pointer;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 1000;
}

.pdf-download-btn:hover {
    background: #c0392b;
    transform: translateY(-2px);
}
</style>

<div class="charts-page">
    <div class="charts-card">
        <div class="charts-header">
            <h2 class="page-title" style="color: white; margin: 0;">
                <i class="fas fa-chart-bar"></i> Advanced Analytics & Visualizations
            </h2>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">Comprehensive data visualization and analytical insights for agricultural data</p>
        </div>

        <!-- Filters -->
        <div class="filters-section">
            <div class="form-group">
                <label for="year">Year</label>
                <select id="year" name="year" class="form-control">
                    <?php
                    foreach ($years_array as $year) {
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
                    if ($products && $products->num_rows > 0) {
                        while ($product = $products->fetch_assoc()) {
                            $selected = ($selected_product == $product['product_id']) ? 'selected' : '';
                            echo "<option value='" . $product['product_id'] . "' $selected>" . htmlspecialchars($product['name']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <button onclick="applyFilters()" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>
        </div>
    </div>

    <?php if ($data_count > 0) { ?>
    <!-- Analytics Summary -->
    <div class="analytics-summary">
        <div class="summary-card">
            <div class="summary-number"><?php echo number_format($total_production, 0); ?></div>
            <div class="summary-label">Total Production (tons)</div>
        </div>
        
        <div class="summary-card">
            <div class="summary-number"><?php echo number_format($avg_yield, 2); ?></div>
            <div class="summary-label">Average Yield (tons/acre)</div>
        </div>
        
        <div class="summary-card">
            <div class="summary-number">$<?php echo number_format($avg_price, 2); ?></div>
            <div class="summary-label">Average Price</div>
        </div>
        
        <div class="summary-card">
            <div class="summary-number"><?php echo number_format($weather_data['avg_rainfall'] ?? 0, 1); ?>mm</div>
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
            <h3><i class="fas fa-chart-bar"></i> Price vs Production Analysis</h3>
            <div class="chart-container">
                <canvas id="priceProductionChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h3><i class="fas fa-cloud-rain"></i> Weather Impact on Production</h3>
            <div class="chart-container">
                <canvas id="weatherImpactChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h3><i class="fas fa-map-marker-alt"></i> Regional Production Comparison</h3>
            <div class="chart-container">
                <canvas id="regionalChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h3><i class="fas fa-balance-scale"></i> Supply vs Demand Analysis</h3>
            <div class="chart-container">
                <canvas id="supplyDemandChart"></canvas>
            </div>
        </div>
    </div>
    <?php } else { ?>
    <!-- No Data Message -->
    <div class="no-data-message">
        <i class="fas fa-chart-line"></i>
        <h3>No Data Available</h3>
        <p>No production data found for the selected year (<?php echo $selected_year; ?>). Please try a different year or add some sample data to the database.</p>
        <p><strong>Tip:</strong> The database has been populated with sample data for years 2021-2023.</p>
    </div>
    <?php } ?>
</div>

<!-- PDF Download Button -->
<button onclick="downloadPDF()" class="pdf-download-btn">
    <i class="fas fa-file-pdf"></i> Download PDF
</button>

<script>
// Chart data from PHP
const chartData = {
    pie: {
        labels: <?php echo json_encode($pie_labels); ?>,
        data: <?php echo json_encode($pie_data); ?>
    },
    trend: {
        labels: <?php echo json_encode($trend_years); ?>,
        data: <?php echo json_encode($trend_data); ?>
    },
    regional: {
        labels: <?php echo json_encode($regional_labels); ?>,
        data: <?php echo json_encode($regional_data); ?>
    },
    priceProduction: <?php echo json_encode($price_production_data); ?>,
    weather: {
        labels: <?php echo json_encode($weather_labels); ?>,
        rainfall: <?php echo json_encode($weather_rainfall); ?>,
        temperature: <?php echo json_encode($weather_temperature); ?>
    },
    supplyDemand: {
        labels: <?php echo json_encode($supply_demand_labels); ?>,
        supply: <?php echo json_encode($supply_demand_supply); ?>,
        demand: <?php echo json_encode($supply_demand_demand); ?>
    }
};

// Chart colors
const colors = {
    primary: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'],
    secondary: ['rgba(255, 99, 132, 0.8)', 'rgba(54, 162, 235, 0.8)', 'rgba(255, 206, 86, 0.8)', 'rgba(75, 192, 192, 0.8)', 'rgba(153, 102, 255, 0.8)', 'rgba(255, 159, 64, 0.8)']
};

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
});

function initializeCharts() {
    // Production Distribution Pie Chart
    const pieCtx = document.getElementById('productionPieChart');
    if (pieCtx && chartData.pie.labels.length > 0) {
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: chartData.pie.labels,
                datasets: [{
                    data: chartData.pie.data,
                    backgroundColor: colors.primary.slice(0, chartData.pie.labels.length),
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
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': ' + context.parsed.toLocaleString() + ' tons (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Production Trend Line Chart
    const trendCtx = document.getElementById('productionTrendChart');
    if (trendCtx && chartData.trend.labels.length > 0) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: chartData.trend.labels,
                datasets: [{
                    label: 'Total Production (tons)',
                    data: chartData.trend.data,
                    borderColor: '#36A2EB',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#36A2EB',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6
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
                            text: 'Production (tons)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    // Price vs Production Scatter Chart
    const priceCtx = document.getElementById('priceProductionChart');
    if (priceCtx && chartData.priceProduction.length > 0) {
        new Chart(priceCtx, {
            type: 'scatter',
            data: {
                datasets: [{
                    label: 'Price vs Production',
                    data: chartData.priceProduction,
                    backgroundColor: '#FF6384',
                    borderColor: '#FF6384',
                    pointRadius: 8,
                    pointHoverRadius: 10
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
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    // Weather Impact Chart
    const weatherCtx = document.getElementById('weatherImpactChart');
    if (weatherCtx && chartData.weather.labels.length > 0) {
        new Chart(weatherCtx, {
            type: 'bar',
            data: {
                labels: chartData.weather.labels,
                datasets: [{
                    label: 'Rainfall (mm)',
                    data: chartData.weather.rainfall,
                    backgroundColor: '#36A2EB',
                    borderColor: '#36A2EB',
                    borderWidth: 1,
                    yAxisID: 'y'
                }, {
                    label: 'Temperature (°C)',
                    data: chartData.weather.temperature,
                    type: 'line',
                    borderColor: '#FF6384',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderWidth: 3,
                    fill: false,
                    yAxisID: 'y1',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Rainfall (mm)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Temperature (°C)'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
    }

    // Regional Production Chart
    const regionalCtx = document.getElementById('regionalChart');
    if (regionalCtx && chartData.regional.labels.length > 0) {
        new Chart(regionalCtx, {
            type: 'doughnut',
            data: {
                labels: chartData.regional.labels,
                datasets: [{
                    data: chartData.regional.data,
                    backgroundColor: colors.primary.slice(0, chartData.regional.labels.length),
                    borderWidth: 3,
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
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed.toLocaleString() + ' tons';
                            }
                        }
                    }
                }
            }
        });
    }

    // Supply vs Demand Chart
    const supplyDemandCtx = document.getElementById('supplyDemandChart');
    if (supplyDemandCtx && chartData.supplyDemand.labels.length > 0) {
        new Chart(supplyDemandCtx, {
            type: 'bar',
            data: {
                labels: chartData.supplyDemand.labels,
                datasets: [{
                    label: 'Supply (tons)',
                    data: chartData.supplyDemand.supply,
                    backgroundColor: '#4BC0C0',
                    borderColor: '#4BC0C0',
                    borderWidth: 1
                }, {
                    label: 'Demand (tons)',
                    data: chartData.supplyDemand.demand,
                    backgroundColor: '#FF6384',
                    borderColor: '#FF6384',
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
                            text: 'Quantity (tons)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
}

// Apply filters function
function applyFilters() {
    const year = document.getElementById('year').value;
    const product = document.getElementById('product').value;
    
    let url = 'charts_fixed_new.php?year=' + year;
    if (product) url += '&product=' + product;
    
    window.location.href = url;
}

// Download PDF function
function downloadPDF() {
    const year = document.getElementById('year').value;
    const product = document.getElementById('product').value;
    
    let url = 'pdf_export.php?page=charts&year=' + year;
    if (product) url += '&product=' + product;
    
    window.open(url, '_blank');
}
</script>

<?php include 'templates/footer.php'; ?>

