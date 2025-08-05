<?php
// Script to add PDF download buttons to all feature pages

$pages = [
    'product_info.php' => 'products',
    'production_history.php' => 'production',
    'price_trends.php' => 'prices',
    'weather_data.php' => 'weather',
    'surplus_deficit.php' => 'surplus_deficit',
    'consumption_patterns.php' => 'consumption',
    'supply_demand.php' => 'supply_demand'
];

foreach ($pages as $file => $page_type) {
    $file_path = "/home/ubuntu/agricultural_analysis/agricultural_analysis/$file";
    
    if (file_exists($file_path)) {
        $content = file_get_contents($file_path);
        
        // Check if PDF button already exists
        if (strpos($content, 'pdf-download-btn') === false) {
            // Add PDF download button before the closing body tag or footer
            $pdf_button = '
<!-- PDF Download Button -->
<button onclick="downloadPDF()" class="pdf-download-btn">
    <i class="fas fa-file-pdf"></i> Download PDF
</button>

<script>
// Download PDF function
function downloadPDF() {
    const year = document.getElementById(\'year\') ? document.getElementById(\'year\').value : new Date().getFullYear();
    const product = document.getElementById(\'product\') ? document.getElementById(\'product\').value : \'\';
    
    let url = \'pdf_export.php?page=' . $page_type . '&year=\' + year;
    if (product) url += \'&product=\' + product;
    
    window.open(url, \'_blank\');
}
</script>

<?php include \'templates/footer.php\'; ?>';
            
            // Replace the footer include with our enhanced version
            $content = str_replace('<?php include \'templates/footer.php\'; ?>', $pdf_button, $content);
            
            // Write back to file
            file_put_contents($file_path, $content);
            echo "Added PDF button to $file\n";
        } else {
            echo "PDF button already exists in $file\n";
        }
    } else {
        echo "File $file not found\n";
    }
}

echo "PDF buttons addition completed!\n";
?>

