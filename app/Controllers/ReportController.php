<?php

namespace App\Controllers;

use App\Libraries\DataTableHandler;

class ReportController extends BaseController
{
    /**
     * GET /admin/reports/revenue
     * Halaman laporan pendapatan.
     */
    public function revenue()
    {
        $data = [
            'title'      => 'Laporan Pendapatan',
            'page_title' => 'Laporan Pendapatan',
        ];

        return $this->renderView('reports/revenue', $data);
    }

    /**
     * GET /admin/reports/revenue/ajax
     * AJAX DataTables endpoint.
     */
    public function revenueAjax()
    {
        $db = \Config\Database::connect();

        $builder = $db->table('orders')
            ->select('orders.id, orders.order_number, orders.type, orders.amount, orders.status, orders.paid_at, orders.created_at,
                      plans.name as plan_name, plans.duration_days,
                      users.username')
            ->join('plans', 'plans.id = orders.plan_id', 'left')
            ->join('users', 'users.id = orders.user_id', 'left')
            ->where('orders.status', 'paid');

        // Filter: type
        $type = $this->request->getGet('type');
        if (! empty($type)) {
            $builder->where('orders.type', $type);
        }

        // Filter: date range (paid_at)
        $dateFrom = $this->request->getGet('date_from');
        $dateTo   = $this->request->getGet('date_to');
        if (! empty($dateFrom)) {
            $builder->where('orders.paid_at >=', $dateFrom . ' 00:00:00');
        }
        if (! empty($dateTo)) {
            $builder->where('orders.paid_at <=', $dateTo . ' 23:59:59');
        }

        $countBuilder = clone $builder;

        $handler = new DataTableHandler($this->request);
        $result  = $handler->setBuilder($builder)
            ->setCountBuilder($countBuilder)
            ->setColumnMap([
                0 => 'orders.id',
                1 => 'orders.paid_at',
                2 => 'orders.order_number',
                3 => 'users.username',
                4 => 'plans.name',
                5 => 'orders.type',
                6 => 'orders.amount',
            ])
            ->process();

        return $this->response->setJSON($result);
    }

    /**
     * GET /admin/reports/revenue/summary
     * Summary data for cards (AJAX).
     */
    public function revenueSummary()
    {
        $db = \Config\Database::connect();

        $baseBuilder = function () use ($db) {
            return $db->table('orders')->where('status', 'paid');
        };

        // Apply filters
        $applyFilters = function ($builder) {
            $type     = $this->request->getGet('type');
            $dateFrom = $this->request->getGet('date_from');
            $dateTo   = $this->request->getGet('date_to');

            if (! empty($type)) {
                $builder->where('type', $type);
            }
            if (! empty($dateFrom)) {
                $builder->where('paid_at >=', $dateFrom . ' 00:00:00');
            }
            if (! empty($dateTo)) {
                $builder->where('paid_at <=', $dateTo . ' 23:59:59');
            }

            return $builder;
        };

        // Total pendapatan
        $total = $applyFilters($baseBuilder())
            ->selectSum('amount', 'total')
            ->selectCount('id', 'count')
            ->get()->getRow();

        // Pendapatan pembelian baru
        $newOrders = $applyFilters($baseBuilder())
            ->where('type', 'new')
            ->selectSum('amount', 'total')
            ->selectCount('id', 'count')
            ->get()->getRow();

        // Pendapatan renewal
        $renewalOrders = $applyFilters($baseBuilder())
            ->where('type', 'renewal')
            ->selectSum('amount', 'total')
            ->selectCount('id', 'count')
            ->get()->getRow();

        return $this->response->setJSON([
            'total'   => ['amount' => (float) ($total->total ?? 0), 'count' => (int) ($total->count ?? 0)],
            'new'     => ['amount' => (float) ($newOrders->total ?? 0), 'count' => (int) ($newOrders->count ?? 0)],
            'renewal' => ['amount' => (float) ($renewalOrders->total ?? 0), 'count' => (int) ($renewalOrders->count ?? 0)],
        ]);
    }

    /**
     * GET /admin/reports/revenue/export?format=csv|excel|pdf
     * Export laporan pendapatan.
     */
    public function revenueExport()
    {
        $format = $this->request->getGet('format') ?? 'csv';
        $rows   = $this->getRevenueData();

        return match ($format) {
            'excel' => $this->exportExcel($rows),
            'pdf'   => $this->exportPdf($rows),
            default => $this->exportCsv($rows),
        };
    }

    // ---------------------------------------------------------------
    // Private helpers
    // ---------------------------------------------------------------

    /**
     * Get filtered revenue data for export.
     */
    private function getRevenueData(): array
    {
        $db = \Config\Database::connect();

        $builder = $db->table('orders')
            ->select('orders.order_number, orders.type, orders.amount, orders.paid_at,
                      plans.name as plan_name, plans.duration_days,
                      users.username')
            ->join('plans', 'plans.id = orders.plan_id', 'left')
            ->join('users', 'users.id = orders.user_id', 'left')
            ->where('orders.status', 'paid');

        $type     = $this->request->getGet('type');
        $dateFrom = $this->request->getGet('date_from');
        $dateTo   = $this->request->getGet('date_to');

        if (! empty($type)) {
            $builder->where('orders.type', $type);
        }
        if (! empty($dateFrom)) {
            $builder->where('orders.paid_at >=', $dateFrom . ' 00:00:00');
        }
        if (! empty($dateTo)) {
            $builder->where('orders.paid_at <=', $dateTo . ' 23:59:59');
        }

        return $builder->orderBy('orders.paid_at', 'DESC')->get()->getResultArray();
    }

    /**
     * Export as CSV.
     */
    private function exportCsv(array $rows)
    {
        $filename = 'laporan_pendapatan_' . date('Ymd_His') . '.csv';

        $this->response->setHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM for Excel
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header
        fputcsv($output, ['No', 'Tanggal Bayar', 'No. Order', 'User', 'Paket', 'Durasi (hari)', 'Tipe', 'Jumlah (Rp)']);

        $no    = 1;
        $total = 0;
        foreach ($rows as $row) {
            $total += $row['amount'];
            fputcsv($output, [
                $no++,
                $row['paid_at'] ? date('d/m/Y H:i', strtotime($row['paid_at'])) : '-',
                $row['order_number'],
                $row['username'] ?? '-',
                $row['plan_name'] ?? '-',
                $row['duration_days'] ?? '-',
                $row['type'] === 'renewal' ? 'Perpanjangan' : 'Pembelian Baru',
                number_format($row['amount'], 0, ',', '.'),
            ]);
        }

        // Total row
        fputcsv($output, ['', '', '', '', '', '', 'TOTAL', number_format($total, 0, ',', '.')]);

        fclose($output);

        $this->response->send();
        exit;
    }

    /**
     * Export as Excel (XML Spreadsheet / .xls).
     */
    private function exportExcel(array $rows)
    {
        $filename = 'laporan_pendapatan_' . date('Ymd_His') . '.xls';

        $this->response->setHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');

        $siteName = setting('App.siteName') ?? 'BillingPro';
        $dateFrom = $this->request->getGet('date_from') ? date('d/m/Y', strtotime($this->request->getGet('date_from'))) : 'Semua';
        $dateTo   = $this->request->getGet('date_to') ? date('d/m/Y', strtotime($this->request->getGet('date_to'))) : 'Semua';
        $typeLabel = match ($this->request->getGet('type')) {
            'new'     => 'Pembelian Baru',
            'renewal' => 'Perpanjangan',
            default   => 'Semua',
        };

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
        <head><meta charset="UTF-8">
        <!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
        <x:Name>Laporan</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
        </x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->
        <style>
            th { background-color: #4472C4; color: white; font-weight: bold; padding: 8px; text-align: center; }
            td { padding: 6px 8px; border: 1px solid #ddd; }
            .header-row td { font-weight: bold; border: none; }
            .total-row { font-weight: bold; background-color: #E2EFDA; }
            .amount { text-align: right; }
        </style>
        </head><body><table>';

        // Header info
        $html .= '<tr class="header-row"><td colspan="8"><b>' . esc($siteName) . ' — Laporan Pendapatan</b></td></tr>';
        $html .= '<tr class="header-row"><td colspan="2">Periode:</td><td colspan="6">' . $dateFrom . ' s/d ' . $dateTo . '</td></tr>';
        $html .= '<tr class="header-row"><td colspan="2">Tipe:</td><td colspan="6">' . $typeLabel . '</td></tr>';
        $html .= '<tr class="header-row"><td colspan="2">Dicetak:</td><td colspan="6">' . date('d/m/Y H:i:s') . '</td></tr>';
        $html .= '<tr><td colspan="8"></td></tr>';

        // Table header
        $html .= '<tr><th>No</th><th>Tanggal Bayar</th><th>No. Order</th><th>User</th><th>Paket</th><th>Durasi (hari)</th><th>Tipe</th><th>Jumlah (Rp)</th></tr>';

        $no    = 1;
        $total = 0;
        foreach ($rows as $row) {
            $total += $row['amount'];
            $typeStr = ($row['type'] ?? 'new') === 'renewal' ? 'Perpanjangan' : 'Pembelian Baru';
            $html .= '<tr>';
            $html .= '<td style="text-align:center;">' . $no++ . '</td>';
            $html .= '<td>' . ($row['paid_at'] ? date('d/m/Y H:i', strtotime($row['paid_at'])) : '-') . '</td>';
            $html .= '<td>' . esc($row['order_number']) . '</td>';
            $html .= '<td>' . esc($row['username'] ?? '-') . '</td>';
            $html .= '<td>' . esc($row['plan_name'] ?? '-') . '</td>';
            $html .= '<td style="text-align:center;">' . ($row['duration_days'] ?? '-') . '</td>';
            $html .= '<td>' . $typeStr . '</td>';
            $html .= '<td class="amount">' . number_format($row['amount'], 0, ',', '.') . '</td>';
            $html .= '</tr>';
        }

        // Total
        $html .= '<tr class="total-row"><td colspan="7" style="text-align:right;"><b>TOTAL</b></td><td class="amount"><b>' . number_format($total, 0, ',', '.') . '</b></td></tr>';
        $html .= '</table></body></html>';

        $this->response->setBody($html);
        return $this->response;
    }

    /**
     * Export as PDF (HTML-based).
     */
    private function exportPdf(array $rows)
    {
        $filename = 'laporan_pendapatan_' . date('Ymd_His') . '.pdf';

        $siteName = setting('App.siteName') ?? 'BillingPro';
        $dateFrom = $this->request->getGet('date_from') ? date('d/m/Y', strtotime($this->request->getGet('date_from'))) : 'Semua';
        $dateTo   = $this->request->getGet('date_to') ? date('d/m/Y', strtotime($this->request->getGet('date_to'))) : 'Semua';
        $typeLabel = match ($this->request->getGet('type')) {
            'new'     => 'Pembelian Baru',
            'renewal' => 'Perpanjangan',
            default   => 'Semua',
        };

        $data = [
            'rows'      => $rows,
            'siteName'  => $siteName,
            'dateFrom'  => $dateFrom,
            'dateTo'    => $dateTo,
            'typeLabel' => $typeLabel,
            'filename'  => $filename,
        ];

        $html = view('reports/revenue_pdf', $data);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($this->htmlToPdf($html));
    }

    /**
     * Convert HTML to PDF using wkhtmltopdf or browser print fallback.
     * Falls back to HTML download if wkhtmltopdf not available.
     */
    private function htmlToPdf(string $html): string
    {
        // Try wkhtmltopdf first
        $wkhtmltopdf = $this->findWkhtmltopdf();

        if ($wkhtmltopdf) {
            $tmpHtml = tempnam(sys_get_temp_dir(), 'report_') . '.html';
            $tmpPdf  = tempnam(sys_get_temp_dir(), 'report_') . '.pdf';

            file_put_contents($tmpHtml, $html);

            $cmd = escapeshellarg($wkhtmltopdf)
                . ' --page-size A4 --orientation Landscape --margin-top 10mm --margin-bottom 10mm --margin-left 10mm --margin-right 10mm --encoding UTF-8'
                . ' ' . escapeshellarg($tmpHtml) . ' ' . escapeshellarg($tmpPdf) . ' 2>/dev/null';

            exec($cmd, $output, $returnCode);

            if ($returnCode === 0 && file_exists($tmpPdf)) {
                $pdfContent = file_get_contents($tmpPdf);
                @unlink($tmpHtml);
                @unlink($tmpPdf);
                return $pdfContent;
            }

            @unlink($tmpHtml);
            @unlink($tmpPdf);
        }

        // Fallback: return HTML with print CSS (browser will render it as printable)
        // Change content type to HTML so browser can use window.print()
        $this->response->setHeader('Content-Type', 'text/html; charset=utf-8');
        $this->response->setHeader('Content-Disposition', 'inline');

        // Add auto-print script
        $html = str_replace('</body>', '<script>window.onload=function(){window.print();}</script></body>', $html);

        return $html;
    }

    /**
     * Find wkhtmltopdf binary path.
     */
    private function findWkhtmltopdf(): ?string
    {
        $paths = [
            '/usr/local/bin/wkhtmltopdf',
            '/usr/bin/wkhtmltopdf',
            '/opt/homebrew/bin/wkhtmltopdf',
        ];

        foreach ($paths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        // Try which
        $result = trim(shell_exec('which wkhtmltopdf 2>/dev/null') ?? '');
        if (! empty($result) && file_exists($result)) {
            return $result;
        }

        return null;
    }
}
