<?php
session_start();
require_once("includes/db_connection.php");
require_once("includes/functions.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include FPDF library
require_once("fpdf.php");

// Enhanced PDF class with better formatting
class EnhancedPDF extends FPDF {
    private $title;
    
    function __construct($title = '') {
        parent::__construct();
        $this->title = $title;
    }
    
    // Page header
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(44, 62, 80);
        $this->Cell(0, 10, 'Agricultural Analysis System', 0, 1, 'C');
        
        if ($this->title) {
            $this->SetFont('Arial', 'B', 14);
            $this->SetTextColor(52, 152, 219);
            $this->Cell(0, 8, $this->title, 0, 1, 'C');
        }
        
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 6, 'Generated on: ' . date('F j, Y \a\t g:i A'), 0, 1, 'C');
        $this->Ln(5);
        
        // Line
        $this->SetDrawColor(52, 152, 219);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);
    }
    
    // Page footer
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' - Agricultural Analysis System', 0, 0, 'C');
    }
    
    // Enhanced table
    function EnhancedTable($headers, $data, $col_widths = null) {
        if (empty($data)) {
            $this->SetFont('Arial', '', 12);
            $this->SetTextColor(128, 128, 128);
            $this->Cell(0, 20, 'No data available for this report.', 0, 1, 'C');
            return;
        }
        
        // Calculate column widths if not provided
        if (!$col_widths) {
            $col_widths = array_fill(0, count($headers), 190 / count($headers));
        }
        
        // Headers
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(44, 62, 80);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(255, 255, 255);
        
        for ($i = 0; $i < count($headers); $i++) {
            $this->Cell($col_widths[$i], 8, $headers[$i], 1, 0, 'C', true);
        }
        $this->Ln();
        
        // Data rows
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0, 0, 0);
        $this->SetDrawColor(200, 200, 200);
        
        $fill = false;
        $row_count = 0;
        
        foreach ($data as $row) {
            if ($row_count >= 40) { // Limit rows per page
                $this->SetFont('Arial', 'I', 9);
                $this->SetTextColor(128, 128, 128);
                $this->Cell(0, 6, '... and ' . (count($data) - 40) . ' more records (showing first 40)', 0, 1, 'C');
                break;
            }
            
            $this->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
            
            $i = 0;
            foreach ($row as $cell) {
                $cell_text = $cell !== null ? substr(strval($cell), 0, 20) : '';
                $this->Cell($col_widths[$i], 6, $cell_text, 1, 0, 'C', true);
                $i++;
            }
            $this->Ln();
            $fill = !$fill;
            $row_count++;
        }
    }
    
    // Add summary section
    function AddSummary($summary_data) {
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(44, 62, 80);
        $this->Cell(0, 8, 'Summary Statistics', 0, 1, 'L');
        $this->Ln(3);
        
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 0, 0);
        
        foreach ($summary_data as $label => $value) {
            $this->Cell(60, 6, $label . ':', 0, 0, 'L');
            $this->Cell(0, 6, $value, 0, 1, 'L');
        }
    }
}

if (isset($_GET["page"])) {
    $page = $_GET["page"];
    $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
    $product = isset($_GET['product']) ? $_GET['product'] : '';
    
    try {
        switch ($page) {
            case "charts":
                generateChartsReport($conn, $year, $product);
                break;
            case "products":
                generateProductsReport($conn);
                break;
            case "production":
                generateProductionReport($conn, $year, $product);
                break;
            case "prices":
                generatePricesReport($conn, $year, $product);
                break;
            case "weather":
                generateWeatherReport($conn, $year);
                break;
            case "surplus_deficit":
                generateSurplusDeficitReport($conn, $year, $product);
                break;
            case "consumption":
                generateConsumptionReport($conn, $year, $product);
                break;
            case "supply_demand":
                generateSupplyDemandReport($conn, $year, $product);
                break;
            default:
                throw new Exception("Invalid page type.");
        }
    } catch (Exception $e) {
        header('Content-Type: text/html');
        echo "<html><body>";
        echo "<h2>PDF Generation Error</h2>";
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><a href='javascript:history.back()'>Go Back</a></p>";
        echo "</body></html>";
    }
} else {
    header("Location: index.php");
    exit();
}

