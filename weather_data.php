<?php 
include 'templates/header.php';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $location_id = sanitizeInput($_POST['location_id']);
                $date = sanitizeInput($_POST['date']);
                $rainfall_mm = sanitizeInput($_POST['rainfall_mm']);
                $temperature_celsius = sanitizeInput($_POST['temperature_celsius']);
                
                $sql = "INSERT INTO weather_history (location_id, date, rainfall_mm, temperature_celsius) 
                        VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isdd", $location_id, $date, $rainfall_mm, $temperature_celsius);
                
                if ($stmt->execute()) {
                    $success_message = "Weather record added successfully!";
                } else {
                    $error_message = "Error adding weather record: " . $conn->error;
                }
                break;
                
            case 'update':
                $weather_id = $_POST['weather_id'];
                $location_id = sanitizeInput($_POST['location_id']);
                $date = sanitizeInput($_POST['date']);
                $rainfall_mm = sanitizeInput($_POST['rainfall_mm']);
                $temperature_celsius = sanitizeInput($_POST['temperature_celsius']);
                
                $sql = "UPDATE weather_history SET location_id=?, date=?, rainfall_mm=?, temperature_celsius=? WHERE weather_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isddi", $location_id, $date, $rainfall_mm, $temperature_celsius, $weather_id);
                
                if ($stmt->execute()) {
                    $success_message = "Weather record updated successfully!";
                } else {
                    $error_message = "Error updating weather record: " . $conn->error;
                }
                break;
                
            case 'delete':
                $weather_id = $_POST['weather_id'];
                $sql = "DELETE FROM weather_history WHERE weather_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $weather_id);
                
                if ($stmt->execute()) {
                    $success_message = "Weather record deleted successfully!";
                } else {
                    $error_message = "Error deleting weather record: " . $conn->error;
                }
                break;
        }
    }
}

// Get record for editing
$edit_record = null;
if (isset($_GET['edit'])) {
    $weather_id = $_GET['edit'];
    $sql = "SELECT * FROM weather_history WHERE weather_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $weather_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_record = $result->fetch_assoc();
}

// Get locations for dropdown
$locations = $conn->query("SELECT location_id, district_name, division_name FROM locations ORDER BY district_name");
?>

<style>
/* Weather Data Page Specific Styles */
.weather-page {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    min-height: calc(100vh - 80px);
    padding: 0;
    margin: -30px;
    padding: 30px;
}

.weather-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.weather-header {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    padding: 25px;
    border-radius: 15px 15px 0 0;
    margin: -25px -25px 25px -25px;
}

.weather-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.weather-btn {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
}

.weather-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
}

.weather-table th {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.weather-table tr:hover {
    background: rgba(79, 172, 254, 0.1);
    transform: scale(1.01);
    transition: all 0.3s ease;
}

.weather-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.weather-stat-card {
    background: rgba(255, 255, 255, 0.9);
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    border-left: 4px solid #4facfe;
    position: relative;
    overflow: hidden;
}

.weather-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.weather-stat-number {
    font-size: 28px;
    font-weight: bold;
    color: #4facfe;
    margin-bottom: 5px;
}

.weather-stat-label {
    color: #666;
    font-size: 14px;
}

.weather-condition {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: bold;
}

.condition-dry {
    background: #fff3cd;
    color: #856404;
}

.condition-moderate {
    background: #d1ecf1;
    color: #0c5460;
}

.condition-wet {
    background: #d4edda;
    color: #155724;
}

.condition-hot {
    background: #f8d7da;
    color: #721c24;
}

.condition-cold {
    background: #e2e3e5;
    color: #383d41;
}

.condition-normal {
    background: #d1ecf1;
    color: #0c5460;
}

/* Charts Grid */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 25px;
    margin-bottom: 25px;
}

.chart-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    padding: 20px;
}

.chart-card h4 {
    color: #2c3e50;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.chart-container {
    position: relative;
    width: 100%;
    height: 300px;
}

.chart-container canvas {
    max-height: 280px !important;
    width: 100% !important;
}
</style>

