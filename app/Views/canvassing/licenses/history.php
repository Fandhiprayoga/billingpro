<?php
$statusBadge = match($license->status) {
  'active'    => 'badge-success',
  'expired'   => 'badge-secondary',
  'revoked'   => 'badge-danger',
  'suspended' => 'badge-warning',
  default     => 'badge-light',
};

$orderBadges = [
  'pending'               => 'badge-warning',
  'awaiting_confirmation' => 'badge-info',
  'paid'                  => 'badge-success',
  'cancelled'             => 'badge-danger',
  'expired'               => 'badge-secondary',
];

$orderLabels = [
  'pending'               => 'Pending',
  'awaiting_confirmation' => 'Menunggu Konfirmasi',
  'paid'                  => 'Lunas',
  'cancelled'             => 'Dibatalkan',
  'expired'               => 'Expired',
];

$paymentBadges = [
  'pending'  => 'badge-warning',
  'approved' => 'badge-success',
  'rejected' => 'badge-danger',
];

// Index payments by order_id for easy lookup
$paymentsByOrder = [];
foreach ($payments as $p) {
  $paymentsByOrder[$p->order_id][] = $p;
}

$actionLabels = [
  'create_order'      => ['label' => 'Buat Order', 'badge' => 'badge-primary', 'icon' => 'fa-cart-plus'],
  'upload_payment'    => ['label' => 'Upload Bukti Bayar', 'badge' => 'badge-info', 'icon' => 'fa-upload'],
  'manage_license'    => ['label' => 'Kelola Lisensi', 'badge' => 'badge-warning', 'icon' => 'fa-key'],
  'approve_order'     => ['label' => 'Setujui Order', 'badge' => 'badge-success', 'icon' => 'fa-check-circle'],
  'reject_order'      => ['label' => 'Tolak Order', 'badge' => 'badge-danger', 'icon' => 'fa-times-circle'],
];
?>

