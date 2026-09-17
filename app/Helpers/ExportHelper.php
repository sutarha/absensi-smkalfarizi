<?php
namespace App\Helpers;

class ExportHelper
{
    /**
     * Export data array to an Excel-compatible HTML table (downloaded as .xls)
     * 
     * @param string $filename Name of the downloaded file without extension
     * @param array $headers Array of column headers
     * @param array $data 2D Array of rows
     */
    public static function toExcel(string $filename, array $headers, array $data): void
    {
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename={$filename}.xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        echo '<head><meta charset="utf-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Sheet1</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
        echo '<body>';
        echo '<table border="1" cellpadding="3" cellspacing="0">';
        
        // Headers
        echo '<tr>';
        foreach ($headers as $header) {
            echo '<th style="background-color: #f2f2f2;">' . htmlspecialchars($header) . '</th>';
        }
        echo '</tr>';

        // Data
        foreach ($data as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                // Ensure text formatting for numeric strings to prevent Excel scientific notation
                $style = is_numeric($cell) && strlen((string)$cell) > 10 ? ' style="mso-number-format:\'\@\';"' : '';
                echo '<td' . $style . '>' . htmlspecialchars((string)$cell) . '</td>';
            }
            echo '</tr>';
        }

        echo '</table>';
        echo '</body></html>';
        exit;
    }
}
