<?php
// Check if user is logged in
function isLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_id']);
}

// Redirect to login if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Generate PDF using FPDF
function generatePDF($title, $data, $headers) {
    require_once('fpdf.php');
    
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    
    // Title
    $pdf->Cell(0, 10, $title, 0, 1, 'C');
    $pdf->Ln(10);
    
    // Date and time
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'R');
    $pdf->Ln(5);
    
    // Table headers
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(102, 126, 234);
    $pdf->SetTextColor(255, 255, 255);
    
    $col_width = 190 / count($headers);
    foreach ($headers as $header) {
        $pdf->Cell($col_width, 8, $header, 1, 0, 'C', true);
    }
    $pdf->Ln();
    
    // Table data
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFillColor(245, 245, 245);
    
    $fill = false;
    foreach ($data as $row) {
        foreach ($row as $cell) {
            $pdf->Cell($col_width, 6, substr($cell, 0, 20), 1, 0, 'C', $fill);
        }
        $pdf->Ln();
        $fill = !$fill;
    }
    
    return $pdf;
}

// Calculate surplus/deficit
function calculateSurplusDeficit($production, $consumption) {
    return $production - $consumption;
}

// Format currency
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

// Format date
function formatDate($date) {
    return date('Y-m-d', strtotime($date));
}
?>

