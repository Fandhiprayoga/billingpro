<div class="row">
  <?php if (!empty($plans)): ?>
    <?php foreach ($plans as $plan): ?>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card card-primary">
        <div class="card-header">
          <h4><?= esc($plan->name) ?></h4>
        </div>
        <div class="card-body text-center">
          <h3 class="text-primary mb-1">Rp <?= number_format($plan->price, 0, ',', '.') ?></h3>
          <p class="text-muted"><?= $plan->duration_days ?> hari</p>

          <?php if (!empty($plan->description)): ?>
            <p class="text-muted small"><?= esc($plan->description) ?></p>
          <?php endif; ?>

          <?php
            $features = json_decode($plan->features ?? '[]', true);
            if (!empty($features)):
          ?>
          <ul class="text-left small mt-3 mb-4">
            <?php foreach ($features as $feature): ?>
              <li class="mb-1"><i class="fas fa-check text-success mr-1"></i> <?= esc($feature) ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>

          <a href="<?= base_url('my-orders/create?plan=' . $plan->id) ?>" class="btn btn-primary btn-block">
            <i class="fas fa-shopping-cart"></i> Pilih Paket
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="col-12">
      <div class="card">
        <div class="card-body text-center py-5">
          <p class="text-muted">Belum ada paket tersedia saat ini.</p>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>
