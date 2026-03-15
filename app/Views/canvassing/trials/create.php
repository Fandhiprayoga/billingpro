<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h4>Buat Trial Lisensi untuk <?= esc($customer->username) ?></h4>
      </div>
      <div class="card-body">
        <?php if (session()->has('errors')): ?>
        <div class="alert alert-danger">
          <ul class="mb-0">
            <?php foreach (session('errors') as $err): ?>
            <li><?= esc($err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <?php if (session()->has('error')): ?>
        <div class="alert alert-danger"><?= session('error') ?></div>
        <?php endif; ?>

        <form action="<?= base_url('canvassing/customer-trials/store/' . $customer->id) ?>" method="post">
          <?= csrf_field() ?>

          <div class="form-group">
            <label>Customer</label>
            <input type="text" class="form-control" value="<?= esc($customer->username) ?> (ID: <?= $customer->id ?>)" readonly>
          </div>

          <div class="form-group">
            <label for="duration_days">Durasi Trial (hari) <span class="text-danger">*</span></label>
            <input type="number" name="duration_days" id="duration_days" class="form-control"
                   value="<?= old('duration_days', 7) ?>" min="1" max="365" required>
            <small class="form-text text-muted">Masa aktif lisensi trial dalam hari (1 - 365).</small>
          </div>

          <div class="form-group">
            <label for="notes">Catatan (opsional)</label>
            <textarea name="notes" id="notes" class="form-control" rows="3"
                      placeholder="Catatan untuk lisensi trial ini..."><?= old('notes') ?></textarea>
          </div>

          <div class="form-group">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-plus"></i> Buat Trial Lisensi
            </button>
            <a href="<?= base_url('canvassing/customer-trials') ?>" class="btn btn-secondary ml-2">
              <i class="fas fa-arrow-left"></i> Batal
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card bg-light">
      <div class="card-body">
        <h6 class="font-weight-bold"><i class="fas fa-info-circle text-info"></i> Informasi</h6>
        <ul class="mb-0 pl-3" style="font-size: 13px;">
          <li>Lisensi trial dibuat tanpa perlu order/pembayaran.</li>
          <li>Setiap customer hanya bisa memiliki <strong>1 trial aktif</strong>.</li>
          <li>License key 20 karakter akan di-generate otomatis.</li>
          <li>Durasi trial dihitung mulai dari saat dibuat.</li>
        </ul>
      </div>
    </div>

    <div class="card bg-light">
      <div class="card-body">
        <h6 class="font-weight-bold"><i class="fas fa-clock text-warning"></i> Rekomendasi Durasi</h6>
        <table class="table table-sm table-borderless mb-0" style="font-size: 13px;">
          <tr><td>Demo singkat</td><td class="text-right"><strong>3 hari</strong></td></tr>
          <tr><td>Evaluasi standar</td><td class="text-right"><strong>7 hari</strong></td></tr>
          <tr><td>Evaluasi extended</td><td class="text-right"><strong>14 hari</strong></td></tr>
          <tr><td>Pilot project</td><td class="text-right"><strong>30 hari</strong></td></tr>
        </table>
      </div>
    </div>
  </div>
</div>
