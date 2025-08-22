<?php 
include 'templates/header.php';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $product_id = sanitizeInput($_POST['product_id']);
                $location_id = sanitizeInput($_POST['location_id']);
                $year = sanitizeInput($_POST['year']);
                $season = sanitizeInput($_POST['season']);
                $temperature = sanitizeInput($_POST['temperature']);
                $acreage = sanitizeInput($_POST['acreage']);
                $quantity_produced = sanitizeInput($_POST['quantity_produced']);
                
                $sql = "INSERT INTO production_history (product_id, location_id, year, season, temperature, acreage, quantity_produced) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iissddd", $product_id, $location_id, $year, $season, $temperature, $acreage, $quantity_produced);
                
                if ($stmt->execute()) {
                    $success_message = "Production record added successfully!";
                } else {
                    $error_message = "Error adding production record: " . $conn->error;
                }
                break;
                
            case 'update':
                $production_id = $_POST['production_id'];
                $product_id = sanitizeInput($_POST['product_id']);
                $location_id = sanitizeInput($_POST['location_id']);
                $year = sanitizeInput($_POST['year']);
                $season = sanitizeInput($_POST['season']);
                $temperature = sanitizeInput($_POST['temperature']);
                $acreage = sanitizeInput($_POST['acreage']);
                $quantity_produced = sanitizeInput($_POST['quantity_produced']);
                
                $sql = "UPDATE production_history SET product_id=?, location_id=?, year=?, season=?, temperature=?, acreage=?, quantity_produced=? WHERE production_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iissdddi", $product_id, $location_id, $year, $season, $temperature, $acreage, $quantity_produced, $production_id);
                
                if ($stmt->execute()) {
                    $success_message = "Production record updated successfully!";
                } else {
                    $error_message = "Error updating production record: " . $conn->error;
                }
                break;
                
            case 'delete':
                $production_id = $_POST['production_id'];
                $sql = "DELETE FROM production_history WHERE production_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $production_id);
                
                if ($stmt->execute()) {
                    $success_message = "Production record deleted successfully!";
                } else {
                    $error_message = "Error deleting production record: " . $conn->error;
                }
                break;
        }
    }
}

// Get record for editing
$edit_record = null;
if (isset($_GET['edit'])) {
    $production_id = $_GET['edit'];
    $sql = "SELECT * FROM production_history WHERE production_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $production_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_record = $result->fetch_assoc();
}

// Get products and locations for dropdowns
$products = $conn->query("SELECT product_id, name FROM products ORDER BY name");
$locations = $conn->query("SELECT location_id, district_name, division_name FROM locations ORDER BY district_name");
?>

<style>
/* Production History Page Specific Styles */
.production-page {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    min-height: calc(100vh - 80px);
    padding: 0;
    margin: -30px;
    padding: 30px;
}

.production-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.production-header {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
    padding: 25px;
    border-radius: 15px 15px 0 0;
    margin: -25px -25px 25px -25px;
}

.production-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.production-btn {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
}

.production-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(17, 153, 142, 0.4);
}

.production-table th {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
}

