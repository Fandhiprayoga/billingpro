<div class="row">
  <div class="col-12 col-md-8 offset-md-2">
    <div class="card">
      <div class="card-header"><h4>Perpanjang Lisensi Customer</h4></div>
      <div class="card-body">
        <div class="alert alert-info">
          <strong>License Key:</strong> <code><?= esc($license->license_key) ?></code><br>
          <strong>Paket saat ini:</strong> <?= esc($license->plan_name) ?><br>
          <strong>Expired:</strong> <?= date('d/m/Y H:i', strtotime($license->expires_at)) ?>
        </div>

        <form action="<?= base_url('canvassing/customer-licenses/store-renewal/' . $license->uuid) ?>" method="post">
          <?= csrf_field() ?>

          <div class="form-group">
            <label>Pilih Paket Perpanjangan <span class="text-danger">*</span></label>
            <div class="row">
              <?php foreach ($plans as $plan): ?>
              <div class="col-md-6 mb-3">
                <div class="card border <?= old('plan_id') == $plan->id ? 'border-primary' : '' ?>">
                  <div class="card-body">
                    <div class="custom-control custom-radio">
                      <input type="radio" id="plan_<?= $plan->id ?>" name="plan_id" 
                             value="<?= $plan->id ?>" class="custom-control-input"
                             <?= old('plan_id', $license->plan_id) == $plan->id ? 'checked' : '' ?>>
                      <label class="custom-control-label" for="plan_<?= $plan->id ?>">
                        <strong><?= esc($plan->name) ?></strong>
                      </label>
                    </div>
                    <p class="mb-1 mt-2">
                      <span class="h5 text-primary">Rp <?= number_format($plan->price, 0, ',', '.') ?></span>
                      <small class="text-muted">/ <?= $plan->duration_days ?> hari</small>
                    </p>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="form-group">
            <label for="notes">Catatan (opsional)</label>
            <textarea class="form-control" id="notes" name="notes" rows="2"><?= old('notes') ?></textarea>
          </div>

          <div class="form-group text-right">
            <a href="<?= base_url('canvassing/customer-licenses/' . $license->uuid) ?>" class="btn btn-secondary mr-1">Batal</a>
            <button type="submit" class="btn btn-warning">
              <i class="fas fa-redo"></i> Buat Order Perpanjangan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
