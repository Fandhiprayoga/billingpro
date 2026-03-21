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
          <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'storage' ? 'active' : '' ?>"
               id="storage-tab" data-toggle="tab" href="#storage" role="tab">
              <i class="fas fa-hdd"></i> Warehousing
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
                      <option value="<?= $key ?>" <?= ($settings['AuthGroups.defaultGroup'] ?? 'user') === $key ? 'selected' : '' ?>>
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
                           <?= !empty($settings['Auth.allowRegistration']) ? 'checked' : '' ?>>
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
                      $proto = $settings['Email.protocol'] ?? 'smtp';
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
                           value="<?= old('mail_hostname', $s('Email.SMTPHost')) ?>"
                           placeholder="smtp.gmail.com">
                  </div>
                </div>

                <div class="form-group row">
                  <label for="mail_port" class="col-sm-3 col-form-label">Port</label>
                  <div class="col-sm-5">
                    <input type="number" class="form-control" id="mail_port" name="mail_port"
                           value="<?= old('mail_port', $s('Email.SMTPPort')) ?>"
                           placeholder="587">
                  </div>
                </div>

                <div class="form-group row">
                  <label for="mail_encryption" class="col-sm-3 col-form-label">Enkripsi</label>
                  <div class="col-sm-5">
                    <?php $enc = $settings['Email.SMTPCrypto'] ?? 'tls'; ?>
                    <select class="form-control" id="mail_encryption" name="mail_encryption">
                      <option value="tls" <?= $enc === 'tls' ? 'selected' : '' ?>>TLS</option>
                      <option value="ssl" <?= $enc === 'ssl' ? 'selected' : '' ?>>SSL</option>
                      <option value="none" <?= ($enc === 'none' || $enc === '') ? 'selected' : '' ?>>Tanpa Enkripsi</option>
                    </select>
                  </div>
                </div>

                <div class="form-group row">
                  <label for="mail_username" class="col-sm-3 col-form-label">Username</label>
                  <div class="col-sm-9">
                    <input type="text" class="form-control" id="mail_username" name="mail_username"
                           value="<?= old('mail_username', $s('Email.SMTPUser')) ?>"
                           placeholder="email@gmail.com" autocomplete="off">
                  </div>
                </div>

                <div class="form-group row">
                  <label for="mail_password" class="col-sm-3 col-form-label">Password</label>
                  <div class="col-sm-9">
                    <input type="password" class="form-control" id="mail_password" name="mail_password"
                           placeholder="Kosongkan jika tidak ingin mengubah" autocomplete="new-password">
                    <?php if (! empty($settings['Email.SMTPPass'])): ?>
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
                         value="<?= old('mail_from_email', $s('Email.fromEmail')) ?>"
                         placeholder="noreply@example.com">
                </div>
              </div>

              <div class="form-group row">
                <label for="mail_from_name" class="col-sm-3 col-form-label">Nama Pengirim</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="mail_from_name" name="mail_from_name"
                         value="<?= old('mail_from_name', $s('Email.fromName')) ?>"
                         placeholder="My App">
                </div>
              </div>

              <div class="form-group row">
                <div class="col-sm-9 offset-sm-3">
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Pengaturan Email
                  </button>
                  <button type="button" class="btn btn-outline-info ml-2" data-toggle="modal" data-target="#testMailModal">
                    <i class="fas fa-paper-plane"></i> Test Kirim Email
                  </button>
                </div>
              </div>
            </form>
          </div>

          <!-- ============================================ -->
          <!-- TAB: WAREHOUSING -->
          <!-- ============================================ -->
          <div class="tab-pane fade <?= $activeTab === 'storage' ? 'show active' : '' ?>" id="storage" role="tabpanel">
            <div class="mt-4">

              <?php // Storage content ?>

              <!-- ========== Section A: Storage Overview ========== -->
              <h6 class="text-muted mb-3"><i class="fas fa-chart-pie"></i> Penggunaan Storage</h6>
              <div class="row">
                <?php foreach ($storageInfo as $key => $info): ?>
                <div class="col-md-4 col-sm-6 mb-3">
                  <div class="card card-statistic-2 h-100">
                    <div class="card-body p-3">
                      <div class="d-flex align-items-center">
                        <div class="mr-3">
                          <i class="fas <?= $info['icon'] ?> fa-2x text-muted"></i>
                        </div>
                        <div>
                          <h6 class="mb-0"><?= esc($info['label']) ?></h6>
                          <span class="text-muted small"><?= $info['fileCount'] ?> file</span>
                          <span class="badge badge-light ml-1"><?= $info['sizeHuman'] ?></span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>

              <hr>

              <!-- ========== Section B: Payment Proof Files ========== -->
              <h6 class="text-muted mb-3"><i class="fas fa-file-image"></i> File Bukti Pembayaran</h6>
              <?php if (empty($proofFiles)): ?>
                <div class="alert alert-info"><i class="fas fa-info-circle"></i> Tidak ada file bukti pembayaran.</div>
              <?php else: ?>
                <form action="<?= base_url('admin/settings/cleanup/payment-proofs') ?>" method="post" id="formDeleteProofs">
                  <?= csrf_field() ?>
                  <div class="mb-3">
                    <button type="submit" class="btn btn-danger btn-sm" id="btnBulkDelete" disabled onclick="return confirm('Yakin ingin menghapus file yang dipilih?')">
                      <i class="fas fa-trash-alt"></i> Hapus File Terpilih (<span id="selectedCount">0</span>)
                    </button>
                    <span class="text-muted small ml-2">Hanya file dari order yang sudah selesai/batal yang bisa dihapus.</span>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered">
                      <thead class="thead-light">
                        <tr>
                          <th style="width:40px"><input type="checkbox" id="checkAll"></th>
                          <th>Nama File</th>
                          <th>Ukuran</th>
                          <th>Tanggal</th>
                          <th>No. Order</th>
                          <th>Status Order</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($proofFiles as $pf): ?>
                        <tr>
                          <td>
                            <?php if ($pf['isDeletable']): ?>
                              <input type="checkbox" name="filenames[]" value="<?= esc($pf['filename']) ?>" class="proof-check">
                            <?php else: ?>
                              <i class="fas fa-lock text-muted" title="Tidak bisa dihapus — order masih dalam proses"></i>
                            <?php endif; ?>
                          </td>
                          <td>
                            <a href="<?= base_url('uploads/payment_proofs/' . esc($pf['filename'])) ?>" target="_blank" class="text-primary" title="Lihat file">
                              <i class="fas fa-image"></i> <?= esc($pf['filename']) ?>
                            </a>
                          </td>
                          <td><?= $pf['sizeHuman'] ?></td>
                          <td><?= $pf['modifiedDate'] ?></td>
                          <td>
                            <?php if ($pf['orderNumber']): ?>
                              <code><?= esc($pf['orderNumber']) ?></code>
                            <?php else: ?>
                              <span class="badge badge-warning">Orphaned</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <?php if ($pf['orderStatus']): ?>
                              <?php
                              $statusColors = ['pending' => 'warning', 'awaiting_confirmation' => 'info', 'paid' => 'success', 'cancelled' => 'secondary', 'expired' => 'dark'];
                              $color = $statusColors[$pf['orderStatus']] ?? 'light';
                              ?>
                              <span class="badge badge-<?= $color ?>"><?= esc($pf['orderStatus']) ?></span>
                            <?php else: ?>
                              <span class="text-muted">-</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <?php if ($pf['isOrphaned']): ?>
                              <span class="badge badge-warning" title="File tidak terkait record di database">Orphaned</span>
                            <?php elseif ($pf['isDeletable']): ?>
                              <span class="badge badge-success">Bisa dihapus</span>
                            <?php else: ?>
                              <span class="badge badge-secondary">Dalam proses</span>
                            <?php endif; ?>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </form>
              <?php endif; ?>

              <hr>

              <!-- ========== Section C: Quick Cleanup ========== -->
              <h6 class="text-muted mb-3"><i class="fas fa-broom"></i> Pembersihan Cepat</h6>
              <div class="row">
                <?php
                $cleanupTargets = [
                    'logs'     => ['label' => 'Log Aplikasi',   'desc' => 'Hapus file log lebih dari 30 hari', 'icon' => 'fa-file-alt',  'color' => 'info'],
                    'sessions' => ['label' => 'Session Files',  'desc' => 'Hapus session lebih dari 7 hari',   'icon' => 'fa-clock',     'color' => 'warning'],
                    'debugbar' => ['label' => 'Debug Bar',      'desc' => 'Hapus data debugbar lebih dari 3 hari', 'icon' => 'fa-bug',   'color' => 'secondary'],
                    'cache'    => ['label' => 'Cache',           'desc' => 'Hapus semua file cache',            'icon' => 'fa-database',  'color' => 'danger'],
                ];
                ?>
                <?php foreach ($cleanupTargets as $tKey => $tInfo): ?>
                <div class="col-md-3 col-sm-6 mb-3">
                  <div class="card h-100">
                    <div class="card-body p-3 text-center">
                      <i class="fas <?= $tInfo['icon'] ?> fa-2x text-<?= $tInfo['color'] ?> mb-2"></i>
                      <h6 class="mb-1"><?= $tInfo['label'] ?></h6>
                      <p class="text-muted small mb-2"><?= $tInfo['desc'] ?></p>
                      <span class="badge badge-light mb-2"><?= $storageInfo[$tKey]['fileCount'] ?? 0 ?> file &middot; <?= $storageInfo[$tKey]['sizeHuman'] ?? '0 B' ?></span><br>
                      <form action="<?= base_url('admin/settings/cleanup/' . $tKey) ?>" method="post" class="d-inline" onsubmit="return confirm('Yakin ingin membersihkan <?= esc($tInfo['label']) ?>?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-<?= $tInfo['color'] ?>">
                          <i class="fas fa-broom"></i> Bersihkan
                        </button>
                      </form>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>

              <hr>

              <!-- ========== Section D: Reset Data Transaksi ========== -->
              <h6 class="text-danger mb-3"><i class="fas fa-exclamation-triangle"></i> Reset Data Transaksi</h6>
              <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Perhatian!</strong> Reset data bersifat <strong>permanen</strong> dan <strong>tidak dapat dibatalkan</strong>.
                Data yang dihapus tidak bisa dikembalikan. Pastikan Anda sudah backup data sebelum melakukan reset.
                <br><small class="text-muted">Data master (Plans & Users) tidak akan terpengaruh.</small>
              </div>

              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead class="thead-light">
                    <tr>
                      <th style="width:50px"></th>
                      <th>Data</th>
                      <th>Keterangan</th>
                      <th style="width:120px" class="text-center">Jumlah</th>
                      <th style="width:120px" class="text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($transactionStats as $tKey => $tStat): ?>
                    <tr>
                      <td class="text-center"><i class="fas <?= $tStat['icon'] ?> text-<?= $tStat['color'] ?>"></i></td>
                      <td><strong><?= esc($tStat['label']) ?></strong></td>
                      <td><small class="text-muted"><?= esc($tStat['desc']) ?></small></td>
                      <td class="text-center">
                        <span class="badge badge-<?= $tStat['count'] > 0 ? $tStat['color'] : 'light' ?>" id="count-<?= $tKey ?>">
                          <?= number_format($tStat['count']) ?>
                        </span>
                      </td>
                      <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-reset-data"
                                data-target="<?= $tKey ?>"
                                data-label="<?= esc($tStat['label']) ?>"
                                data-count="<?= $tStat['count'] ?>"
                                <?= $tStat['count'] === 0 ? 'disabled' : '' ?>>
                          <i class="fas fa-redo-alt"></i> Reset
                        </button>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <!-- Saran Manajemen Storage -->
              <!-- <hr>
              <h6 class="text-muted mb-3"><i class="fas fa-lightbulb"></i> Saran Manajemen Storage</h6>
              <div class="row">
                <div class="col-md-4 mb-3">
                  <div class="card border-left-primary h-100">
                    <div class="card-body p-3">
                      <h6 class="text-primary"><i class="fas fa-clock"></i> Cron Job Cleanup</h6>
                      <p class="small text-muted mb-0">Jalankan <code>php spark maintenance:cleanup</code> via cron job harian untuk membersihkan file log, session, dan debugbar secara otomatis.</p>
                    </div>
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <div class="card border-left-warning h-100">
                    <div class="card-body p-3">
                      <h6 class="text-warning"><i class="fas fa-compress-arrows-alt"></i> Kompresi Upload</h6>
                      <p class="small text-muted mb-0">Pertimbangkan untuk mengompres gambar bukti bayar saat upload (resize ke maks 1024px) untuk menghemat ruang disk.</p>
                    </div>
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <div class="card border-left-info h-100">
                    <div class="card-body p-3">
                      <h6 class="text-info"><i class="fas fa-cloud"></i> Cloud Storage</h6>
                      <p class="small text-muted mb-0">Untuk skala besar, pertimbangkan migrasi upload ke cloud storage (S3, GCS) agar tidak membebani disk server.</p>
                    </div>
                  </div>
                </div>
              </div> -->



            </div>
          </div>

        </div><!-- end tab-content -->
      </div>
    </div>
  </div>