.production-table tr:hover {
    background: rgba(17, 153, 142, 0.1);
    transform: scale(1.01);
    transition: all 0.3s ease;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.stat-card {
    background: rgba(255, 255, 255, 0.9);
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    border-left: 4px solid #11998e;
}

.stat-number {
    font-size: 28px;
    font-weight: bold;
    color: #11998e;
    margin-bottom: 5px;
}

.stat-label {
    color: #666;
    font-size: 14px;
}
</style>

<div class="production-page">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">
                <?php
                $result = $conn->query("SELECT COUNT(*) as count FROM production_history");
                echo $result->fetch_assoc()['count'];
                ?>
            </div>
            <div class="stat-label">Total Records</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">
                <?php
                $result = $conn->query("SELECT SUM(acreage) as total FROM production_history");
                $total = $result->fetch_assoc()['total'];
                echo number_format($total ? $total : 0);
                ?>
            </div>
            <div class="stat-label">Total Acreage</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">
                <?php
                $result = $conn->query("SELECT SUM(quantity_produced) as total FROM production_history");
                $total = $result->fetch_assoc()['total'];
                echo number_format($total ? $total : 0);
                ?>
            </div>
            <div class="stat-label">Total Production (tons)</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">
                <?php
                $result = $conn->query("SELECT AVG(quantity_produced/acreage) as avg_yield FROM production_history WHERE acreage > 0");
                $avg = $result->fetch_assoc()['avg_yield'];
                echo number_format($avg ? $avg : 0, 2);
                ?>
            </div>
            <div class="stat-label">Avg Yield (tons/acre)</div>
        </div>
    </div>

    <div class="production-card card">
        <div class="production-header">
            <h2 class="page-title" style="color: white; margin: 0;">
                <i class="fas fa-chart-line"></i> Production History Management
            </h2>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">Track production data by district/division including acreage and quantity produced</p>
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

        <!-- Add/Edit Production Record Form -->
        <div class="form-container">
            <h3><?php echo $edit_record ? 'Edit Production Record' : 'Add New Production Record'; ?></h3>
            <form method="POST" class="production-form">
                <input type="hidden" name="action" value="<?php echo $edit_record ? 'update' : 'create'; ?>">
                <?php if ($edit_record): ?>
                    <input type="hidden" name="production_id" value="<?php echo $edit_record['production_id']; ?>">
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
                    <label for="year">Year</label>
                    <input type="number" id="year" name="year" class="form-control" min="2000" max="2030"
                           value="<?php echo $edit_record ? $edit_record['year'] : date('Y'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="season">Season</label>
                    <select id="season" name="season" class="form-control" required>
                        <option value="">Select Season</option>
                        <option value="Spring" <?php echo ($edit_record && isset($edit_record['season']) && $edit_record['season'] == 'Spring') ? 'selected' : ''; ?>>Spring</option>
                        <option value="Summer" <?php echo ($edit_record && isset($edit_record['season']) && $edit_record['season'] == 'Summer') ? 'selected' : ''; ?>>Summer</option>
                        <option value="Fall" <?php echo ($edit_record && isset($edit_record['season']) && $edit_record['season'] == 'Fall') ? 'selected' : ''; ?>>Fall</option>
                        <option value="Winter" <?php echo ($edit_record && isset($edit_record['season']) && $edit_record['season'] == 'Winter') ? 'selected' : ''; ?>>Winter</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="temperature">Avg Temperature (°C)</label>
                    <input type="number" step="0.1" id="temperature" name="temperature" class="form-control" 
                           value="<?php echo $edit_record && isset($edit_record['temperature']) ? $edit_record['temperature'] : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="acreage">Acreage</label>
                    <input type="number" step="0.01" id="acreage" name="acreage" class="form-control" 
                           value="<?php echo $edit_record ? $edit_record['acreage'] : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="quantity_produced">Quantity Produced (tons)</label>
                    <input type="number" step="0.01" id="quantity_produced" name="quantity_produced" class="form-control" 
                           value="<?php echo $edit_record ? $edit_record['quantity_produced'] : ''; ?>" required>
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <button type="submit" class="production-btn">
                        <i class="fas fa-save"></i> <?php echo $edit_record ? 'Update Record' : 'Add Record'; ?>
                    </button>
                    <?php if ($edit_record): ?>
                        <a href="production_history.php" class="btn btn-secondary" style="margin-left: 10px;">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Production Records List -->
    <div class="production-card card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Production Records
            </h3>
            <div>
                <input type="text" id="searchInput" placeholder="Search records..." 
                       style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                <button onclick="filterTable()" class="btn btn-primary" style="margin-left: 10px;"><i class="fas fa-search"></i> Search</button>
                <button onclick="exportTableToCSV('productionTable', 'production_history.csv')" class="btn btn-success">
                    <i class="fas fa-download"></i> Export CSV
                </button>
                <a href="pdf_export.php?report=production" class="btn btn-danger" target="_blank">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
            </div>
        </div>
        
        <div class="table-container">
            <table id="productionTable" class="production-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Location</th>
                        <th>Year</th>
                        <th>Season</th>
                        <th>Temperature (°C)</th>
                        <th>Acreage</th>
                        <th>Quantity (tons)</th>
                        <th>Yield (tons/acre)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT ph.*, p.name as product_name, l.district_name, l.division_name 
                            FROM production_history ph 
                            JOIN products p ON ph.product_id = p.product_id 
                            JOIN locations l ON ph.location_id = l.location_id 
                            ORDER BY ph.year DESC, ph.production_id DESC";
                    $result = $conn->query($sql);
                    
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $yield = $row['acreage'] > 0 ? $row['quantity_produced'] / $row['acreage'] : 0;
                            echo "<tr>";
                            echo "<td>" . $row['production_id'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['district_name'] . ', ' . $row['division_name']) . "</td>";
                            echo "<td>" . $row['year'] . "</td>";
                            echo "<td>" . (isset($row['season']) ? htmlspecialchars($row['season']) : 'N/A') . "</td>";
                            echo "<td>" . (isset($row['temperature']) ? number_format($row['temperature'], 1) : 'N/A') . "</td>";
                            echo "<td>" . number_format($row['acreage'], 2) . "</td>";
                            echo "<td>" . number_format($row['quantity_produced'], 2) . "</td>";
                            echo "<td>" . number_format($yield, 2) . "</td>";
                            echo "<td>";
                            echo "<a href='production_history.php?edit=" . $row['production_id'] . "' class='btn btn-warning' style='margin-right: 5px;'>";
                            echo "<i class='fas fa-edit'></i> Edit</a>";
                            echo "<form method='POST' style='display: inline;' onsubmit='return confirmDelete(\"Are you sure you want to delete this record?\")'>";
                            echo "<input type='hidden' name='action' value='delete'>";
                            echo "<input type='hidden' name='production_id' value='" . $row['production_id'] . "'>";
                            echo "<button type='submit' class='btn btn-danger'><i class='fas fa-trash'></i> Delete</button>";
                            echo "</form>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10' style='text-align: center;'>No production records found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Production Analytics Charts -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-line"></i> Production Analytics
            </h3>
        </div>
        
        <div class="charts-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; padding: 20px;">
            <div class="chart-card" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);">
                <h4><i class="fas fa-chart-pie"></i> Production by Product</h4>
                <div class="chart-container" style="height: 300px;">
                    <canvas id="productionByProductChart"></canvas>
                </div>
            </div>

            <div class="chart-card" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);">
                <h4><i class="fas fa-chart-line"></i> Production Trend by Year</h4>
                <div class="chart-container" style="height: 300px;">
                    <canvas id="productionTrendByYearChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function filterTable() {
    var input, filter, table, tr, td, i, txtValue;
    input = document.getElementById("searchInput");
    filter = input.value.toUpperCase();
    table = document.getElementById("productionTable");
    tr = table.getElementsByTagName("tr");
    for (i = 0; i < tr.length; i++) {
        td = tr[i].getElementsByTagName("td")[1]; // Search by Product Name
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

// Production by Product Chart
var productionByProductCtx = document.getElementById("productionByProductChart").getContext("2d");
var productionByProductChart;

// Production Trend by Year Chart
var productionTrendByYearCtx = document.getElementById("productionTrendByYearChart").getContext("2d");
var productionTrendByYearChart;

function renderProductionCharts(data) {
    // Destroy existing charts if they exist
    if (productionByProductChart) productionByProductChart.destroy();
    if (productionTrendByYearChart) productionTrendByYearChart.destroy();

    // Process data for Production by Product Chart
    var productLabels = [];
    var productData = [];
    var productColors = [];
    var productBorderColors = [];

    var productMap = {};
    data.forEach(function(row) {
        if (productMap[row.product_name]) {
            productMap[row.product_name] += parseFloat(row.quantity_produced);
        } else {
            productMap[row.product_name] = parseFloat(row.quantity_produced);
        }
    });

    for (var product in productMap) {
        productLabels.push(product);
        productData.push(productMap[product]);
        var color = getRandomColor();
        productColors.push(color);
        productBorderColors.push(color.replace("0.2", "1"));
    }

    productionByProductChart = new Chart(productionByProductCtx, {
        type: "pie",
        data: {
            labels: productLabels,
            datasets: [{
                data: productData,
                backgroundColor: productColors,
                borderColor: productBorderColors,
                borderWidth: 1,
            }, ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: "right",
                },
                title: {
                    display: true,
                    text: "Production by Product",
                },
            },
        },
    });

    // Process data for Production Trend by Year Chart
    var yearLabels = [];
    var yearData = [];
    var yearMap = {};
    data.forEach(function(row) {
        if (yearMap[row.year]) {
            yearMap[row.year] += parseFloat(row.quantity_produced);
        } else {
            yearMap[row.year] = parseFloat(row.quantity_produced);
        }
    });

    // Sort years
    var sortedYears = Object.keys(yearMap).sort();
    sortedYears.forEach(function(year) {
        yearLabels.push(year);
        yearData.push(yearMap[year]);
    });

    productionTrendByYearChart = new Chart(productionTrendByYearCtx, {
        type: "line",
        data: {
            labels: yearLabels,
            datasets: [{
                label: "Total Production (tons)",
                data: yearData,
                borderColor: "#11998e",
                backgroundColor: "rgba(17, 153, 142, 0.2)",
                fill: true,
                tension: 0.3,
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
                    text: "Production Trend by Year",
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: "Quantity Produced (tons)",
                    },
                },
                x: {
                    title: {
                        display: true,
                        text: "Year",
                    },
                },
            },
        },
    });
}

// Fetch data and render charts on page load
document.addEventListener("DOMContentLoaded", function() {
    fetchProductionData();
});

function fetchProductionData() {
    fetch("api.php?action=get_production_data")
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderProductionCharts(data.data);
            } else {
                console.error("Error fetching production data:", data.message);
            }
        })
        .catch(error => console.error("Fetch error:", error));
}

function getRandomColor() {
    var r = Math.floor(Math.random() * 255);
    var g = Math.floor(Math.random() * 255);
    var b = Math.floor(Math.random() * 255);
    return "rgba(" + r + "," + g + "," + b + ",0.2)";
}
</script>

<?php include 'templates/footer.php'; ?>