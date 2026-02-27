<div class="row">
  <div class="col-12 col-md-8 offset-md-2">
    <div class="card">
      <div class="card-header">
        <h4>Buat Order Baru</h4>
      </div>
      <div class="card-body">
        <form action="<?= base_url('my-orders/store') ?>" method="post">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="plan_id">Pilih Paket <span class="text-danger">*</span></label>
            <div class="row">
              <?php if (!empty($plans)): ?>
                <?php
                  $selectedPlan = $_GET['plan'] ?? old('plan_id');
                ?>
                <?php foreach ($plans as $plan): ?>
                <div class="col-md-6 mb-3">
                  <div class="card border <?= $selectedPlan == $plan->id ? 'border-primary shadow' : '' ?>">
                    <div class="card-body">
                      <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="plan-<?= $plan->id ?>"
                               name="plan_id" value="<?= $plan->id ?>"
                               <?= $selectedPlan == $plan->id ? 'checked' : '' ?> required>
                        <label class="custom-control-label" for="plan-<?= $plan->id ?>">
                          <strong><?= esc($plan->name) ?></strong>
                        </label>
                      </div>
                      <div class="mt-2">
                        <h5 class="text-primary mb-1">Rp <?= number_format($plan->price, 0, ',', '.') ?></h5>
                        <small class="text-muted"><?= $plan->duration_days ?> hari</small>
                      </div>
                      <?php if (!empty($plan->description)): ?>
                        <p class="text-muted small mt-2 mb-0"><?= esc($plan->description) ?></p>
                      <?php endif; ?>
                      <?php
                        $features = json_decode($plan->features ?? '[]', true);
                        if (!empty($features)):
                      ?>
                        <ul class="small mt-2 mb-0 pl-3">
                          <?php foreach ($features as $feature): ?>
                            <li><?= esc($feature) ?></li>
                          <?php endforeach; ?>
                        </ul>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="col-12">
                  <div class="alert alert-warning">Belum ada paket tersedia.</div>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <div class="form-group">
            <label for="notes">Catatan (opsional)</label>
            <textarea class="form-control" id="notes" name="notes" rows="3"
                      placeholder="Catatan tambahan untuk order"><?= old('notes') ?></textarea>
          </div>

          <div class="form-group text-right">
            <a href="<?= base_url('my-orders') ?>" class="btn btn-secondary mr-1">Batal</a>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-shopping-cart"></i> Buat Order
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
