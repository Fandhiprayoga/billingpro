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
  'pending'                => 'Menunggu Pembayaran',
  'awaiting_confirmation'  => 'Menunggu Verifikasi',
  'paid'                   => 'Lunas',
  'cancelled'              => !empty($order->admin_notes) ? 'Ditolak Admin' : 'Dibatalkan',
  'expired'                => 'Kadaluarsa',
  default                  => $order->status,
};
?>
<div class="row">
  <div class="col-md-8">
    <!-- Order Info -->
    <div class="card">
      <div class="card-header"><h4>Informasi Order</h4></div>
      <div class="card-body">
        <table class="table table-sm table-borderless">
          <tr><td width="180"><strong>No. Order</strong></td><td><code><?= esc($order->order_number) ?></code></td></tr>
          <tr><td><strong>Customer</strong></td><td><?= esc($order->username) ?></td></tr>
          <tr><td><strong>Paket</strong></td><td><?= esc($order->plan_name) ?> (<?= $order->duration_days ?> hari)</td></tr>
          <tr><td><strong>Jumlah</strong></td><td><strong class="text-primary">Rp <?= number_format($order->amount, 0, ',', '.') ?></strong></td></tr>
          <tr><td><strong>Metode Bayar</strong></td><td><?= ucfirst($order->payment_method) ?></td></tr>
          <tr><td><strong>Status</strong></td><td><span class="badge <?= $statusBadge ?>"><?= $statusLabel ?></span></td></tr>
          <tr><td><strong>Tanggal Order</strong></td><td><?= date('d/m/Y H:i', strtotime($order->created_at)) ?></td></tr>
          <?php if ($order->paid_at): ?>
          <tr><td><strong>Tanggal Bayar</strong></td><td><?= date('d/m/Y H:i', strtotime($order->paid_at)) ?></td></tr>
          <?php endif; ?>
          <?php if (!empty($order->rejected_at)): ?>
          <tr><td><strong>Tanggal Ditolak</strong></td><td><span class="text-danger"><?= date('d/m/Y H:i', strtotime($order->rejected_at)) ?></span></td></tr>
          <?php endif; ?>
          <?php if (!empty($order->notes)): ?>
          <tr><td><strong>Catatan</strong></td><td><?= esc($order->notes) ?></td></tr>
          <?php endif; ?>
          <?php if (!empty($order->admin_notes) && $order->status === 'cancelled'): ?>
          <tr><td><strong>Alasan Penolakan</strong></td><td><span class="text-danger"><i class="fas fa-times-circle"></i> <?= esc($order->admin_notes) ?></span></td></tr>
          <?php endif; ?>
        </table>
      </div>
    </div>

    <!-- Progress -->
    <div class="card">
      <div class="card-header"><h4>Progress Order</h4></div>
      <div class="card-body">
        <?php
          $steps = [
            ['label' => 'Order Dibuat',        'done' => true],
            ['label' => 'Bukti Bayar Dikirim', 'done' => in_array($order->status, ['awaiting_confirmation', 'paid'])],
            ['label' => 'Diverifikasi Admin',  'done' => $order->status === 'paid'],
            ['label' => 'Lisensi Aktif',       'done' => $order->status === 'paid' && !empty($license)],
          ];
          if ($order->status === 'cancelled') {
            $steps = [
              ['label' => 'Order Dibuat', 'done' => true],
              ['label' => 'Order Dibatalkan', 'done' => true, 'danger' => true],
            ];
          }
        ?>
        <div class="row">
          <?php foreach ($steps as $i => $step): ?>
          <div class="col text-center">
            <div class="mb-2">
              <?php if (isset($step['danger']) && $step['danger']): ?>
                <span class="badge badge-danger" style="width:32px;height:32px;line-height:32px;border-radius:50%;font-size:14px;"><i class="fas fa-times"></i></span>
              <?php elseif ($step['done']): ?>
                <span class="badge badge-success" style="width:32px;height:32px;line-height:32px;border-radius:50%;font-size:14px;"><i class="fas fa-check"></i></span>
              <?php else: ?>
                <span class="badge badge-secondary" style="width:32px;height:32px;line-height:32px;border-radius:50%;font-size:14px;"><?= $i + 1 ?></span>
              <?php endif; ?>
            </div>
            <small class="<?= $step['done'] ? 'font-weight-bold' : 'text-muted' ?>"><?= $step['label'] ?></small>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Payment Confirmations -->
    <?php if (!empty($confirmations)): ?>
    <div class="card">
      <div class="card-header"><h4>Konfirmasi Pembayaran</h4></div>
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
                        $confBadge = match($conf->status) { 'pending' => 'badge-warning', 'approved' => 'badge-success', 'rejected' => 'badge-danger', default => 'badge-light' };
                        $confLabel = match($conf->status) { 'pending' => 'Menunggu Review', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', default => $conf->status };
                      ?>
                      <span class="badge <?= $confBadge ?>"><?= $confLabel ?></span>
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
                    <img src="<?= base_url('uploads/' . $conf->proof_image) ?>" class="img-fluid rounded" style="max-height: 250px;" alt="Bukti Transfer">
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
      <div class="card-header"><h4><i class="fas fa-key text-warning"></i> Lisensi</h4></div>
      <div class="card-body">
        <div class="alert alert-success">
          <strong>License Key:</strong> <code class="h5 ml-2"><?= esc($license->license_key) ?></code>
        </div>
        <table class="table table-sm table-borderless">
          <tr><td width="150"><strong>Status</strong></td><td><span class="badge badge-success"><?= ucfirst($license->status) ?></span></td></tr>
          <tr><td><strong>Expired</strong></td><td><?= date('d/m/Y H:i', strtotime($license->expires_at)) ?></td></tr>
        </table>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Sidebar -->
  <div class="col-md-4">
    <div class="card">
      <div class="card-header"><h4>Aksi</h4></div>
      <div class="card-body">
        <?php if ($order->status === 'pending'): ?>
        <a href="<?= base_url('canvassing/customer-orders/upload-proof/' . $order->order_number) ?>" class="btn btn-success btn-block mb-2">
          <i class="fas fa-upload"></i> Upload Bukti Bayar
        </a>
        <?php endif; ?>
        <a href="<?= base_url('canvassing/customer-orders') ?>" class="btn btn-secondary btn-block">
          <i class="fas fa-arrow-left"></i> Kembali ke Daftar Order
        </a>
      </div>
    </div>

    <!-- Approve Order -->
    <?php if (in_array($order->status, ['pending', 'awaiting_confirmation']) && activeGroupCan('canvassing.orders.approve')): ?>
    <div class="card">
      <div class="card-header"><h4><i class="fas fa-check text-success"></i> Setujui Order</h4></div>
      <div class="card-body">
        <form action="<?= base_url('canvassing/customer-orders/approve/' . $order->order_number) ?>" method="post"
              onsubmit="return confirm('Yakin ingin menyetujui order ini? Lisensi akan otomatis di-generate.')">
          <?= csrf_field() ?>
          <div class="form-group">
            <label for="admin_notes">Catatan (opsional)</label>
            <textarea class="form-control" id="admin_notes" name="admin_notes" rows="2"></textarea>
          </div>
          <button type="submit" class="btn btn-success btn-block">
            <i class="fas fa-check"></i> Setujui & Generate Lisensi
          </button>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <!-- Reject Order -->
    <?php if (in_array($order->status, ['pending', 'awaiting_confirmation']) && activeGroupCan('canvassing.orders.reject')): ?>
    <div class="card">
      <div class="card-header"><h4><i class="fas fa-times text-danger"></i> Tolak Order</h4></div>
      <div class="card-body">
        <form action="<?= base_url('canvassing/customer-orders/reject/' . $order->order_number) ?>" method="post"
              onsubmit="return confirm('Yakin ingin menolak order ini?')">
          <?= csrf_field() ?>
          <div class="form-group">
            <label for="reason">Alasan Penolakan <span class="text-danger">*</span></label>
            <textarea class="form-control" id="reason" name="reason" rows="2" required></textarea>
          </div>
          <button type="submit" class="btn btn-danger btn-block">
            <i class="fas fa-times"></i> Tolak Order
          </button>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <!-- Bank Info -->
    <?php if ($order->status === 'pending' && !empty($bankInfo['bank_name'])): ?>
    <div class="card">
      <div class="card-header"><h4><i class="fas fa-university"></i> Info Rekening</h4></div>
      <div class="card-body">
        <table class="table table-sm table-borderless">
          <tr><td><strong>Bank</strong></td><td><?= esc($bankInfo['bank_name']) ?></td></tr>
          <tr><td><strong>No. Rek</strong></td><td><code><?= esc($bankInfo['account_number']) ?></code></td></tr>
          <tr><td><strong>Atas Nama</strong></td><td><?= esc($bankInfo['account_name']) ?></td></tr>
        </table>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
