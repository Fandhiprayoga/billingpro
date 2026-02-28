<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Daftar Lisensi Trial</h4>
        <div class="card-header-action">
          <?php if (activeGroupCan('trial-licenses.create')): ?>
          <a href="<?= base_url('admin/trial-licenses/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat Lisensi Trial
          </a>
          <?php endif; ?>
        </div>
      </div>
      <div class="card-body">
        <!-- Filters -->
        <div class="row mb-4">
          <div class="col-md-3">
            <label class="small font-weight-bold">Status</label>
            <select id="filter-status" class="form-control select2">
              <option value="">Semua Status</option>
              <option value="active" selected>Aktif</option>
              <option value="expired">Expired</option>
              <option value="revoked">Dicabut</option>
              <option value="suspended">Ditangguhkan</option>
            </select>
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <button id="btn-reset" class="btn btn-outline-secondary btn-sm">
              <i class="fas fa-undo"></i> Reset Filter
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped" id="table-trial-licenses" style="width:100%">
            <thead>
              <tr>
                <th class="text-center" width="50">#</th>
                <th>License Key</th>
                <th>User</th>
                <th>Durasi</th>
                <th>Berlaku Sampai</th>
                <th>Device ID</th>
                <th>Status</th>
                <th>Dibuat Oleh</th>
                <th>Tgl Dibuat</th>
                <th width="100">Aksi</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
$(function() {
  var csrfName = '<?= csrf_token() ?>';
  var csrfHash = '<?= csrf_hash() ?>';
  var canView = <?= activeGroupCan('trial-licenses.view') ? 'true' : 'false' ?>;
  var canRevoke = <?= activeGroupCan('trial-licenses.revoke') ? 'true' : 'false' ?>;

  $('.select2').select2({ width: '100%' });

  var statusBadges = {
    'active': 'badge-success',
    'expired': 'badge-secondary',
    'revoked': 'badge-danger',
    'suspended': 'badge-warning'
  };

  var table = $('#table-trial-licenses').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= base_url('admin/trial-licenses/ajax') ?>',
      data: function(d) {
        d.status = $('#filter-status').val();
      }
    },
    order: [[0, 'desc']],
    columns: [
      { data: 'id', className: 'text-center',
        render: function(data, type, row, meta) {
          return meta.row + meta.settings._iDisplayStart + 1;
        }
      },
      { data: 'license_key',
        render: function(data) {
          var escaped = $('<span>').text(data).html();
          return '<code>' + escaped + '</code> ' +
            '<button type="button" class="btn btn-sm btn-outline-primary py-0 px-1 btn-copy-key" data-key="' + escaped + '" title="Salin">' +
            '<i class="fas fa-copy"></i></button>';
        }
      },
      { data: 'username',
        render: function(data) { return $('<span>').text(data || '-').html(); }
      },
      { data: 'trial_duration_days',
        render: function(data) {
          return data ? data + ' hari' : '-';
        }
      },
      { data: 'expires_at',
        render: function(data) {
          if (!data) return '-';
          var d = new Date(data);
          var dd = ('0' + d.getDate()).slice(-2);
          var mm = ('0' + (d.getMonth() + 1)).slice(-2);
          return dd + '/' + mm + '/' + d.getFullYear();
        }
      },
      { data: 'device_id',
        render: function(data) {
          if (data) {
            var short = data.length > 15 ? data.substring(0, 15) + '...' : data;
            return '<small class="text-monospace">' + $('<span>').text(short).html() + '</small>';
          }
          return '<span class="text-muted">-</span>';
        }
      },
      { data: 'status',
        render: function(data) {
          var cls = statusBadges[data] || 'badge-light';
          return '<span class="badge ' + cls + '">' + (data ? data.charAt(0).toUpperCase() + data.slice(1) : '-') + '</span>';
        }
      },
      { data: 'created_by_name',
        render: function(data) { return $('<span>').text(data || '-').html(); }
      },
      { data: 'created_at',
        render: function(data) {
          if (!data) return '-';
          var d = new Date(data);
          var dd = ('0' + d.getDate()).slice(-2);
          var mm = ('0' + (d.getMonth() + 1)).slice(-2);
          return dd + '/' + mm + '/' + d.getFullYear();
        }
      },
      { data: null, orderable: false,
        render: function(data, type, row) {
          var html = '';
          if (canView) {
            html += '<a href="<?= base_url('admin/trial-licenses/view/') ?>' + row.uuid + '" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i></a> ';
          }
          if (canRevoke && row.status === 'active') {
            html += '<form action="<?= base_url('admin/trial-licenses/revoke/') ?>' + row.uuid + '" method="post" class="d-inline" onsubmit="return confirm(\'Yakin ingin mencabut lisensi trial ini?\')">'; 
            html += '<input type="hidden" name="' + csrfName + '" value="' + csrfHash + '">';
            html += '<button type="submit" class="btn btn-sm btn-danger" title="Cabut Lisensi"><i class="fas fa-ban"></i></button></form>';
          }
          return html || '-';
        }
      }
    ],
    language: {
      processing: 'Memuat...',
      search: 'Cari:',
      lengthMenu: 'Tampilkan _MENU_ data',
      info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
      infoEmpty: 'Tidak ada data',
      infoFiltered: '(difilter dari _MAX_ total data)',
      zeroRecords: 'Tidak ada data yang cocok',
      emptyTable: 'Belum ada lisensi trial',
      paginate: { first: '«', previous: '‹', next: '›', last: '»' }
    }
  });

  $('#filter-status').on('change', function() { table.draw(); });
  $('#btn-reset').on('click', function() {
    $('#filter-status').val('').trigger('change');
  });

  // Copy license key
  $(document).on('click', '.btn-copy-key', function() {
    var key = $(this).data('key');
    navigator.clipboard.writeText(key).then(function() {
      if (typeof iziToast !== 'undefined') {
        iziToast.success({ title: 'Berhasil', message: 'License key berhasil disalin!', position: 'topRight' });
      } else {
        alert('License key berhasil disalin!');
      }
    }).catch(function() {
      var el = document.createElement('textarea');
      el.value = key;
      document.body.appendChild(el);
      el.select();
      document.execCommand('copy');
      document.body.removeChild(el);
      alert('License key berhasil disalin!');
    });
  });
});
</script>