function generateChartsReport($conn, $year, $product) {
    $pdf = new EnhancedPDF("Advanced Analytics Report - $year");
    $pdf->AddPage();
    
    // Build WHERE clause for filters
    $where_conditions = ["ph.year = ?"];
    $params = [$year];
    $param_types = "i";

    if ($product) {
        $where_conditions[] = "ph.product_id = ?";
        $params[] = $product;
        $param_types .= "i";
    }

    $where_clause = implode(" AND ", $where_conditions);
    
    // Get summary statistics
    $total_production_sql = "SELECT SUM(quantity_produced) as total FROM production_history ph WHERE $where_clause";
    $stmt = $conn->prepare($total_production_sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $total_production = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt->close();

    $avg_yield_sql = "SELECT AVG(quantity_produced/acreage) as avg_yield FROM production_history ph WHERE $where_clause AND acreage > 0";
    $stmt = $conn->prepare($avg_yield_sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $avg_yield = $stmt->get_result()->fetch_assoc()['avg_yield'] ?? 0;
    $stmt->close();

    // Production by product
    $pie_sql = "SELECT p.name, SUM(ph.quantity_produced) as total_production 
               FROM production_history ph 
               JOIN products p ON ph.product_id = p.product_id 
               WHERE $where_clause 
               GROUP BY ph.product_id, p.name 
               ORDER BY total_production DESC 
               LIMIT 10";
    $stmt = $conn->prepare($pie_sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $pie_result = $stmt->get_result();
    $production_data = $pie_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Add summary
    $summary = [
        'Report Year' => $year,
        'Total Production' => number_format($total_production, 0) . ' tons',
        'Average Yield' => number_format($avg_yield, 2) . ' tons/acre',
        'Number of Products' => count($production_data)
    ];
    
    $pdf->AddSummary($summary);
    
    // Production table
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(44, 62, 80);
    $pdf->Cell(0, 8, 'Production by Product', 0, 1, 'L');
    $pdf->Ln(3);
    
    $headers = ['Product Name', 'Total Production (tons)', 'Percentage'];
    $table_data = [];
    
    foreach ($production_data as $row) {
        $percentage = $total_production > 0 ? round(($row['total_production'] / $total_production) * 100, 1) : 0;
        $table_data[] = [
            $row['name'],
            number_format($row['total_production'], 0),
            $percentage . '%'
        ];
    }
    
    $pdf->EnhancedTable($headers, $table_data, [80, 60, 50]);
    
    $filename = "analytics_report_" . $year . "_" . date('Y-m-d') . ".pdf";
    $pdf->Output('D', $filename);
}

function generateProductsReport($conn) {
    $pdf = new EnhancedPDF("Product Information Report");
    $pdf->AddPage();
    
    $sql = "SELECT product_id, name, type, variety, sowing_time, harvest_time, seed_per_acre FROM products ORDER BY name";
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }
    $data = $result->fetch_all(MYSQLI_ASSOC);
    
    $headers = ["ID", "Name", "Type", "Variety", "Sowing", "Harvest", "Seed/Acre"];
    $col_widths = [15, 35, 25, 25, 25, 25, 30];
    
    $pdf->EnhancedTable($headers, $data, $col_widths);
    
    $filename = "products_report_" . date('Y-m-d') . ".pdf";
    $pdf->Output('D', $filename);
}

function generateProductionReport($conn, $year, $product) {
    $title = "Production History Report - $year";
    if ($product) {
        $product_name = $conn->query("SELECT name FROM products WHERE product_id = $product")->fetch_assoc()['name'];
        $title .= " ($product_name)";
    }
    
    $pdf = new EnhancedPDF($title);
    $pdf->AddPage();
    
    $where_conditions = ["ph.year = $year"];
    if ($product) {
        $where_conditions[] = "ph.product_id = $product";
    }
    $where_clause = implode(" AND ", $where_conditions);
    
    $sql = "SELECT ph.production_id, p.name, l.district_name, ph.year, ph.acreage, ph.quantity_produced 
            FROM production_history ph 
            JOIN products p ON ph.product_id = p.product_id 
            JOIN locations l ON ph.location_id = l.location_id 
            WHERE $where_clause
            ORDER BY ph.quantity_produced DESC LIMIT 100";
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }
    $data = $result->fetch_all(MYSQLI_ASSOC);
    
    $headers = ["ID", "Product", "Location", "Year", "Acreage", "Quantity"];
    $col_widths = [15, 40, 35, 20, 25, 30];
    
    $pdf->EnhancedTable($headers, $data, $col_widths);
    
    $filename = "production_report_" . $year . "_" . date('Y-m-d') . ".pdf";
    $pdf->Output('D', $filename);
}

function generatePricesReport($conn, $year, $product) {
    $title = "Price Trends Report - $year";
    if ($product) {
        $product_name = $conn->query("SELECT name FROM products WHERE product_id = $product")->fetch_assoc()['name'];
        $title .= " ($product_name)";
    }
    
    $pdf = new EnhancedPDF($title);
    $pdf->AddPage();
    
    $where_conditions = ["YEAR(ph.date) = $year"];
    if ($product) {
        $where_conditions[] = "ph.product_id = $product";
    }
    $where_clause = implode(" AND ", $where_conditions);
    
    $sql = "SELECT ph.price_id, p.name, l.district_name, ph.date, ph.wholesale_price, ph.retail_price 
            FROM price_history ph 
            JOIN products p ON ph.product_id = p.product_id 
            JOIN locations l ON ph.location_id = l.location_id 
            WHERE $where_clause
            ORDER BY ph.date DESC LIMIT 100";
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }
    $data = $result->fetch_all(MYSQLI_ASSOC);
    
    // Convert prices to BDT
    foreach ($data as &$row) {
        $row['wholesale_price'] = '৳' . number_format($row['wholesale_price'], 2);
        $row['retail_price'] = '৳' . number_format($row['retail_price'], 2);
    }
    
    $headers = ["ID", "Product", "Location", "Date", "Wholesale", "Retail"];
    $col_widths = [15, 35, 30, 25, 30, 30];
    
    $pdf->EnhancedTable($headers, $data, $col_widths);
    
    $filename = "prices_report_" . $year . "_" . date('Y-m-d') . ".pdf";
    $pdf->Output('D', $filename);
}