<div class="weather-page">
    <!-- Weather Statistics -->
    <div class="weather-stats">
        <div class="weather-stat-card">
            <div class="weather-stat-number">
                <?php
                $result = $conn->query("SELECT AVG(rainfall_mm) as avg_rainfall FROM weather_history WHERE rainfall_mm IS NOT NULL");
                $avg = $result->fetch_assoc()['avg_rainfall'];
                echo number_format($avg ? $avg : 0, 1);
                ?>mm
            </div>
            <div class="weather-stat-label">Avg Rainfall</div>
        </div>
        <div class="weather-stat-card">
            <div class="weather-stat-number">
                <?php
                $result = $conn->query("SELECT AVG(temperature_celsius) as avg_temp FROM weather_history WHERE temperature_celsius IS NOT NULL");
                $avg = $result->fetch_assoc()['avg_temp'];
                echo number_format($avg ? $avg : 0, 1);
                ?>°C
            </div>
            <div class="weather-stat-label">Avg Temperature</div>
        </div>
        <div class="weather-stat-card">
            <div class="weather-stat-number">
                <?php
                $result = $conn->query("SELECT MAX(rainfall_mm) as max_rainfall FROM weather_history WHERE rainfall_mm IS NOT NULL");
                $max = $result->fetch_assoc()['max_rainfall'];
                echo number_format($max ? $max : 0, 1);
                ?>mm
            </div>
            <div class="weather-stat-label">Max Rainfall</div>
        </div>
        <div class="weather-stat-card">
            <div class="weather-stat-number">
                <?php
                $result = $conn->query("SELECT MAX(temperature_celsius) as max_temp FROM weather_history WHERE temperature_celsius IS NOT NULL");
                $max = $result->fetch_assoc()['max_temp'];
                echo number_format($max ? $max : 0, 1);
                ?>°C
            </div>
            <div class="weather-stat-label">Max Temperature</div>
        </div>
    </div>

    <div class="weather-card card">
        <div class="weather-header">
            <h2 class="page-title" style="color: white; margin: 0;">
                <i class="fas fa-cloud-sun"></i> Weather Data Management
            </h2>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">Track rainfall and temperature data throughout different locations</p>
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

        <!-- Add/Edit Weather Record Form -->
        <div class="form-container">
            <h3><?php echo $edit_record ? 'Edit Weather Record' : 'Add New Weather Record'; ?></h3>
            <form method="POST" class="weather-form">
                <input type="hidden" name="action" value="<?php echo $edit_record ? 'update' : 'create'; ?>">
                <?php if ($edit_record): ?>
                    <input type="hidden" name="weather_id" value="<?php echo $edit_record['weather_id']; ?>">
                <?php endif; ?>

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
                    <label for="rainfall_mm">Rainfall (mm)</label>
                    <input type="number" step="0.1" id="rainfall_mm" name="rainfall_mm" class="form-control" 
                           value="<?php echo $edit_record ? $edit_record['rainfall_mm'] : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="temperature_celsius">Temperature (°C)</label>
                    <input type="number" step="0.1" id="temperature_celsius" name="temperature_celsius" class="form-control" 
                           value="<?php echo $edit_record ? $edit_record['temperature_celsius'] : ''; ?>" required>
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <button type="submit" class="weather-btn">
                        <i class="fas fa-save"></i> <?php echo $edit_record ? 'Update Record' : 'Add Record'; ?>
                    </button>
                    <?php if ($edit_record): ?>
                        <a href="weather_data.php" class="btn btn-secondary" style="margin-left: 10px;">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Weather Records List -->
    <div class="weather-card card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Weather Records
            </h3>
            <div>
                <input type="text" id="searchInput" placeholder="Search records..." 
                       style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                <button onclick="filterTable()" class="btn btn-primary" style="margin-left: 10px;"><i class="fas fa-search"></i> Search</button>
                <button onclick="exportTableToCSV('weatherTable', 'weather_data.csv')" class="btn btn-success">
                    <i class="fas fa-download"></i> Export CSV
                </button>
                <a href="pdf_export.php?report=weather" class="btn btn-danger" target="_blank">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
            </div>
        </div>
        
        <div class="table-container">
            <table id="weatherTable" class="weather-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th>Rainfall (mm)</th>
                        <th>Temperature (°C)</th>
                        <th>Conditions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT wh.*, l.district_name, l.division_name 
                            FROM weather_history wh 
                            JOIN locations l ON wh.location_id = l.location_id 
                            ORDER BY wh.date DESC, wh.weather_id DESC";
                    $result = $conn->query($sql);
                    
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            // Determine weather conditions
                            $rainfall_condition = '';
                            $temp_condition = '';
                            
                            if ($row['rainfall_mm'] < 5) {
                                $rainfall_condition = '<span class="weather-condition condition-dry"><i class="fas fa-sun"></i> Dry</span>';
                            } elseif ($row['rainfall_mm'] < 20) {
                                $rainfall_condition = '<span class="weather-condition condition-moderate"><i class="fas fa-cloud"></i> Moderate</span>';
                            } else {
                                $rainfall_condition = '<span class="weather-condition condition-wet"><i class="fas fa-cloud-rain"></i> Wet</span>';
                            }
                            
                            if ($row['temperature_celsius'] < 15) {
                                $temp_condition = '<span class="weather-condition condition-cold"><i class="fas fa-thermometer-quarter"></i> Cold</span>';
                            } elseif ($row['temperature_celsius'] > 30) {
                                $temp_condition = '<span class="weather-condition condition-hot"><i class="fas fa-thermometer-full"></i> Hot</span>';
                            } else {
                                $temp_condition = '<span class="weather-condition condition-normal"><i class="fas fa-thermometer-half"></i> Normal</span>';
                            }
                            
                            echo "<tr>";
                            echo "<td>" . $row['weather_id'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['district_name'] . ', ' . $row['division_name']) . "</td>";
                            echo "<td>" . date('M d, Y', strtotime($row['date'])) . "</td>";
                            echo "<td>" . number_format($row['rainfall_mm'], 1) . "</td>";
                            echo "<td>" . number_format($row['temperature_celsius'], 1) . "</td>";
                            echo "<td>" . $rainfall_condition . " " . $temp_condition . "</td>";
                            echo "<td>";
                            echo "<a href='weather_data.php?edit=" . $row['weather_id'] . "' class='btn btn-warning' style='margin-right: 5px;'>";
                            echo "<i class='fas fa-edit'></i> Edit</a>";
                            echo "<form method='POST' style='display: inline;' onsubmit='return confirmDelete(\"Are you sure you want to delete this record?\")'>";
                            echo "<input type='hidden' name='action' value='delete'>";
                            echo "<input type='hidden' name='weather_id' value='" . $row['weather_id'] . "'>";
                            echo "<button type='submit' class='btn btn-danger'><i class='fas fa-trash'></i> Delete</button>";
                            echo "</form>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align: center;'>No weather records found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Weather Analytics Charts -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-cloud-sun"></i> Weather Analytics
            </h3>
        </div>
        
        <div class="charts-grid">
            <div class="chart-card">
                <h4><i class="fas fa-chart-line"></i> Temperature & Rainfall Trends</h4>
                <div class="chart-container">
                    <canvas id="weatherTrendChart" width="400" height="300"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <h4><i class="fas fa-chart-bar"></i> Average Weather by Location</h4>
                <div class="chart-container">
                    <canvas id="weatherLocationChart" width="400" height="300"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <h4><i class="fas fa-chart-area"></i> Monthly Weather Pattern</h4>
                <div class="chart-container">
                    <canvas id="monthlyWeatherChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize search functionality