</div>

<!-- Modal Test Email -->
<div class="modal fade" id="testMailModal" tabindex="-1" role="dialog" aria-labelledby="testMailModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="testMailModalLabel"><i class="fas fa-paper-plane"></i> Test Kirim Email</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="text-muted small">Email akan dikirim menggunakan konfigurasi SMTP yang sudah tersimpan.</p>
        <div class="form-group">
          <label for="test_email_to">Email Tujuan <span class="text-danger">*</span></label>
          <input type="email" class="form-control" id="test_email_to" placeholder="contoh@email.com" required>
        </div>
        <div class="form-group">
          <label for="test_email_subject">Subject</label>
          <input type="text" class="form-control" id="test_email_subject" value="Test Email - <?= esc(setting('App.siteName') ?? 'CI4 Shield RBAC') ?>">
        </div>
        <div class="form-group">
          <label for="test_email_message">Pesan</label>
          <textarea class="form-control" id="test_email_message" rows="3">Ini adalah email percobaan dari sistem. Jika Anda menerima email ini, berarti konfigurasi email sudah benar.</textarea>
        </div>
        <div id="test-mail-result" style="display:none;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-primary" id="btn-send-test-mail">
          <i class="fas fa-paper-plane"></i> Kirim
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Reset Data Transaksi -->
<div class="modal fade" id="resetDataModal" tabindex="-1" role="dialog" aria-labelledby="resetDataModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="resetDataModalLabel"><i class="fas fa-exclamation-triangle"></i> Konfirmasi Reset Data</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-triangle"></i>
          <strong>Peringatan!</strong> Tindakan ini <strong>tidak dapat dibatalkan</strong>. Data yang dihapus tidak bisa dikembalikan.
        </div>
        <p>Anda akan mereset data: <strong id="resetTargetLabel"></strong></p>
        <p>Jumlah record yang akan dihapus: <strong id="resetTargetCount" class="text-danger"></strong></p>
        <hr>
        <div class="form-group">
          <label for="resetPassword"><i class="fas fa-lock"></i> Masukkan password Anda untuk konfirmasi:</label>
          <input type="password" class="form-control" id="resetPassword" placeholder="Password akun Anda" autocomplete="off">
          <div id="resetPasswordError" class="invalid-feedback"></div>
        </div>
        <div id="resetResult" style="display:none;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger" id="btnConfirmReset">
          <i class="fas fa-redo-alt"></i> Ya, Reset Data
        </button>
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

  // Move test mail modal to body to avoid z-index/backdrop issues
  $('#testMailModal').appendTo('body');

  // Test send email
  $('#btn-send-test-mail').on('click', function() {
    var btn = $(this);
    var email = $('#test_email_to').val().trim();
    if (!email) {
      $('#test_email_to').focus();
      return;
    }
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengirim...');
    $('#test-mail-result').hide();

    $.ajax({
      url: '<?= base_url('admin/settings/test-mail') ?>',
      method: 'POST',
      data: {
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
        to: email,
        subject: $('#test_email_subject').val(),
        message: $('#test_email_message').val()
      },
      dataType: 'json',
      success: function(res) {
        var cls = res.success ? 'alert-success' : 'alert-danger';
        var icon = res.success ? 'fa-check-circle' : 'fa-times-circle';
        $('#test-mail-result').removeClass('alert-success alert-danger')
          .addClass('alert ' + cls)
          .html('<i class="fas ' + icon + '"></i> ' + res.message)
          .show();
      },
      error: function(xhr) {
        var msg = 'Terjadi kesalahan saat mengirim email.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }
        $('#test-mail-result').removeClass('alert-success alert-danger')
          .addClass('alert alert-danger')
          .html('<i class="fas fa-times-circle"></i> ' + msg)
          .show();
      },
      complete: function() {
        btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Kirim');
      }
    });
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

  // ================================================================
  // Warehousing Tab: Payment Proof Checkbox Handling
  // ================================================================
  function updateSelectedCount() {
    var count = $('.proof-check:checked').length;
    $('#selectedCount').text(count);
    $('#btnBulkDelete').prop('disabled', count === 0);
  }

  $('#checkAll').on('change', function() {
    $('.proof-check').prop('checked', $(this).is(':checked'));
    updateSelectedCount();
  });

  $(document).on('change', '.proof-check', function() {
    var total = $('.proof-check').length;
    var checked = $('.proof-check:checked').length;
    $('#checkAll').prop('checked', total === checked);
    updateSelectedCount();
  });

  // ================================================================
  // Warehousing Tab: Reset Data Transaction
  // ================================================================
  var resetTarget = '';

  // Move reset modal to body
  $('#resetDataModal').appendTo('body');

  $(document).on('click', '.btn-reset-data', function() {
    resetTarget = $(this).data('target');
    var label = $(this).data('label');
    var count = $(this).data('count');

    $('#resetTargetLabel').text(label);
    $('#resetTargetCount').text(count);
    $('#resetPassword').val('').removeClass('is-invalid');
    $('#resetPasswordError').text('');
    $('#resetResult').hide();
    $('#btnConfirmReset').prop('disabled', false).html('<i class="fas fa-redo-alt"></i> Ya, Reset Data');

    $('#resetDataModal').modal('show');
  });

  $('#btnConfirmReset').on('click', function() {
    var btn = $(this);
    var password = $('#resetPassword').val().trim();

    if (!password) {
      $('#resetPassword').addClass('is-invalid');
      $('#resetPasswordError').text('Password wajib diisi.').show();
      return;
    }

    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
    $('#resetPassword').removeClass('is-invalid');
    $('#resetResult').hide();

    $.ajax({
      url: '<?= base_url('admin/settings/reset-data') ?>',
      method: 'POST',
      data: {
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
        target: resetTarget,
        password: password
      },
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          $('#resetResult').removeClass('alert-danger').addClass('alert alert-success')
            .html('<i class="fas fa-check-circle"></i> ' + res.message).show();
          btn.html('<i class="fas fa-check"></i> Berhasil');
          // Reload page after 1.5s
          setTimeout(function() {
            window.location.href = '<?= base_url('admin/settings') ?>?tab=storage';
          }, 1500);
        } else {
          $('#resetPassword').addClass('is-invalid');
          $('#resetPasswordError').text(res.message).show();
          btn.prop('disabled', false).html('<i class="fas fa-redo-alt"></i> Ya, Reset Data');
        }
      },
      error: function() {
        $('#resetResult').removeClass('alert-success').addClass('alert alert-danger')
          .html('<i class="fas fa-times-circle"></i> Terjadi kesalahan. Silakan coba lagi.').show();
        btn.prop('disabled', false).html('<i class="fas fa-redo-alt"></i> Ya, Reset Data');
      }
    });
  });
});
</script>