function generateWeatherReport($conn, $year) {
    $pdf = new EnhancedPDF("Weather Data Report - $year");
    $pdf->AddPage();
    
    $sql = "SELECT wh.weather_id, l.district_name, wh.date, wh.rainfall_mm, wh.temperature_celsius 
            FROM weather_history wh 
            JOIN locations l ON wh.location_id = l.location_id 
            WHERE YEAR(wh.date) = $year
            ORDER BY wh.date DESC LIMIT 100";
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }
    $data = $result->fetch_all(MYSQLI_ASSOC);
    
    $headers = ["ID", "Location", "Date", "Rainfall (mm)", "Temp (°C)"];
    $col_widths = [15, 40, 30, 35, 30];
    
    $pdf->EnhancedTable($headers, $data, $col_widths);
    
    $filename = "weather_report_" . $year . "_" . date('Y-m-d') . ".pdf";
    $pdf->Output('D', $filename);
}

function generateSurplusDeficitReport($conn, $year, $product) {
    $pdf = new EnhancedPDF("Surplus/Deficit Analysis - $year");
    $pdf->AddPage();
    
    $where_conditions = ["ph.year = $year"];
    if ($product) {
        $where_conditions[] = "ph.product_id = $product";
    }
    $where_clause = implode(" AND ", $where_conditions);
    
    $sql = "SELECT p.name, l.district_name, ph.quantity_produced, 
                   (ph.quantity_produced * 0.8) as estimated_demand,
                   (ph.quantity_produced - (ph.quantity_produced * 0.8)) as surplus_deficit
            FROM production_history ph 
            JOIN products p ON ph.product_id = p.product_id 
            JOIN locations l ON ph.location_id = l.location_id 
            WHERE $where_clause
            ORDER BY surplus_deficit DESC LIMIT 50";
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }
    $data = $result->fetch_all(MYSQLI_ASSOC);
    
    $headers = ["Product", "Location", "Production", "Demand", "Surplus/Deficit"];
    $col_widths = [35, 35, 30, 30, 35];
    
    $pdf->EnhancedTable($headers, $data, $col_widths);
    
    $filename = "surplus_deficit_" . $year . "_" . date('Y-m-d') . ".pdf";
    $pdf->Output('D', $filename);
}

function generateConsumptionReport($conn, $year, $product) {
    $pdf = new EnhancedPDF("Consumption Patterns - $year");
    $pdf->AddPage();
    
    $where_conditions = ["ph.year = $year"];
    if ($product) {
        $where_conditions[] = "ph.product_id = $product";
    }
    $where_clause = implode(" AND ", $where_conditions);
    
    $sql = "SELECT p.name, l.district_name, ph.quantity_produced,
                   (ph.quantity_produced * 0.7) as local_consumption,
                   (ph.quantity_produced * 0.3) as export_potential
            FROM production_history ph 
            JOIN products p ON ph.product_id = p.product_id 
            JOIN locations l ON ph.location_id = l.location_id 
            WHERE $where_clause
            ORDER BY ph.quantity_produced DESC LIMIT 50";
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }
    $data = $result->fetch_all(MYSQLI_ASSOC);
    
    $headers = ["Product", "Location", "Production", "Local Use", "Export"];
    $col_widths = [35, 35, 30, 30, 35];
    
    $pdf->EnhancedTable($headers, $data, $col_widths);
    
    $filename = "consumption_" . $year . "_" . date('Y-m-d') . ".pdf";
    $pdf->Output('D', $filename);
}

function generateSupplyDemandReport($conn, $year, $product) {
    $pdf = new EnhancedPDF("Supply vs Demand Analysis - $year");
    $pdf->AddPage();
    
    $where_conditions = ["ph.year = $year"];
    if ($product) {
        $where_conditions[] = "ph.product_id = $product";
    }
    $where_clause = implode(" AND ", $where_conditions);
    
    $sql = "SELECT p.name, l.district_name, 
                   SUM(ph.quantity_produced) as supply,
                   SUM(ph.quantity_produced * 0.85) as demand,
                   (SUM(ph.quantity_produced) - SUM(ph.quantity_produced * 0.85)) as balance
            FROM production_history ph 
            JOIN products p ON ph.product_id = p.product_id 
            JOIN locations l ON ph.location_id = l.location_id 
            WHERE $where_clause
            GROUP BY p.product_id, l.location_id
            ORDER BY balance DESC LIMIT 50";
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }
    $data = $result->fetch_all(MYSQLI_ASSOC);
    
    $headers = ["Product", "Location", "Supply", "Demand", "Balance"];
    $col_widths = [35, 35, 30, 30, 35];
    
    $pdf->EnhancedTable($headers, $data, $col_widths);
    
    $filename = "supply_demand_" . $year . "_" . date('Y-m-d') . ".pdf";
    $pdf->Output('D', $filename);
}
?>

