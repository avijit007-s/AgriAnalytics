<?php
session_start();
include_once 'includes/db_connection.php';
include_once 'includes/functions.php';

requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agricultural Analysis System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/charts.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-seedling"></i> AgriAnalysis</h2>
            <p>Welcome, <?php echo $_SESSION['username']; ?>!</p>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li><a href="index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="product_info.php" class="nav-link"><i class="fas fa-leaf"></i> Product Information</a></li>
                <li><a href="production_history.php" class="nav-link"><i class="fas fa-chart-line"></i> Production History</a></li>
                <li><a href="price_trends.php" class="nav-link"><i class="fas fa-dollar-sign"></i> Price Trends</a></li>
                <li><a href="weather_data.php" class="nav-link"><i class="fas fa-cloud-sun"></i> Weather Data</a></li>
                <li><a href="surplus_deficit.php" class="nav-link"><i class="fas fa-balance-scale"></i> Surplus/Deficit</a></li>
                <li><a href="consumption_patterns.php" class="nav-link"><i class="fas fa-chart-pie"></i> Consumption Patterns</a></li>
                <li><a href="supply_demand.php" class="nav-link"><i class="fas fa-exchange-alt"></i> Supply vs Demand</a></li>
                <li><a href="charts.php" class="nav-link"><i class="fas fa-chart-bar"></i> Advanced Analytics</a></li>
            </ul>
        </nav>
        
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <header class="top-header">
            <h1>Agricultural Demand and Supply Analysis</h1>
            <div class="header-actions">
                <span class="date"><?php echo date('F j, Y'); ?></span>
            </div>
        </header>
        <main class="content-area">

