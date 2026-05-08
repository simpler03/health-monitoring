<?php
/**
 * API: Generate Building Block Report
 * Generates a PDF report based on filtered building block data
 */

header('Content-Type: application/pdf');

require_once __DIR__ . '/../config/BaseConfig.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Database.php';

Auth::requireLogin();

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $year = $input['year'] ?? 'All Years';
    $facility = $input['facility'] ?? 'All Facilities';
    $provinces = $input['provinces'] ?? [];
    
    // Check if TCPDF is available, if not use simple HTML response
    require_once '../PhpSpreadsheet/IOFactory.php';
    
    // Create a simple HTML table for PDF generation
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Provincial Building Block Performance Report</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; background: white; }
            h1 { color: #333; text-align: center; }
            .report-meta { background: #f5f5f5; padding: 15px; margin: 20px 0; border-radius: 5px; }
            .report-meta p { margin: 5px 0; }
            .province-section { margin-bottom: 40px; page-break-inside: avoid; }
            .province-title { background: #667eea; color: white; padding: 10px; border-radius: 3px; margin-bottom: 15px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th { background: #f5f5f5; padding: 10px; text-align: left; border: 1px solid #ddd; font-weight: bold; }
            td { padding: 10px; border: 1px solid #ddd; }
            tr:nth-child(even) { background: #f9f9f9; }
            .compliance-rate { font-weight: bold; color: #2e7d32; }
            .footer { text-align: center; margin-top: 40px; color: #666; font-size: 12px; border-top: 1px solid #ddd; padding-top: 20px; }
        </style>
    </head>
    <body>
        <h1>Provincial Building Block Performance Report</h1>
        
        <div class="report-meta">
            <p><strong>Report Generated:</strong> ' . date('F d, Y H:i:s') . '</p>
            <p><strong>Filter - Year:</strong> ' . htmlspecialchars($year) . '</p>
            <p><strong>Filter - Facility:</strong> ' . htmlspecialchars($facility) . '</p>
        </div>';
    
    if (empty($provinces)) {
        $html .= '<p style="color: #999; text-align: center; padding: 40px;">No data available for the selected filters.</p>';
    } else {
        foreach ($provinces as $province) {
            $html .= '
            <div class="province-section">
                <div class="province-title">Province: ' . htmlspecialchars($province['name']) . '</div>
                <table>
                    <thead>
                        <tr>
                            <th>Building Block</th>
                            <th>Compliance Rate</th>
                        </tr>
                    </thead>
                    <tbody>';
            
            if (!empty($province['building_blocks'])) {
                foreach ($province['building_blocks'] as $bb) {
                    $html .= '
                        <tr>
                            <td>' . htmlspecialchars($bb['name']) . '</td>
                            <td class="compliance-rate">' . htmlspecialchars($bb['compliance']) . '</td>
                        </tr>';
                }
            } else {
                $html .= '<tr><td colspan="2" style="text-align: center; color: #999;">No building blocks data</td></tr>';
            }
            
            $html .= '
                    </tbody>
                </table>
            </div>';
        }
    }
    
    $html .= '
        <div class="footer">
            <p>This is an auto-generated report from the Provincial Building Block Performance Dashboard</p>
        </div>
    </body>
    </html>';
    
    // Try to use mPDF for better PDF generation if available
    $useMpdf = false;
    if (file_exists('../vendor/mpdf/mpdf/src/Mpdf.php')) {
        try {
            require_once '../vendor/mpdf/mpdf/src/Mpdf.php';
            $mpdf = new \Mpdf\Mpdf();
            $mpdf->WriteHTML($html);
            $mpdf->Output('building-block-report.pdf', 'D');
            $useMpdf = true;
        } catch (Exception $e) {
            // Fall back to HTML output
        }
    }
    
    // If mPDF not available, output HTML for browser to convert to PDF
    if (!$useMpdf) {
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }
    
} catch (Exception $e) {
    error_log("Generate Report API Error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Error generating report: ' . $e->getMessage()
    ]);
}
?>
