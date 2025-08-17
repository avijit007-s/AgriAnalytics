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
                        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
                        $result = $conn->query("SELECT COUNT(*) as count FROM production_history WHERE year = $year");
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
                        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
                        $result = $conn->query("SELECT COUNT(*) as count FROM price_history WHERE YEAR(date) = $year");
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
                        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
                        $result = $conn->query("SELECT COUNT(*) as count FROM weather_history WHERE YEAR(date) = $year");
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
    // Initialize arrays
    $production_labels = [];
    $production_data = [];
    $price_labels = [];
    $price_data = [];
    $regional_labels = [];
    $regional_data = [];
    $weather_labels = [];
    $weather_rainfall = [];
    $weather_temperature = [];
    $supply_demand_labels = [];
    $supply_data = [];
    $demand_data = [];

    // Get production data
    try {
        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
        $product_filter = isset($_GET['product']) ? $_GET['product'] : '';
        
        // Production by product
        $production_query = "SELECT p.name, SUM(pr.quantity_produced) as total 
                           FROM products p 
                           LEFT JOIN production_history pr ON p.product_id = pr.product_id 
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
        
        while ($row = $production_result->fetch_assoc()) {
            $production_labels[] = $row['name'];
            $production_data[] = $row['total'] ?: 0;
        }
        
        // Price trends
        $price_query = "SELECT p.name, AVG(pr.retail_price) as avg_price 
                       FROM products p 
                       LEFT JOIN price_history pr ON p.product_id = pr.product_id 
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
        
        while ($row = $price_result->fetch_assoc()) {
            $price_labels[] = $row['name'];
            $price_data[] = $row['avg_price'] ?: 0;
        }
        
        // Regional production
        $regional_query = "SELECT location, SUM(quantity_produced) as total 
                          FROM production_history 
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
        
        while ($row = $regional_result->fetch_assoc()) {
            $regional_labels[] = $row['location'];
            $regional_data[] = $row['total'] ?: 0;
        }
        
        // Weather data
        $weather_query = "SELECT location, AVG(rainfall) as avg_rainfall, AVG(temperature) as avg_temp 
                         FROM weather_history 
                         WHERE YEAR(date) = ? 
                         GROUP BY location 
                         ORDER BY avg_rainfall DESC LIMIT 10";
        
        $stmt = $conn->prepare($weather_query);
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $weather_result = $stmt->get_result();
        
        while ($row = $weather_result->fetch_assoc()) {
            $weather_labels[] = $row['location'];
            $weather_rainfall[] = $row['avg_rainfall'] ?: 0;
            $weather_temperature[] = $row['avg_temp'] ?: 0;
        }
        
        // Supply vs Demand (simplified using production data)
        $supply_demand_query = "SELECT p.name, SUM(pr.quantity_produced) as total 
                              FROM products p 
                              LEFT JOIN production_history pr ON p.product_id = pr.product_id 
                              WHERE pr.year = ?";
        if ($product_filter) {
            $supply_demand_query .= " AND p.product_id = ?";
        }
        $supply_demand_query .= " GROUP BY p.product_id, p.name ORDER BY total DESC LIMIT 10";
        
        $stmt = $conn->prepare($supply_demand_query);
        if ($product_filter) {
            $stmt->bind_param("ii", $year, $product_filter);
        } else {
            $stmt->bind_param("i", $year);
        }
        $stmt->execute();
        $supply_demand_result = $stmt->get_result();
        
        while ($row = $supply_demand_result->fetch_assoc()) {
            $supply_demand_labels[] = $row['name'];
            $supply_data[] = $row['total'] ?: 0;
            $demand_data[] = ($row['total'] ?: 0) * 0.85; // Simulated demand as 85% of supply
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