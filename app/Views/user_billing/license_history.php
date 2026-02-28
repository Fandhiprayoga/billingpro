<?php
$isExpired = strtotime($license->expires_at) < time();
$licBadge = match($license->status) {
  'active'    => $isExpired ? 'badge-secondary' : 'badge-success',
  'expired'   => 'badge-secondary',
  'revoked'   => 'badge-danger',
  'suspended' => 'badge-warning',
  default     => 'badge-light',
};
$licLabel = match($license->status) {
  'active'    => $isExpired ? 'Expired' : 'Aktif',
  'expired'   => 'Expired',
  'revoked'   => 'Dicabut',
  'suspended' => 'Ditangguhkan',
  default     => $license->status,
};
?>

<div class="row">
  <!-- Sidebar -->
  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-key"></i> Informasi Lisensi</h4>
      </div>
      <div class="card-body">
        <div class="text-center mb-3">
          <small class="d-block text-muted mb-1">License Key</small>
          <code class="h5"><?= esc($license->license_key) ?></code>
        </div>
        <table class="table table-sm table-borderless mb-0">
          <tr>
            <td width="110"><strong>Paket</strong></td>
            <td><?= esc($license->plan_name ?? '-') ?></td>
          </tr>
          <tr>
            <td><strong>Status</strong></td>
            <td><span class="badge <?= $licBadge ?>"><?= $licLabel ?></span></td>
          </tr>
          <tr>
            <td><strong>Berlaku Sampai</strong></td>
            <td><?= date('d/m/Y H:i', strtotime($license->expires_at)) ?></td>
          </tr>
        </table>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <a href="<?= base_url('my-licenses/view/' . $license->uuid) ?>" class="btn btn-secondary btn-block">
          <i class="fas fa-arrow-left"></i> Kembali ke Detail
        </a>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-history"></i> Riwayat Pembayaran</h4>
      </div>
      <div class="card-body">
        <?php if (empty($orders)): ?>
          <div class="text-center text-muted py-5">
            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
            <p class="mb-0">Belum ada riwayat pembayaran untuk lisensi ini.</p>
          </div>
        <?php else: ?>

          <!-- Summary -->
          <div class="row mb-4">
            <?php
              $totalPaid = 0;
              $totalRenewal = 0;
              foreach ($orders as $order) {
                if ($order->status === 'paid') {
                  $totalPaid += $order->amount;
                  if ($order->type === 'renewal') $totalRenewal++;
                }
              }
            ?>
            <div class="col-md-4">
              <div class="card card-statistic-2 mb-0">
                <div class="card-body p-3 text-center">
                  <div class="text-muted small">Total Transaksi</div>
                  <div class="h5 font-weight-bold mb-0"><?= count($orders) ?></div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card card-statistic-2 mb-0">
                <div class="card-body p-3 text-center">
                  <div class="text-muted small">Perpanjangan</div>
                  <div class="h5 font-weight-bold mb-0"><?= $totalRenewal ?>x</div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card card-statistic-2 mb-0">
                <div class="card-body p-3 text-center">
                  <div class="text-muted small">Total Dibayar</div>
                  <div class="h5 font-weight-bold mb-0 text-success">Rp <?= number_format($totalPaid, 0, ',', '.') ?></div>
                </div>
              </div>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>No. Order</th>
                  <th>Tipe</th>
                  <th>Paket</th>
                  <th>Durasi</th>
                  <th class="text-right">Jumlah</th>
                  <th>Status</th>
                  <th>Tanggal</th>
                  <th width="50">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                  <td><code class="small"><?= esc($order->order_number) ?></code></td>
                  <td>
                    <?php if (($order->type ?? 'new') === 'renewal'): ?>
                      <span class="badge badge-info"><i class="fas fa-sync-alt mr-1"></i>Perpanjangan</span>
                    <?php else: ?>
                      <span class="badge badge-primary"><i class="fas fa-shopping-cart mr-1"></i>Pembelian</span>
                    <?php endif; ?>
                  </td>
                  <td><?= esc($order->plan_name ?? '-') ?></td>
                  <td><?= $order->duration_days ?? '-' ?> hari</td>
                  <td class="text-right font-weight-bold">Rp <?= number_format($order->amount, 0, ',', '.') ?></td>
                  <td>
                    <?php
                    $statusBadge = match($order->status) {
                      'pending'                => 'badge-warning',
                      'awaiting_confirmation'  => 'badge-info',
                      'paid'                   => 'badge-success',
                      'cancelled'              => 'badge-danger',
                      'expired'                => 'badge-secondary',
                      default                  => 'badge-light',
                    };
                    $statusLabel = match($order->status) {
                      'pending'                => 'Pending',
                      'awaiting_confirmation'  => 'Menunggu Konfirmasi',
                      'paid'                   => 'Dibayar',
                      'cancelled'              => 'Dibatalkan',
                      'expired'                => 'Expired',
                      default                  => $order->status,
                    };
                    ?>
                    <span class="badge <?= $statusBadge ?>"><?= $statusLabel ?></span>
                  </td>
                  <td><?= date('d/m/Y', strtotime($order->created_at)) ?></td>
                  <td>
                    <a href="<?= base_url('my-orders/view/' . $order->order_number) ?>" class="btn btn-sm btn-outline-info" title="Detail Order">
                      <i class="fas fa-eye"></i>
                    </a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