searchTable('searchInput', 'weatherTable');

// Weather Analytics Charts
document.addEventListener('DOMContentLoaded', function() {
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js library not loaded');
        return;
    }

    <?php
    // Get weather trends over time
    $trend_query = "SELECT DATE_FORMAT(date, '%Y-%m') as month_year, 
                           AVG(rainfall_mm) as avg_rainfall, 
                           AVG(temperature_celsius) as avg_temp 
                    FROM weather_history 
                    GROUP BY DATE_FORMAT(date, '%Y-%m') 
                    ORDER BY date";
    $trend_result = $conn->query($trend_query);
    $trend_labels = [];
    $rainfall_data = [];
    $temp_data = [];
    if ($trend_result && $trend_result->num_rows > 0) {
        while ($row = $trend_result->fetch_assoc()) {
            $trend_labels[] = $row['month_year'];
            $rainfall_data[] = round($row['avg_rainfall'], 1);
            $temp_data[] = round($row['avg_temp'], 1);
        }
    }

    // Get weather by location
    $location_query = "SELECT l.district_name, 
                              AVG(wh.rainfall_mm) as avg_rainfall, 
                              AVG(wh.temperature_celsius) as avg_temp 
                       FROM weather_history wh 
                       JOIN locations l ON wh.location_id = l.location_id 
                       GROUP BY l.location_id, l.district_name 
                       ORDER BY avg_rainfall DESC";
    $location_result = $conn->query($location_query);
    $location_labels = [];
    $location_rainfall = [];
    $location_temp = [];
    if ($location_result && $location_result->num_rows > 0) {
        while ($row = $location_result->fetch_assoc()) {
            $location_labels[] = $row['district_name'];
            $location_rainfall[] = round($row['avg_rainfall'], 1);
            $location_temp[] = round($row['avg_temp'], 1);
        }
    }

    // Get monthly patterns
    $monthly_query = "SELECT MONTH(date) as month_num, 
                             MONTHNAME(date) as month_name,
                             AVG(rainfall_mm) as avg_rainfall, 
                             AVG(temperature_celsius) as avg_temp 
                      FROM weather_history 
                      GROUP BY MONTH(date), MONTHNAME(date) 
                      ORDER BY MONTH(date)";
    $monthly_result = $conn->query($monthly_query);
    $monthly_labels = [];
    $monthly_rainfall = [];
    $monthly_temp = [];
    if ($monthly_result && $monthly_result->num_rows > 0) {
        while ($row = $monthly_result->fetch_assoc()) {
            $monthly_labels[] = substr($row['month_name'], 0, 3);
            $monthly_rainfall[] = round($row['avg_rainfall'], 1);
            $monthly_temp[] = round($row['avg_temp'], 1);
        }
    }
    ?>

    // Chart data from PHP
    const trendLabels = <?php echo json_encode($trend_labels); ?>;
    const rainfallData = <?php echo json_encode($rainfall_data); ?>;
    const tempData = <?php echo json_encode($temp_data); ?>;
    const locationLabels = <?php echo json_encode($location_labels); ?>;
    const locationRainfall = <?php echo json_encode($location_rainfall); ?>;
    const locationTemp = <?php echo json_encode($location_temp); ?>;
    const monthlyLabels = <?php echo json_encode($monthly_labels); ?>;
    const monthlyRainfall = <?php echo json_encode($monthly_rainfall); ?>;
    const monthlyTemp = <?php echo json_encode($monthly_temp); ?>;

    // Chart options
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    };

    // Temperature & Rainfall Trends Chart
    const trendCtx = document.getElementById('weatherTrendChart');
    if (trendCtx && trendLabels.length > 0) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Rainfall (mm)',
                    data: rainfallData,
                    borderColor: '#4facfe',
                    backgroundColor: 'rgba(79, 172, 254, 0.1)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Temperature (°C)',
                    data: tempData,
                    borderColor: '#00f2fe',
                    backgroundColor: 'rgba(0, 242, 254, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: chartOptions
        });
    }

    // Average Weather by Location Chart
    const locationCtx = document.getElementById('weatherLocationChart');
    if (locationCtx && locationLabels.length > 0) {
        new Chart(locationCtx, {
            type: 'bar',
            data: {
                labels: locationLabels,
                datasets: [{
                    label: 'Avg Rainfall (mm)',
                    data: locationRainfall,
                    backgroundColor: 'rgba(79, 172, 254, 0.7)',
                    borderColor: '#4facfe',
                    borderWidth: 1
                }, {
                    label: 'Avg Temperature (°C)',
                    data: locationTemp,
                    backgroundColor: 'rgba(0, 242, 254, 0.7)',
                    borderColor: '#00f2fe',
                    borderWidth: 1
                }]
            },
            options: chartOptions
        });
    }

    // Monthly Weather Pattern Chart
    const monthlyCtx = document.getElementById('monthlyWeatherChart');
    if (monthlyCtx && monthlyLabels.length > 0) {
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Avg Rainfall (mm)',
                    data: monthlyRainfall,
                    backgroundColor: 'rgba(79, 172, 254, 0.7)',
                    borderColor: '#4facfe',
                    borderWidth: 1
                }, {
                    label: 'Avg Temperature (°C)',
                    data: monthlyTemp,
                    backgroundColor: 'rgba(0, 242, 254, 0.7)',
                    borderColor: '#00f2fe',
                    borderWidth: 1
                }]
            },
            options: chartOptions
        });
    }

    // If no data available, show message
    if (trendLabels.length === 0) {
        document.querySelectorAll('.chart-container').forEach(container => {
            container.innerHTML = '<div style="text-align: center; padding: 50px; color: #666;"><i class="fas fa-chart-line" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i><br>No weather data available<br><small>Add some weather records to see charts</small></div>';
        });
    }
});
            $location_temp[] = round($row['avg_temp'], 1);
        }
    }

    // Get monthly patterns
    $monthly_query = "SELECT MONTH(date) as month, 
                             AVG(rainfall_mm) as avg_rainfall, 
                             AVG(temperature_celsius) as avg_temp 
                      FROM weather_history 
                      GROUP BY MONTH(date) 
                      ORDER BY MONTH(date)";
    $monthly_result = $conn->query($monthly_query);
    $month_names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $monthly_labels = [];
    $monthly_rainfall = [];
    $monthly_temp = [];
    if ($monthly_result && $monthly_result->num_rows > 0) {
        while ($row = $monthly_result->fetch_assoc()) {
            $monthly_labels[] = $month_names[$row['month'] - 1];
            $monthly_rainfall[] = round($row['avg_rainfall'], 1);
            $monthly_temp[] = round($row['avg_temp'], 1);
        }
    }
    ?>

    const trendLabels = <?php echo json_encode($trend_labels); ?>;
    const rainfallData = <?php echo json_encode($rainfall_data); ?>;
    const tempData = <?php echo json_encode($temp_data); ?>;
    const locationLabels = <?php echo json_encode($location_labels); ?>;
    const locationRainfall = <?php echo json_encode($location_rainfall); ?>;
    const locationTemp = <?php echo json_encode($location_temp); ?>;
    const monthlyLabels = <?php echo json_encode($monthly_labels); ?>;
    const monthlyRainfall = <?php echo json_encode($monthly_rainfall); ?>;
    const monthlyTemp = <?php echo json_encode($monthly_temp); ?>;
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
                        drawOnChartArea: false,
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Weather Trends Over Time'
                }
            }
        }
    });

    // Weather by Location Chart
    const locationCtx = document.getElementById('weatherLocationChart').getCont            labels: locationLabels,
            datasets: [{
                label: "Average Rainfall (mm)",
                data: locationRainfall,
                backgroundColor: "#36A2EB",
                yAxisID: "y"
            }, {
                label: "Average Temperature (°C)",
                data: locationTemp,
                backgroundColor: "#FF6384",
                yAxisID: "y1"
            }]       options: {
            responsive: true,
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
                        drawOnChartArea: false,
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Average Weather Conditions by Location'
                }
            }
        }
    });

    // Monthly Weather P    // Weather Trend Chart
    const trendCtx = document.getElementById("weatherTrendChart").getContext("2d");
    new Chart(trendCtx, {
        type: "line",
        data:             labels: monthlyLabels,
            datasets: [{
                label: "Average Rainfall (mm)",
                data: monthlyRainfall,
                borderColor: "#36A2EB",
                backgroundColor: "rgba(54, 162, 235, 0.2)",
                fill: true,
                yAxisID: "y"
            }, {
                label: "Average Temperature (°C)",
                data: monthlyTemp,
                borderColor: "#FF6384",
                backgroundColor: "rgba(255, 99, 132, 0.2)",
                fill: true,
                yAxisID: "y1"
            }] true,
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Monthly Weather Patterns'
                }
            }
        }
    });
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
    
    let url = 'pdf_export.php?page=weather&year=' + year;
    if (product) url += '&product=' + product;
    
    window.open(url, '_blank');
}    </div>

    <!-- Weather Analytics Section -->
    <div class="weather-card card" style="margin-top: 30px;">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-line"></i> Weather Analytics
            </h3>
        </div>
        
        <div class="row" style="margin: 20px 0;">
            <div class="col-md-6">
                <div class="chart-container">
                    <h4><i class="fas fa-chart-line"></i> Temperature & Rainfall Trends</h4>
                    <canvas id="weatherTrendChart" width="400" height="200"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h4><i class="fas fa-map-marker-alt"></i> Average Weather by Location</h4>
                    <canvas id="weatherLocationChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <div class="row" style="margin: 20px 0;">
            <div class="col-md-12">
                <div class="chart-container">
                    <h4><i class="fas fa-calendar-alt"></i> Monthly Weather Pattern</h4>
                    <canvas id="monthlyWeatherChart" width="800" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Prepare data for weather charts
