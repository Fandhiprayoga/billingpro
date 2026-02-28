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
  'awaiting_confirmation'  => 'Menunggu Review',
  'paid'                   => 'Lunas',
  'cancelled'              => !empty($order->admin_notes) ? 'Ditolak' : 'Dibatalkan',
  'expired'                => 'Kadaluarsa',
  default                  => $order->status,
};
?>
<div class="row">
  <div class="col-md-8">
    <!-- Order Info -->
    <div class="card">
      <div class="card-header">
        <h4>Informasi Order</h4>
      </div>
      <div class="card-body">
        <table class="table table-sm table-borderless">
          <tr>
            <td width="180"><strong>No. Order</strong></td>
            <td><code><?= esc($order->order_number) ?></code></td>
          </tr>
          <tr>
            <td><strong>User</strong></td>
            <td><?= esc($order->username) ?></td>
          </tr>
          <tr>
            <td><strong>Paket</strong></td>
            <td><?= esc($order->plan_name) ?> (<?= $order->duration_days ?> hari)</td>
          </tr>
          <tr>
            <td><strong>Jumlah</strong></td>
            <td><strong class="text-primary">Rp <?= number_format($order->amount, 0, ',', '.') ?></strong></td>
          </tr>
          <tr>
            <td><strong>Metode Bayar</strong></td>
            <td><?= ucfirst($order->payment_method) ?></td>
          </tr>
          <tr>
            <td><strong>Status</strong></td>
            <td><span class="badge <?= $statusBadge ?>"><?= $statusLabel ?></span></td>
          </tr>
          <tr>
            <td><strong>Tanggal Order</strong></td>
            <td><?= date('d/m/Y H:i', strtotime($order->created_at)) ?></td>
          </tr>
          <?php if ($order->paid_at): ?>
          <tr>
            <td><strong>Tanggal Bayar</strong></td>
            <td><?= date('d/m/Y H:i', strtotime($order->paid_at)) ?></td>
          </tr>
          <?php endif; ?>
          <?php if (!empty($order->rejected_at)): ?>
          <tr>
            <td><strong>Tanggal Ditolak</strong></td>
            <td><span class="text-danger"><?= date('d/m/Y H:i', strtotime($order->rejected_at)) ?></span></td>
          </tr>
          <?php endif; ?>
          <?php if (!empty($order->notes)): ?>
          <tr>
            <td><strong>Catatan User</strong></td>
            <td><?= esc($order->notes) ?></td>
          </tr>
          <?php endif; ?>
          <?php if (!empty($order->admin_notes)): ?>
          <tr>
            <td><strong>Catatan Admin</strong></td>
            <td>
              <?php if ($order->status === 'cancelled'): ?>
                <span class="text-danger"><i class="fas fa-times-circle"></i> <?= esc($order->admin_notes) ?></span>
              <?php else: ?>
                <?= esc($order->admin_notes) ?>
              <?php endif; ?>
            </td>
          </tr>
          <?php endif; ?>
        </table>
      </div>
    </div>

    <!-- Payment Confirmations -->
    <?php if (!empty($confirmations)): ?>
    <div class="card">
      <div class="card-header">
        <h4>Konfirmasi Pembayaran</h4>
      </div>
      <div class="card-body">
        <?php foreach ($confirmations as $conf): ?>
        <div class="card border mb-3">
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0">
                  <tr><td width="130"><strong>Bank</strong></td><td><?= esc($conf->bank_name) ?></td></tr>
                  <tr><td><strong>Nama Rek.</strong></td><td><?= esc($conf->account_name) ?></td></tr>
                  <tr><td><strong>No. Rek.</strong></td><td><?= esc($conf->account_number) ?></td></tr>
                  <tr><td><strong>Jumlah</strong></td><td>Rp <?= number_format($conf->transfer_amount, 0, ',', '.') ?></td></tr>
                  <tr><td><strong>Tgl. Transfer</strong></td><td><?= date('d/m/Y', strtotime($conf->transfer_date)) ?></td></tr>
                  <tr>
                    <td><strong>Status</strong></td>
                    <td>
                      <?php
                        $confBadge = match($conf->status) {
                          'pending'  => 'badge-warning',
                          'approved' => 'badge-success',
                          'rejected' => 'badge-danger',
                          default    => 'badge-light',
                        };
                      ?>
                      <span class="badge <?= $confBadge ?>"><?= ucfirst($conf->status) ?></span>
                    </td>
                  </tr>
                  <?php if (!empty($conf->admin_notes)): ?>
                  <tr><td><strong>Catatan Admin</strong></td><td><?= esc($conf->admin_notes) ?></td></tr>
                  <?php endif; ?>
                </table>
              </div>
              <div class="col-md-6 text-center">
                <?php if (!empty($conf->proof_image)): ?>
                  <a href="<?= base_url('uploads/' . $conf->proof_image) ?>" target="_blank">
                    <img src="<?= base_url('uploads/' . $conf->proof_image) ?>"
                         class="img-fluid rounded" style="max-height: 250px;" alt="Bukti Transfer">
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- License Info -->
    <?php if (!empty($license)): ?>
    <div class="card">
      <div class="card-header">
        <h4>Lisensi</h4>
      </div>
      <div class="card-body">
        <table class="table table-sm table-borderless">
          <tr>
            <td width="150"><strong>License Key</strong></td>
            <td><code class="h5"><?= esc($license->license_key) ?></code></td>
          </tr>
          <tr>
            <td><strong>Device ID</strong></td>
            <td><?= esc($license->device_id ?? 'Belum diaktivasi') ?></td>
          </tr>
          <tr>
            <td><strong>Status</strong></td>
            <td>
              <?php
                $licBadge = match($license->status) {
                  'active'    => 'badge-success',
                  'expired'   => 'badge-secondary',
                  'revoked'   => 'badge-danger',
                  'suspended' => 'badge-warning',
                  default     => 'badge-light',
                };
              ?>
              <span class="badge <?= $licBadge ?>"><?= ucfirst($license->status) ?></span>
            </td>
          </tr>
          <tr>
            <td><strong>Berlaku Sampai</strong></td>
            <td><?= date('d/m/Y H:i', strtotime($license->expires_at)) ?></td>
          </tr>
          <?php if ($license->activated_at): ?>
          <tr>
            <td><strong>Diaktivasi</strong></td>
            <td><?= date('d/m/Y H:i', strtotime($license->activated_at)) ?></td>
          </tr>
          <?php endif; ?>
        </table>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Action Sidebar -->
  <div class="col-md-4">
    <!-- Upload Bukti Bayar -->
    <?php if (in_array($order->status, ['pending']) && activeGroupCan('orders.create')): ?>
    <div class="card">
      <div class="card-body text-center">
        <a href="<?= base_url('admin/orders/upload-confirmation/' . $order->order_number) ?>" class="btn btn-primary btn-block">
          <i class="fas fa-upload"></i> Upload Bukti Bayar
        </a>
      </div>
    </div>
    <?php endif; ?>

    <!-- Admin Actions -->
    <?php if (in_array($order->status, ['pending', 'awaiting_confirmation'])): ?>
      <?php if (activeGroupCan('orders.approve')): ?>
      <div class="card">
        <div class="card-header">
          <h4>Setujui Order</h4>
        </div>
        <div class="card-body">
          <form action="<?= base_url('admin/orders/approve/' . $order->order_number) ?>" method="post"
                onsubmit="return confirm('Yakin ingin menyetujui order ini? Lisensi akan otomatis di-generate.')">
            <?= csrf_field() ?>
            <div class="form-group">
              <label for="admin_notes">Catatan Admin</label>
              <textarea class="form-control" id="admin_notes" name="admin_notes" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-success btn-block">
              <i class="fas fa-check"></i> Setujui & Generate Lisensi
            </button>
          </form>
        </div>
      </div>
      <?php endif; ?>
    <?php endif; ?>

    <?php if (in_array($order->status, ['pending', 'awaiting_confirmation', 'paid'])): ?>
      <?php if (activeGroupCan('orders.reject')): ?>
      <div class="card">
        <div class="card-header">
          <h4>Tolak Order</h4>
        </div>
        <div class="card-body">
          <?php if ($order->status === 'paid'): ?>
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <small>Order ini sudah <strong>Lunas</strong>. Menolak order ini akan <strong>mencabut lisensi</strong> yang sudah di-generate.</small>
          </div>
          <?php endif; ?>
          <form action="<?= base_url('admin/orders/reject/' . $order->order_number) ?>" method="post"
                onsubmit="return confirm('<?= $order->status === 'paid' ? 'PERHATIAN: Order sudah lunas! Menolak akan mencabut lisensi. Yakin ingin melanjutkan?' : 'Yakin ingin menolak order ini?' ?>')">
            <?= csrf_field() ?>
            <div class="form-group">
              <label for="reason">Alasan Penolakan <span class="text-danger">*</span></label>
              <textarea class="form-control" id="reason" name="reason" rows="2" required></textarea>
            </div>
            <button type="submit" class="btn btn-danger btn-block">
              <i class="fas fa-times"></i> <?= $order->status === 'paid' ? 'Tolak & Cabut Lisensi' : 'Tolak Order' ?>
            </button>
          </form>
        </div>
      </div>
      <?php endif; ?>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <a href="<?= base_url('admin/orders') ?>" class="btn btn-secondary btn-block">
          <i class="fas fa-arrow-left"></i> Kembali
        </a>
      </div>
    </div>
  </div>
</div>
