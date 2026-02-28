<?php
/** @var array $settings */
/** @var array $groups */
/** @var string $activeTab */

// Shortcut untuk ambil value setting
$s = function (string $key) use ($settings) {
    return esc($settings[$key] ?? '');
};
?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Pengaturan Sistem</h4>
      </div>
      <div class="card-body">

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs" id="settingTabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'general' ? 'active' : '' ?>"
               id="general-tab" data-toggle="tab" href="#general" role="tab">
              <i class="fas fa-cog"></i> Umum
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'auth' ? 'active' : '' ?>"
               id="auth-tab" data-toggle="tab" href="#auth" role="tab">
              <i class="fas fa-shield-alt"></i> Autentikasi
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'mail' ? 'active' : '' ?>"
               id="mail-tab" data-toggle="tab" href="#mail" role="tab">
              <i class="fas fa-envelope"></i> Email
            </a>
          </li>
        </ul>

        <!-- Tabs Content -->
        <div class="tab-content" id="settingTabContent">

          <!-- ============================================ -->
          <!-- TAB: UMUM -->
          <!-- ============================================ -->
          <div class="tab-pane fade <?= $activeTab === 'general' ? 'show active' : '' ?>" id="general" role="tabpanel">
            <form action="<?= base_url('admin/settings/update/general') ?>" method="post" enctype="multipart/form-data" class="mt-4">
              <?= csrf_field() ?>

              <div class="form-group row">
                <label for="site_name" class="col-sm-3 col-form-label">Nama Aplikasi <span class="text-danger">*</span></label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="site_name" name="site_name"
                         value="<?= old('site_name', $s('App.siteName')) ?>" required>
                  <small class="form-text text-muted">Ditampilkan di title bar dan header sidebar.</small>
                </div>
              </div>

              <div class="form-group row">
                <label for="site_name_short" class="col-sm-3 col-form-label">Nama Pendek</label>
                <div class="col-sm-4">
                  <input type="text" class="form-control" id="site_name_short" name="site_name_short"
                         value="<?= old('site_name_short', $s('App.siteNameShort')) ?>" maxlength="10">
                  <small class="form-text text-muted">Ditampilkan di sidebar saat diminimalkan (maks 10 karakter).</small>
                </div>
              </div>

              <div class="form-group row">
                <label for="site_description" class="col-sm-3 col-form-label">Deskripsi</label>
                <div class="col-sm-9">
                  <textarea class="form-control" id="site_description" name="site_description"
                            rows="2"><?= old('site_description', $s('App.siteDescription')) ?></textarea>
                  <small class="form-text text-muted">Deskripsi singkat tentang aplikasi.</small>
                </div>
              </div>

              <div class="form-group row">
                <label for="site_footer" class="col-sm-3 col-form-label">Teks Footer</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="site_footer" name="site_footer"
                         value="<?= old('site_footer', $s('App.siteFooter')) ?>">
                  <small class="form-text text-muted">Ditampilkan di bagian bawah halaman.</small>
                </div>
              </div>

              <div class="form-group row">
                <label for="site_version" class="col-sm-3 col-form-label">Versi</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="site_version" name="site_version"
                         value="<?= old('site_version', $s('App.siteVersion')) ?>">
                </div>
              </div>

              <hr>
              <h6 class="text-muted mb-3"><i class="fas fa-image"></i> Branding</h6>

              <div class="form-group row">
                <label for="favicon" class="col-sm-3 col-form-label">Favicon</label>
                <div class="col-sm-9">
                  <div class="d-flex align-items-center mb-2">
                    <?php
                      $faviconPath = $settings['App.favicon'] ?? '';
                      $faviconUrl  = $faviconPath ? base_url('uploads/' . $faviconPath) : base_url('assets/img/stisla-fill.svg');
                    ?>
                    <img src="<?= $faviconUrl ?>" alt="Favicon saat ini" id="favicon-preview"
                         style="width: 32px; height: 32px; object-fit: contain; border: 1px solid #ddd; border-radius: 4px; padding: 2px; background: #fff;" class="mr-3">
                    <?php if ($faviconPath): ?>
                      <span class="badge badge-success mr-2"><i class="fas fa-check"></i> Sudah diatur</span>
                      <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 btn-delete-branding" data-type="favicon" title="Hapus & Kembali ke Default">
                        <i class="fas fa-trash-alt"></i> Hapus
                      </button>
                    <?php else: ?>
                      <span class="badge badge-secondary">Default</span>
                    <?php endif; ?>
                  </div>
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" id="favicon" name="favicon"
                           accept=".ico,.png,.svg">
                    <label class="custom-file-label" for="favicon">Pilih file favicon...</label>
                  </div>
                  <small class="form-text text-muted">Format: ICO, PNG, atau SVG. Maksimal 512 KB. Rekomendasi ukuran: 32x32 atau 64x64 px.</small>
                </div>
              </div>

              <div class="form-group row">
                <label for="login_logo" class="col-sm-3 col-form-label">Logo Halaman Login</label>
                <div class="col-sm-9">
                  <div class="d-flex align-items-center mb-2">
                    <?php
                      $logoPath = $settings['App.loginLogo'] ?? '';
                      $logoUrl  = $logoPath ? base_url('uploads/' . $logoPath) : base_url('assets/img/stisla-fill.svg');
                    ?>
                    <img src="<?= $logoUrl ?>" alt="Logo login saat ini" id="logo-preview"
                         style="width: 80px; height: 80px; object-fit: contain; border: 1px solid #ddd; border-radius: 8px; padding: 4px; background: #fff;" class="mr-3">
                    <?php if ($logoPath): ?>
                      <span class="badge badge-success mr-2"><i class="fas fa-check"></i> Sudah diatur</span>
                      <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 btn-delete-branding" data-type="login_logo" title="Hapus & Kembali ke Default">
                        <i class="fas fa-trash-alt"></i> Hapus
                      </button>
                    <?php else: ?>
                      <span class="badge badge-secondary">Default (Stisla)</span>
                    <?php endif; ?>
                  </div>
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" id="login_logo" name="login_logo"
                           accept=".png,.jpg,.jpeg,.svg,.webp">
                    <label class="custom-file-label" for="login_logo">Pilih file logo...</label>
                  </div>
                  <small class="form-text text-muted">Format: PNG, JPG, SVG, atau WebP. Maksimal 2 MB. Rekomendasi ukuran: 100x100 px.</small>
                </div>
              </div>

              <hr>
              <h6 class="text-muted mb-3"><i class="fas fa-university"></i> Rekening Tujuan Transfer</h6>
              <p class="text-muted small mb-3">Informasi rekening ini akan ditampilkan kepada user saat melakukan pembayaran order.</p>

              <div class="form-group row">
                <label for="bank_name" class="col-sm-3 col-form-label">Nama Bank</label>
                <div class="col-sm-6">
                  <input type="text" class="form-control" id="bank_name" name="bank_name"
                         value="<?= old('bank_name', $s('App.bankName')) ?>"
                         placeholder="Contoh: BCA, BNI, Mandiri, BRI">
                  <small class="form-text text-muted">Nama bank tujuan transfer pembayaran.</small>
                </div>
              </div>

              <div class="form-group row">
                <label for="bank_account_number" class="col-sm-3 col-form-label">No. Rekening</label>
                <div class="col-sm-6">
                  <input type="text" class="form-control" id="bank_account_number" name="bank_account_number"
                         value="<?= old('bank_account_number', $s('App.bankAccountNumber')) ?>"
                         placeholder="Contoh: 1234567890">
                  <small class="form-text text-muted">Nomor rekening tujuan transfer.</small>
                </div>
              </div>

              <div class="form-group row">
                <label for="bank_account_name" class="col-sm-3 col-form-label">Atas Nama</label>
                <div class="col-sm-6">
                  <input type="text" class="form-control" id="bank_account_name" name="bank_account_name"
                         value="<?= old('bank_account_name', $s('App.bankAccountName')) ?>"
                         placeholder="Contoh: PT. Billing Pro Indonesia">
                  <small class="form-text text-muted">Nama pemilik rekening.</small>
                </div>
              </div>

              <div class="form-group row">
                <div class="col-sm-9 offset-sm-3">
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Pengaturan Umum
                  </button>
                </div>
              </div>
            </form>
          </div>

          <!-- ============================================ -->
          <!-- TAB: AUTENTIKASI -->
          <!-- ============================================ -->
          <div class="tab-pane fade <?= $activeTab === 'auth' ? 'show active' : '' ?>" id="auth" role="tabpanel">
            <form action="<?= base_url('admin/settings/update/auth') ?>" method="post" class="mt-4">
              <?= csrf_field() ?>

              <div class="form-group row">
                <label for="default_role" class="col-sm-3 col-form-label">Default Role <span class="text-danger">*</span></label>
                <div class="col-sm-9">
                  <select class="form-control" id="default_role" name="default_role">
                    <?php foreach ($groups as $key => $group): ?>
                      <option value="<?= $key ?>" <?= ($settings['App.defaultRole'] ?? 'user') === $key ? 'selected' : '' ?>>
                        <?= esc($group['title']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <small class="form-text text-muted">Role yang otomatis diberikan ke user baru saat registrasi.</small>
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Registrasi</label>
                <div class="col-sm-9">
                  <label class="custom-switch mt-2">
                    <input type="checkbox" name="allow_registration" value="1" class="custom-switch-input"
                           <?= ($settings['App.allowRegistration'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <span class="custom-switch-indicator"></span>
                    <span class="custom-switch-description">Izinkan registrasi user baru</span>
                  </label>
                </div>
              </div>

              <hr>
              <h6 class="text-muted mb-3"><i class="fas fa-tools"></i> Mode Pemeliharaan</h6>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Maintenance Mode</label>
                <div class="col-sm-9">
                  <label class="custom-switch mt-2">
                    <input type="checkbox" name="maintenance_mode" value="1" class="custom-switch-input"
                           <?= ($settings['App.maintenanceMode'] ?? '0') === '1' ? 'checked' : '' ?>>
                    <span class="custom-switch-indicator"></span>
                    <span class="custom-switch-description">Aktifkan mode pemeliharaan (hanya Super Admin yang bisa akses)</span>
                  </label>
                </div>
              </div>

              <div class="form-group row">
                <label for="maintenance_msg" class="col-sm-3 col-form-label">Pesan Maintenance</label>
                <div class="col-sm-9">
                  <textarea class="form-control" id="maintenance_msg" name="maintenance_msg"
                            rows="2"><?= old('maintenance_msg', $s('App.maintenanceMsg')) ?></textarea>
                </div>
              </div>

              <div class="form-group row">
                <div class="col-sm-9 offset-sm-3">
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Pengaturan Autentikasi
                  </button>
                </div>
              </div>
            </form>
          </div>

          <!-- ============================================ -->
          <!-- TAB: EMAIL -->
          <!-- ============================================ -->
          <div class="tab-pane fade <?= $activeTab === 'mail' ? 'show active' : '' ?>" id="mail" role="tabpanel">
            <form action="<?= base_url('admin/settings/update/mail') ?>" method="post" class="mt-4">
              <?= csrf_field() ?>

              <div class="form-group row">
                <label for="mail_protocol" class="col-sm-3 col-form-label">Protokol <span class="text-danger">*</span></label>
                <div class="col-sm-9">
                  <select class="form-control" id="mail_protocol" name="mail_protocol">
                    <?php
                      $proto = $settings['Mail.protocol'] ?? 'smtp';
                    ?>
                    <option value="smtp" <?= $proto === 'smtp' ? 'selected' : '' ?>>SMTP</option>
                    <option value="sendmail" <?= $proto === 'sendmail' ? 'selected' : '' ?>>Sendmail</option>
                    <option value="mail" <?= $proto === 'mail' ? 'selected' : '' ?>>PHP Mail</option>
                  </select>
                </div>
              </div>

              <div id="smtp-settings">
                <div class="form-group row">
                  <label for="mail_hostname" class="col-sm-3 col-form-label">SMTP Host</label>
                  <div class="col-sm-9">
                    <input type="text" class="form-control" id="mail_hostname" name="mail_hostname"
                           value="<?= old('mail_hostname', $s('Mail.hostname')) ?>"
                           placeholder="smtp.gmail.com">
                  </div>
                </div>

                <div class="form-group row">
                  <label for="mail_port" class="col-sm-3 col-form-label">Port</label>
                  <div class="col-sm-5">
                    <input type="number" class="form-control" id="mail_port" name="mail_port"
                           value="<?= old('mail_port', $s('Mail.port')) ?>"
                           placeholder="587">
                  </div>
                </div>

                <div class="form-group row">
                  <label for="mail_encryption" class="col-sm-3 col-form-label">Enkripsi</label>
                  <div class="col-sm-5">
                    <?php $enc = $settings['Mail.encryption'] ?? 'tls'; ?>
                    <select class="form-control" id="mail_encryption" name="mail_encryption">
                      <option value="tls" <?= $enc === 'tls' ? 'selected' : '' ?>>TLS</option>
                      <option value="ssl" <?= $enc === 'ssl' ? 'selected' : '' ?>>SSL</option>
                      <option value="none" <?= $enc === 'none' ? 'selected' : '' ?>>Tanpa Enkripsi</option>
                    </select>
                  </div>
                </div>

                <div class="form-group row">
                  <label for="mail_username" class="col-sm-3 col-form-label">Username</label>
                  <div class="col-sm-9">
                    <input type="text" class="form-control" id="mail_username" name="mail_username"
                           value="<?= old('mail_username', $s('Mail.username')) ?>"
                           placeholder="email@gmail.com" autocomplete="off">
                  </div>
                </div>

                <div class="form-group row">
                  <label for="mail_password" class="col-sm-3 col-form-label">Password</label>
                  <div class="col-sm-9">
                    <input type="password" class="form-control" id="mail_password" name="mail_password"
                           placeholder="Kosongkan jika tidak ingin mengubah" autocomplete="new-password">
                    <?php if (! empty($settings['Mail.password'])): ?>
                      <small class="form-text text-success"><i class="fas fa-check"></i> Password sudah diatur</small>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <hr>
              <h6 class="text-muted mb-3"><i class="fas fa-paper-plane"></i> Identitas Pengirim</h6>

              <div class="form-group row">
                <label for="mail_from_email" class="col-sm-3 col-form-label">Email Pengirim</label>
                <div class="col-sm-9">
                  <input type="email" class="form-control" id="mail_from_email" name="mail_from_email"
                         value="<?= old('mail_from_email', $s('Mail.fromEmail')) ?>"
                         placeholder="noreply@example.com">
                </div>
              </div>

              <div class="form-group row">
                <label for="mail_from_name" class="col-sm-3 col-form-label">Nama Pengirim</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="mail_from_name" name="mail_from_name"
                         value="<?= old('mail_from_name', $s('Mail.fromName')) ?>"
                         placeholder="My App">
                </div>
              </div>

              <div class="form-group row">
                <div class="col-sm-9 offset-sm-3">
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Pengaturan Email
                  </button>
                </div>
              </div>
            </form>
          </div>

        </div><!-- end tab-content -->
      </div>
    </div>
  </div>
</div>

<script>
$(function() {
  // Custom file input label update + image preview
  $('.custom-file-input').on('change', function() {
    var fileName = $(this).val().split('\\').pop();
    $(this).siblings('.custom-file-label').addClass('selected').html(fileName);

    // Preview image
    var previewId = $(this).attr('id') === 'favicon' ? '#favicon-preview' : '#logo-preview';
    if (this.files && this.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {
        $(previewId).attr('src', e.target.result);
      };
      reader.readAsDataURL(this.files[0]);
    }
  });

  // Delete branding (favicon / logo) — submit via dynamic form to avoid nested form issue
  $(document).on('click', '.btn-delete-branding', function() {
    var type = $(this).data('type');
    var label = type === 'favicon' ? 'favicon' : 'logo halaman login';
    if (!confirm('Yakin ingin menghapus ' + label + ' dan kembali ke default?')) return;

    var form = $('<form>', {
      action: '<?= base_url('admin/settings/delete-branding/') ?>' + type,
      method: 'post'
    });
    form.append($('<input>', { type: 'hidden', name: '<?= csrf_token() ?>', value: '<?= csrf_hash() ?>' }));
    $('body').append(form);
    form.submit();
  });
});
</script>
