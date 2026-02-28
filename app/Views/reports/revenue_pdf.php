<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Pendapatan - <?= $siteName ?? 'POS Billing' ?></title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #333; padding: 20px; }
    .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
    .header h1 { font-size: 18px; margin-bottom: 4px; }
    .header p { font-size: 11px; color: #555; }
    .meta { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 11px; }
    .meta .left { text-align: left; }
    .meta .right { text-align: right; }
    .summary { display: flex; gap: 15px; margin-bottom: 20px; }
    .summary-box { flex: 1; border: 1px solid #ddd; border-radius: 4px; padding: 10px; text-align: center; }
    .summary-box .label { font-size: 10px; text-transform: uppercase; color: #888; margin-bottom: 4px; }
    .summary-box .value { font-size: 16px; font-weight: bold; }
    .summary-box .count { font-size: 10px; color: #666; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    thead th { background: #2c3e50; color: #fff; padding: 6px 8px; text-align: left; font-size: 11px; }
    tbody td { padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px; }
    tbody tr:nth-child(even) { background: #f9f9f9; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    tfoot td { background: #ecf0f1; padding: 6px 8px; font-weight: bold; font-size: 12px; }
    .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 10px; color: #fff; }
    .badge-primary { background: #3498db; }
    .badge-info { background: #00b5ad; }
    .footer { text-align: center; margin-top: 20px; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }

    @media print {
      body { padding: 0; }
      .no-print { display: none !important; }
      @page { margin: 15mm 10mm; }
    }
  </style>
</head>
<body>

  <div class="header">
    <h1><?= esc($siteName ?? 'POS Billing') ?></h1>
    <p>Laporan Pendapatan</p>
  </div>

  <div class="meta">
    <div class="left">
      <strong>Periode:</strong> <?= esc($period ?? 'Semua') ?><br>
      <strong>Tipe:</strong> <?= esc($typeLabel ?? 'Semua') ?>
    </div>
    <div class="right">
      <strong>Dicetak:</strong> <?= date('d/m/Y H:i') ?><br>
      <strong>Total Data:</strong> <?= number_format(count($rows ?? [])) ?> transaksi
    </div>
  </div>

  <div class="summary">
    <div class="summary-box">
      <div class="label">Total Pendapatan</div>
      <div class="value">Rp <?= number_format($totalAmount ?? 0, 0, ',', '.') ?></div>
      <div class="count"><?= number_format($totalCount ?? 0) ?> transaksi</div>
    </div>
    <div class="summary-box">
      <div class="label">Pembelian Baru</div>
      <div class="value">Rp <?= number_format($newAmount ?? 0, 0, ',', '.') ?></div>
      <div class="count"><?= number_format($newCount ?? 0) ?> transaksi</div>
    </div>
    <div class="summary-box">
      <div class="label">Perpanjangan</div>
      <div class="value">Rp <?= number_format($renewalAmount ?? 0, 0, ',', '.') ?></div>
      <div class="count"><?= number_format($renewalCount ?? 0) ?> transaksi</div>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th class="text-center" width="40">#</th>
        <th>Tanggal Bayar</th>
        <th>No. Order</th>
        <th>User</th>
        <th>Paket</th>
        <th>Tipe</th>
        <th class="text-right">Jumlah (Rp)</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="7" class="text-center" style="padding:20px; color:#999;">Tidak ada data.</td></tr>
      <?php else: ?>
        <?php $no = 1; foreach ($rows as $row): ?>
        <tr>
          <td class="text-center"><?= $no++ ?></td>
          <td><?= date('d/m/Y H:i', strtotime($row['paid_at'])) ?></td>
          <td><?= esc($row['order_number']) ?></td>
          <td><?= esc($row['username'] ?? '-') ?></td>
          <td><?= esc($row['plan_name'] ?? '-') ?></td>
          <td>
            <?php if ($row['type'] === 'renewal'): ?>
              <span class="badge badge-info">Perpanjangan</span>
            <?php else: ?>
              <span class="badge badge-primary">Pembelian</span>
            <?php endif; ?>
          </td>
          <td class="text-right"><?= number_format($row['amount'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="6" class="text-right">Total:</td>
        <td class="text-right">Rp <?= number_format($totalAmount ?? 0, 0, ',', '.') ?></td>
      </tr>
    </tfoot>
  </table>

  <div class="footer">
    Dicetak oleh <?= esc($printedBy ?? 'System') ?> pada <?= date('d/m/Y H:i:s') ?>
  </div>

  <!-- Fallback: if opened in browser (not wkhtmltopdf), offer print -->
  <div class="no-print" style="text-align:center; margin-top:30px;">
    <button onclick="window.print()" style="padding:10px 30px; font-size:14px; cursor:pointer; background:#3498db; color:#fff; border:none; border-radius:4px;">
      <strong>🖨 Cetak / Simpan PDF</strong>
    </button>
  </div>

</body>
</html>