<div class="row">
  <div class="col-md-4">
    <!-- License Info Card -->
    <div class="card">
      <div class="card-header"><h4>Info Lisensi</h4></div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
          <tr><td width="110"><strong>Customer</strong></td><td><?= esc($license->username) ?></td></tr>
          <tr><td><strong>License Key</strong></td><td><code><?= esc($license->license_key) ?></code></td></tr>
          <tr><td><strong>Paket</strong></td><td><?= esc($license->plan_name ?? '-') ?></td></tr>
          <tr>
            <td><strong>Status</strong></td>
            <td><span class="badge <?= $statusBadge ?>"><?= ucfirst($license->status) ?></span></td>
          </tr>
          <tr><td><strong>Expired</strong></td><td><?= date('d/m/Y H:i', strtotime($license->expires_at)) ?></td></tr>
        </table>
      </div>
      <div class="card-footer text-center">
        <a href="<?= base_url('canvassing/customer-licenses/' . $license->uuid) ?>" class="btn btn-info btn-sm">
          <i class="fas fa-eye"></i> Detail Lisensi
        </a>
        <a href="<?= base_url('canvassing/customer-licenses') ?>" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-left"></i> Kembali
        </a>
      </div>
    </div>
  </div>

  <div class="col-md-8">
    <!-- Timeline -->
    <div class="card">
      <div class="card-header"><h4><i class="fas fa-history"></i> History Transaksi</h4></div>
      <div class="card-body">
        <?php if (empty($orders)): ?>
          <div class="text-center text-muted py-4">
            <i class="fas fa-inbox fa-3x mb-3"></i>
            <p>Belum ada transaksi untuk lisensi ini.</p>
          </div>
        <?php else: ?>
          <?php foreach ($orders as $order): ?>
            <div class="card shadow-sm mb-3">
              <div class="card-body p-3">
                <!-- Order Header -->
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div>
                    <h6 class="mb-0">
                      <i class="fas fa-receipt text-primary"></i>
                      <code><?= esc($order->order_number) ?></code>
                      <?php if ($order->type === 'renewal'): ?>
                        <span class="badge badge-light ml-1">Perpanjangan</span>
                      <?php else: ?>
                        <span class="badge badge-light ml-1">Order Baru</span>
                      <?php endif; ?>
                    </h6>
                    <small class="text-muted">
                      <i class="fas fa-calendar-alt"></i> <?= date('d/m/Y H:i', strtotime($order->created_at)) ?>
                    </small>
                  </div>
                  <span class="badge <?= $orderBadges[$order->status] ?? 'badge-light' ?>">
                    <?= $orderLabels[$order->status] ?? ucfirst($order->status) ?>
                  </span>
                </div>

                <!-- Order Details -->
                <div class="row small mb-2">
                  <div class="col-sm-4">
                    <span class="text-muted">Paket:</span>
                    <strong><?= esc($order->plan_name ?? '-') ?></strong>
                  </div>
                  <div class="col-sm-4">
                    <span class="text-muted">Total:</span>
                    <strong>Rp <?= number_format($order->amount, 0, ',', '.') ?></strong>
                  </div>
                  <div class="col-sm-4">
                    <span class="text-muted">Metode:</span>
                    <strong><?= ucfirst($order->payment_method) ?></strong>
                  </div>
                </div>

                <?php if (! empty($order->paid_at)): ?>
                <div class="small text-success mb-2">
                  <i class="fas fa-check-circle"></i> Dibayar: <?= date('d/m/Y H:i', strtotime($order->paid_at)) ?>
                </div>
                <?php endif; ?>

                <?php if (! empty($order->rejected_at)): ?>
                <div class="small text-danger mb-2">
                  <i class="fas fa-times-circle"></i> Ditolak: <?= date('d/m/Y H:i', strtotime($order->rejected_at)) ?>
                  <?php if (! empty($order->admin_notes)): ?>
                    — <em><?= esc($order->admin_notes) ?></em>
                  <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (! empty($order->notes)): ?>
                <div class="small text-muted mb-2">
                  <i class="fas fa-sticky-note"></i> Catatan: <?= esc($order->notes) ?>
                </div>
                <?php endif; ?>

                <!-- Payment Confirmations -->
                <?php if (! empty($paymentsByOrder[$order->id])): ?>
                <hr class="my-2">
                <p class="small font-weight-bold mb-1"><i class="fas fa-money-check-alt"></i> Bukti Pembayaran</p>
                <?php foreach ($paymentsByOrder[$order->id] as $payment): ?>
                  <div class="bg-light rounded p-2 mb-1 small">
                    <div class="d-flex justify-content-between">
                      <div>
                        <strong><?= esc($payment->bank_name) ?></strong> — <?= esc($payment->account_name) ?>
                        (<?= esc($payment->account_number) ?>)
                      </div>
                      <span class="badge <?= $paymentBadges[$payment->status] ?? 'badge-light' ?>">
                        <?= ucfirst($payment->status) ?>
                      </span>
                    </div>
                    <div class="text-muted mt-1">
                      Rp <?= number_format($payment->transfer_amount, 0, ',', '.') ?>
                      &bull; Transfer: <?= date('d/m/Y', strtotime($payment->transfer_date)) ?>
                      &bull; Upload: <?= date('d/m/Y H:i', strtotime($payment->created_at)) ?>
                    </div>
                  </div>
                <?php endforeach; ?>
                <?php endif; ?>

                <!-- View Order Link -->
                <div class="mt-2 text-right">
                  <a href="<?= base_url('canvassing/customer-orders/view/' . $order->order_number) ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-external-link-alt"></i> Lihat Order
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Activity Logs -->
    <?php if (! empty($activities)): ?>
    <div class="card">
      <div class="card-header"><h4><i class="fas fa-list-ul"></i> Log Aktivitas Terkait</h4></div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-striped mb-0">
            <thead>
              <tr>
                <th>Waktu</th>
                <th>Aksi</th>
                <th>Keterangan</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($activities as $act): ?>
              <tr>
                <td class="small"><?= date('d/m/Y H:i', strtotime($act->created_at)) ?></td>
                <td>
                  <?php
                    $info = $actionLabels[$act->action_type] ?? ['label' => $act->action_type, 'badge' => 'badge-light', 'icon' => 'fa-circle'];
                  ?>
                  <span class="badge <?= $info['badge'] ?>"><i class="fas <?= $info['icon'] ?>"></i> <?= $info['label'] ?></span>
                </td>
                <td class="small"><?= esc($act->description ?? '-') ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
