<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Daftar Order</h4>
        <div class="card-header-action">
          <?php if (activeGroupCan('orders.create')): ?>
          <a href="<?= base_url('admin/orders/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat Order
          </a>
          <?php endif; ?>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th class="text-center">#</th>
                <th>No. Order</th>
                <th>User</th>
                <th>Paket</th>
                <th>Jumlah</th>
                <th>Metode</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($orders)): ?>
                <?php $no = 1; foreach ($orders as $order): ?>
                <tr>
                  <td class="text-center"><?= $no++ ?></td>
                  <td><code><?= esc($order->order_number) ?></code></td>
                  <td><?= esc($order->username ?? '-') ?></td>
                  <td><?= esc($order->plan_name ?? '-') ?></td>
                  <td>Rp <?= number_format($order->amount, 0, ',', '.') ?></td>
                  <td><span class="badge badge-light"><?= ucfirst($order->payment_method) ?></span></td>
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
                        'awaiting_confirmation'  => 'Menunggu Review',
                        'paid'                   => 'Lunas',
                        'cancelled'              => 'Dibatalkan',
                        'expired'                => 'Kadaluarsa',
                        default                  => $order->status,
                      };
                    ?>
                    <span class="badge <?= $statusBadge ?>"><?= $statusLabel ?></span>
                  </td>
                  <td><?= date('d/m/Y H:i', strtotime($order->created_at)) ?></td>
                  <td>
                    <?php if (activeGroupCan('orders.view')): ?>
                    <a href="<?= base_url('admin/orders/view/' . $order->order_number) ?>" class="btn btn-sm btn-info" title="Detail">
                      <i class="fas fa-eye"></i>
                    </a>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="9" class="text-center">Belum ada data order.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
