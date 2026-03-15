<div class="row">
  <div class="col-12 col-md-8 offset-md-2">
    <div class="card">
      <div class="card-header">
        <h4>Buat Order untuk <?= esc($customer->username) ?></h4>
      </div>
      <div class="card-body">
        <div class="alert alert-info">
          <i class="fas fa-info-circle"></i> Anda akan membuat order atas nama customer <strong><?= esc($customer->username) ?></strong>.
        </div>

        <form action="<?= base_url('canvassing/customer-orders/store/' . $customer->id) ?>" method="post">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="plan_id">Pilih Paket <span class="text-danger">*</span></label>
            <div class="row">
              <?php foreach ($plans as $plan): ?>
              <div class="col-md-6 mb-3">
                <div class="card border <?= old('plan_id') == $plan->id ? 'border-primary' : '' ?>">
                  <div class="card-body">
                    <div class="custom-control custom-radio">
                      <input type="radio" id="plan_<?= $plan->id ?>" name="plan_id" 
                             value="<?= $plan->id ?>" class="custom-control-input"
                             <?= old('plan_id') == $plan->id ? 'checked' : '' ?>>
                      <label class="custom-control-label" for="plan_<?= $plan->id ?>">
                        <strong><?= esc($plan->name) ?></strong>
                      </label>
                    </div>
                    <p class="mb-1 mt-2">
                      <span class="h5 text-primary">Rp <?= number_format($plan->price, 0, ',', '.') ?></span>
                      <small class="text-muted">/ <?= $plan->duration_days ?> hari</small>
                    </p>
                    <?php if (!empty($plan->description)): ?>
                    <small class="text-muted"><?= esc($plan->description) ?></small>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="form-group">
            <label for="notes">Catatan (opsional)</label>
            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Catatan tambahan..."><?= old('notes') ?></textarea>
          </div>

          <div class="form-group text-right">
            <a href="<?= base_url('canvassing/my-customers/' . $customer->id) ?>" class="btn btn-secondary mr-1">Batal</a>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-cart-plus"></i> Buat Order
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
