<div class="row">
  <div class="col-12 col-md-8 offset-md-2">
    <div class="card">
      <div class="card-header">
        <h4>Upload Bukti Pembayaran</h4>
      </div>
      <div class="card-body">
        <div class="alert alert-info">
          <strong>Order:</strong> <?= esc($order->order_number) ?><br>
          <strong>Jumlah yang harus dibayar:</strong> <span class="h5">Rp <?= number_format($order->amount, 0, ',', '.') ?></span>
        </div>

        <?php if (!empty($bankInfo['bank_name'])): ?>
        <div class="alert alert-primary">
          <strong><i class="fas fa-university"></i> Transfer ke Rekening Berikut:</strong>
          <table class="table table-sm table-borderless mb-0 mt-2">
            <tr>
              <td width="110"><strong>Bank</strong></td>
              <td><span class="font-weight-bold"><?= esc($bankInfo['bank_name']) ?></span></td>
            </tr>
            <tr>
              <td><strong>No. Rekening</strong></td>
              <td><code class="h6 mb-0"><?= esc($bankInfo['account_number']) ?></code></td>
            </tr>
            <tr>
              <td><strong>Atas Nama</strong></td>
              <td><strong><?= esc($bankInfo['account_name']) ?></strong></td>
            </tr>
          </table>
        </div>
        <?php endif; ?>

        <div class="alert alert-light">
          <strong><i class="fas fa-info-circle"></i> Instruksi Pembayaran:</strong>
          <ol class="mb-0 mt-2">
            <li>Transfer sesuai jumlah di atas ke rekening yang tertera</li>
            <li>Isi form di bawah dengan data transfer Anda</li>
            <li>Upload foto/screenshot bukti transfer</li>
            <li>Tunggu verifikasi dari admin (maks. 1x24 jam)</li>
          </ol>
        </div>

        <form action="<?= base_url('my-orders/submit-confirmation/' . $order->order_number) ?>" method="post" enctype="multipart/form-data">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="bank_name">Nama Bank Pengirim <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="bank_name" name="bank_name"
                   value="<?= old('bank_name') ?>" placeholder="Contoh: BCA, BNI, Mandiri" required>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="account_name">Nama Pemilik Rekening <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="account_name" name="account_name"
                       value="<?= old('account_name') ?>" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="account_number">Nomor Rekening <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="account_number" name="account_number"
                       value="<?= old('account_number') ?>" required>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="transfer_amount">Jumlah Transfer (Rp) <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="transfer_amount" name="transfer_amount"
                       value="<?= old('transfer_amount', $order->amount) ?>" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="transfer_date">Tanggal Transfer <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="transfer_date" name="transfer_date"
                       value="<?= old('transfer_date', date('Y-m-d')) ?>" required>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="proof_image">Bukti Transfer <span class="text-danger">*</span></label>
            <input type="file" class="form-control-file" id="proof_image" name="proof_image"
                   accept="image/jpeg,image/png,image/jpg" required>
            <small class="form-text text-muted">Format: JPG, JPEG, PNG. Maks: 2MB.</small>
          </div>

          <div class="form-group text-right">
            <a href="<?= base_url('my-orders/view/' . $order->order_number) ?>" class="btn btn-secondary mr-1">Batal</a>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-upload"></i> Kirim Konfirmasi
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