$weather_trend_data = $conn->query("
    SELECT DATE_FORMAT(date, '%Y-%m') as month, 
           AVG(temperature_celsius) as avg_temp, 
           AVG(rainfall_mm) as avg_rainfall 
    FROM weather_history 
    GROUP BY DATE_FORMAT(date, '%Y-%m') 
    ORDER BY month
");

$weather_location_data = $conn->query("
    SELECT l.district_name, 
           AVG(wh.temperature_celsius) as avg_temp, 
           AVG(wh.rainfall_mm) as avg_rainfall 
    FROM weather_history wh 
    JOIN locations l ON wh.location_id = l.location_id 
    GROUP BY l.district_name
");

$monthly_pattern_data = $conn->query("
    SELECT MONTH(date) as month, 
           AVG(temperature_celsius) as avg_temp, 
           AVG(rainfall_mm) as avg_rainfall 
    FROM weather_history 
    GROUP BY MONTH(date) 
    ORDER BY month
");
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prepare weather trend data
    const weatherTrendLabels = [];
    const weatherTrendTemp = [];
    const weatherTrendRainfall = [];
    
    <?php
    if ($weather_trend_data->num_rows > 0) {
        while ($row = $weather_trend_data->fetch_assoc()) {
            echo "weatherTrendLabels.push('" . $row['month'] . "');\n";
            echo "weatherTrendTemp.push(" . ($row['avg_temp'] ? $row['avg_temp'] : 0) . ");\n";
            echo "weatherTrendRainfall.push(" . ($row['avg_rainfall'] ? $row['avg_rainfall'] : 0) . ");\n";
        }
    } else {
        // Fallback sample data
        echo "weatherTrendLabels.push('2024-01', '2024-02', '2024-03', '2024-04', '2024-05', '2024-06');\n";
        echo "weatherTrendTemp.push(18.5, 22.3, 26.1, 28.7, 30.2, 29.8);\n";
        echo "weatherTrendRainfall.push(12.5, 8.2, 15.7, 22.1, 45.3, 78.9);\n";
    }
    ?>

    // Weather Trend Chart
    const weatherTrendCtx = document.getElementById('weatherTrendChart').getContext('2d');
    new Chart(weatherTrendCtx, {
        type: 'line',
        data: {
            labels: weatherTrendLabels,
            datasets: [{
                label: 'Temperature (°C)',
                data: weatherTrendTemp,
                borderColor: '#ff6b6b',
                backgroundColor: 'rgba(255, 107, 107, 0.1)',
                yAxisID: 'y'
            }, {
                label: 'Rainfall (mm)',
                data: weatherTrendRainfall,
                borderColor: '#4ecdc4',
                backgroundColor: 'rgba(78, 205, 196, 0.1)',
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Temperature (°C)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Rainfall (mm)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });

    // Prepare weather location data
    const weatherLocationLabels = [];
    const weatherLocationTemp = [];
    const weatherLocationRainfall = [];
    
    <?php
    $weather_location_data->data_seek(0);
    if ($weather_location_data->num_rows > 0) {
        while ($row = $weather_location_data->fetch_assoc()) {
            echo "weatherLocationLabels.push('" . $row['district_name'] . "');\n";
            echo "weatherLocationTemp.push(" . ($row['avg_temp'] ? $row['avg_temp'] : 0) . ");\n";
            echo "weatherLocationRainfall.push(" . ($row['avg_rainfall'] ? $row['avg_rainfall'] : 0) . ");\n";
        }
    } else {
        // Fallback sample data
        echo "weatherLocationLabels.push('Dhaka', 'Chittagong', 'Rajshahi', 'Sylhet', 'Barisal');\n";
        echo "weatherLocationTemp.push(26.5, 28.2, 25.8, 24.9, 27.1);\n";
        echo "weatherLocationRainfall.push(35.2, 42.8, 28.5, 55.7, 48.3);\n";
    }
    ?>

    // Weather Location Chart
    const weatherLocationCtx = document.getElementById('weatherLocationChart').getContext('2d');
    new Chart(weatherLocationCtx, {
        type: 'bar',
        data: {
            labels: weatherLocationLabels,
            datasets: [{
                label: 'Avg Temperature (°C)',
                data: weatherLocationTemp,
                backgroundColor: 'rgba(255, 107, 107, 0.8)',
                borderColor: '#ff6b6b',
                borderWidth: 1
            }, {
                label: 'Avg Rainfall (mm)',
                data: weatherLocationRainfall,
                backgroundColor: 'rgba(78, 205, 196, 0.8)',
                borderColor: '#4ecdc4',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Prepare monthly pattern data
    const monthlyPatternLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const monthlyPatternTemp = new Array(12).fill(0);
    const monthlyPatternRainfall = new Array(12).fill(0);
    
    <?php
    $monthly_pattern_data->data_seek(0);
    if ($monthly_pattern_data->num_rows > 0) {
        while ($row = $monthly_pattern_data->fetch_assoc()) {
            $month_index = $row['month'] - 1;
            echo "monthlyPatternTemp[$month_index] = " . ($row['avg_temp'] ? $row['avg_temp'] : 0) . ";\n";
            echo "monthlyPatternRainfall[$month_index] = " . ($row['avg_rainfall'] ? $row['avg_rainfall'] : 0) . ";\n";
        }
    } else {
        // Fallback sample data
        echo "monthlyPatternTemp = [20.5, 23.2, 26.8, 29.1, 30.5, 29.8, 28.9, 28.7, 27.5, 25.2, 22.8, 21.1];\n";
        echo "monthlyPatternRainfall = [15.2, 18.5, 25.7, 45.2, 78.9, 125.3, 165.7, 142.8, 98.5, 52.3, 28.7, 19.8];\n";
    }
    ?>

    // Monthly Weather Pattern Chart
    const monthlyWeatherCtx = document.getElementById('monthlyWeatherChart').getContext('2d');
    new Chart(monthlyWeatherCtx, {
        type: 'line',
        data: {
            labels: monthlyPatternLabels,
            datasets: [{
                label: 'Temperature (°C)',
                data: monthlyPatternTemp,
                borderColor: '#ff6b6b',
                backgroundColor: 'rgba(255, 107, 107, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Rainfall (mm)',
                data: monthlyPatternRainfall,
                borderColor: '#4ecdc4',
                backgroundColor: 'rgba(78, 205, 196, 0.1)',
                fill: true,
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Temperature (°C)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Rainfall (mm)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
});
</script>

<?php include 'templates/footer.php'; ?>

<script>
function filterTable() {
    var input, filter, table, tr, td, i, txtValue;
    input = document.getElementById("searchInput");
    filter = input.value.toUpperCase();
    table = document.getElementById("weatherTable");
    tr = table.getElementsByTagName("tr");
    for (i = 0; i < tr.length; i++) {
        td = tr[i].getElementsByTagName("td")[1]; // Search by Location
        if (td) {
            txtValue = td.textContent || td.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}
</script>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Temperature & Rainfall Trends Chart
    var tempRainfallCtx = document.getElementById("tempRainfallChart").getContext("2d");
    var tempRainfallChart;

    // Average Weather by Location Chart
    var avgWeatherLocationCtx = document.getElementById("avgWeatherLocationChart").getContext("2d");
    var avgWeatherLocationChart;

    // Monthly Weather Pattern Chart
    var monthlyWeatherCtx = document.getElementById("monthlyWeatherChart").getContext("2d");
    var monthlyWeatherChart;

    function renderWeatherCharts(data) {
        // Destroy existing charts if they exist
        if (tempRainfallChart) tempRainfallChart.destroy();
        if (avgWeatherLocationChart) avgWeatherLocationChart.destroy();
        if (monthlyWeatherChart) monthlyWeatherChart.destroy();

        // Process data for Temperature & Rainfall Trends Chart
        var dates = [];
        var rainfallData = [];
        var temperatureData = [];

        data.sort((a, b) => new Date(a.date) - new Date(b.date)); // Sort by date

        data.forEach(function(row) {
            dates.push(new Date(row.date).toLocaleDateString());
            rainfallData.push(parseFloat(row.rainfall_mm));
            temperatureData.push(parseFloat(row.temperature_celsius));
        });

        tempRainfallChart = new Chart(tempRainfallCtx, {
            type: "line",
            data: {
                labels: dates,
                datasets: [{
                    label: "Rainfall (mm)",
                    data: rainfallData,
                    borderColor: "#4facfe",
                    backgroundColor: "rgba(79, 172, 254, 0.2)",
                    fill: false,
                    yAxisID: "y-rainfall",
                },
                {
                    label: "Temperature (°C)",
                    data: temperatureData,
                    borderColor: "#00f2fe",
                    backgroundColor: "rgba(0, 242, 254, 0.2)",
                    fill: false,
                    yAxisID: "y-temperature",
                }, ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "top",
                    },
                    title: {
                        display: true,
                        text: "Temperature & Rainfall Trends",
                    },
                },
                scales: {
                    "y-rainfall": {
                        type: "linear",
                        position: "left",
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: "Rainfall (mm)",
                        },
                    },
                    "y-temperature": {
                        type: "linear",
                        position: "right",
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: "Temperature (°C)",
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                },
            },
        });

        // Process data for Average Weather by Location Chart
        var locationLabels = [];
        var avgRainfallByLocation = {};
        var avgTempByLocation = {};
        var locationCounts = {};

        data.forEach(function(row) {
            var locationName = row.district_name + ", " + row.division_name;
            if (!avgRainfallByLocation[locationName]) {
                avgRainfallByLocation[locationName] = 0;
                avgTempByLocation[locationName] = 0;
                locationCounts[locationName] = 0;
            }
            avgRainfallByLocation[locationName] += parseFloat(row.rainfall_mm);
            avgTempByLocation[locationName] += parseFloat(row.temperature_celsius);
            locationCounts[locationName]++;
        });

        for (var loc in avgRainfallByLocation) {
            locationLabels.push(loc);
            avgRainfallByLocation[loc] /= locationCounts[loc];
            avgTempByLocation[loc] /= locationCounts[loc];
        }

        avgWeatherLocationChart = new Chart(avgWeatherLocationCtx, {
            type: "bar",
            data: {
                labels: locationLabels,
                datasets: [{
                    label: "Avg Rainfall (mm)",
                    data: Object.values(avgRainfallByLocation),
                    backgroundColor: "rgba(79, 172, 254, 0.7)",
                    borderColor: "rgba(79, 172, 254, 1)",
                    borderWidth: 1,
                },
                {
                    label: "Avg Temperature (°C)",
                    data: Object.values(avgTempByLocation),
                    backgroundColor: "rgba(0, 242, 254, 0.7)",
                    borderColor: "rgba(0, 242, 254, 1)",
                    borderWidth: 1,
                }, ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "top",
                    },
                    title: {
                        display: true,
                        text: "Average Weather by Location",
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: "Value",
                        },
                    },
                },
            },
        });

        // Process data for Monthly Weather Pattern Chart
        var monthlyRainfall = {};
        var monthlyTemperature = {};
        var monthlyCounts = {};

        data.forEach(function(row) {
            var month = new Date(row.date).toLocaleString("default", { month: "short" });
            if (!monthlyRainfall[month]) {
                monthlyRainfall[month] = 0;
                monthlyTemperature[month] = 0;
                monthlyCounts[month] = 0;
            }
            monthlyRainfall[month] += parseFloat(row.rainfall_mm);
            monthlyTemperature[month] += parseFloat(row.temperature_celsius);
            monthlyCounts[month]++;
        });

        var monthOrder = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        var sortedMonths = monthOrder.filter(month => monthlyRainfall.hasOwnProperty(month));

        var monthlyRainfallData = sortedMonths.map(month => monthlyRainfall[month] / monthlyCounts[month]);
        var monthlyTemperatureData = sortedMonths.map(month => monthlyTemperature[month] / monthlyCounts[month]);

        monthlyWeatherChart = new Chart(monthlyWeatherCtx, {
            type: "bar",
            data: {
                labels: sortedMonths,
                datasets: [{
                    label: "Avg Rainfall (mm)",
                    data: monthlyRainfallData,
                    backgroundColor: "rgba(79, 172, 254, 0.7)",
                    borderColor: "rgba(79, 172, 254, 1)",
                    borderWidth: 1,
                },
                {
                    label: "Avg Temperature (°C)",
                    data: monthlyTemperatureData,
                    backgroundColor: "rgba(0, 242, 254, 0.7)",
                    borderColor: "rgba(0, 242, 254, 1)",
                    borderWidth: 1,
                }, ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "top",
                    },
                    title: {
                        display: true,
                        text: "Monthly Weather Pattern",
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: "Value",
                        },
                    },
                },
            },
        });
    }

    // Fetch data and render charts on page load
    document.addEventListener("DOMContentLoaded", function() {
        fetchWeatherData();
    });

    function fetchWeatherData() {
        fetch("api.php?action=get_weather_data")
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderWeatherCharts(data.data);
                } else {
                    console.error("Error fetching weather data:", data.message);
                }
            })
            .catch(error => console.error("Fetch error:", error));
    }
</script>


<?php include 'templates/footer.php'; ?>

