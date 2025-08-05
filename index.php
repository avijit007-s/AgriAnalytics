<?php include 'templates/header.php'; ?>

<div class="page-header">
    <h2 class="page-title">Dashboard</h2>
    <p class="page-description">Overview of agricultural data and system statistics</p>
</div>

<div class="dashboard-cards">
    <div class="dashboard-card">
        <div class="icon" style="color: #27ae60;">
            <i class="fas fa-leaf"></i>
        </div>
        <h3>
            <?php
            try {
                $result = $conn->query("SELECT COUNT(*) as count FROM products");
                $count = $result->fetch_assoc()['count'];
                echo $count;
            } catch (Exception $e) {
                echo "0";
            }
            ?>
        </h3>
        <p>Total Products</p>
    </div>
    
    <div class="dashboard-card">
        <div class="icon" style="color: #3498db;">
            <i class="fas fa-map-marker-alt"></i>
        </div>
        <h3>
            <?php
            try {
                $result = $conn->query("SELECT COUNT(DISTINCT location) as count FROM production");
                $count = $result->fetch_assoc()['count'];
                echo $count;
            } catch (Exception $e) {
                echo "0";
            }
            ?>
        </h3>
        <p>Locations Tracked</p>
    </div>
    
    <div class="dashboard-card">
        <div class="icon" style="color: #f39c12;">
            <i class="fas fa-chart-line"></i>
        </div>
        <h3>
            <?php
            try {
                $result = $conn->query("SELECT COUNT(*) as count FROM production");
                $count = $result->fetch_assoc()['count'];
                echo $count;
            } catch (Exception $e) {
                echo "0";
            }
            ?>
        </h3>
        <p>Production Records</p>
    </div>
    
    <div class="dashboard-card">
        <div class="icon" style="color: #e74c3c;">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <h3>
            <?php
            try {
                $result = $conn->query("SELECT COUNT(*) as count FROM prices");
                $count = $result->fetch_assoc()['count'];
                echo $count;
            } catch (Exception $e) {
                echo "0";
            }
            ?>
        </h3>
        <p>Price Records</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recent Activities</h3>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Activity</th>
                    <th>Details</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo date('Y-m-d'); ?></td>
                    <td>System Login</td>
                    <td>Admin user logged into the system</td>
                    <td><span style="color: #27ae60;">Active</span></td>
                </tr>
                <tr>
                    <td><?php echo date('Y-m-d', strtotime('-1 day')); ?></td>
                    <td>Data Update</td>
                    <td>Price history data updated</td>
                    <td><span style="color: #27ae60;">Completed</span></td>
                </tr>
                <tr>
                    <td><?php echo date('Y-m-d', strtotime('-2 days')); ?></td>
                    <td>Report Generation</td>
                    <td>Monthly production report generated</td>
                    <td><span style="color: #27ae60;">Completed</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quick Actions</h3>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        <a href="product_info.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Product
        </a>
        <a href="production_history.php" class="btn btn-success">
            <i class="fas fa-chart-line"></i> View Production Data
        </a>
        <a href="price_trends.php" class="btn btn-warning">
            <i class="fas fa-dollar-sign"></i> Check Price Trends
        </a>
        <a href="weather_data.php" class="btn btn-primary">
            <i class="fas fa-cloud-sun"></i> Weather Analysis
        </a>
    </div>
</div>

<?php include 'templates/footer.php'; ?>

