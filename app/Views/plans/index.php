<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Daftar Paket Lisensi</h4>
        <div class="card-header-action">
          <?php if (activeGroupCan('plans.create')): ?>
          <a href="<?= base_url('admin/plans/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Paket
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
              <option value="1" selected>Aktif</option>
              <option value="0">Nonaktif</option>
            </select>
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <button id="btn-reset" class="btn btn-outline-secondary btn-sm">
              <i class="fas fa-undo"></i> Reset Filter
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped" id="table-plans" style="width:100%">
            <thead>
              <tr>
                <th class="text-center" width="50">#</th>
                <th>Nama Paket</th>
                <th>Harga</th>
                <th>Durasi</th>
                <th>Status</th>
                <th width="120">Aksi</th>
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
  var canEdit = <?= activeGroupCan('plans.edit') ? 'true' : 'false' ?>;
  var canDelete = <?= activeGroupCan('plans.delete') ? 'true' : 'false' ?>;

  $('.select2').select2({ width: '100%' });

  function formatRupiah(val) {
    return 'Rp ' + parseInt(val).toLocaleString('id-ID');
  }

  var table = $('#table-plans').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= base_url('admin/plans/ajax') ?>',
      data: function(d) {
        d.is_active = $('#filter-status').val();
      }
    },
    order: [[0, 'desc']],
    columns: [
      { data: 'id', className: 'text-center',
        render: function(data, type, row, meta) {
          return meta.row + meta.settings._iDisplayStart + 1;
        }
      },
      { data: 'name',
        render: function(data, type, row) {
          var html = '<strong>' + $('<span>').text(data).html() + '</strong>';
          if (row.description) {
            html += '<br><small class="text-muted">' + $('<span>').text(row.description).html() + '</small>';
          }
          return html;
        }
      },
      { data: 'price',
        render: function(data) { return formatRupiah(data); }
      },
      { data: 'duration_days',
        render: function(data) { return data + ' hari'; }
      },
      { data: 'is_active',
        render: function(data) {
          return data == 1
            ? '<span class="badge badge-success">Aktif</span>'
            : '<span class="badge badge-secondary">Nonaktif</span>';
        }
      },
      { data: 'id', orderable: false,
        render: function(data) {
          var html = '';
          if (canEdit) {
            html += '<a href="<?= base_url('admin/plans/edit/') ?>' + data + '" class="btn btn-sm btn-info" title="Edit"><i class="fas fa-edit"></i></a> ';
          }
          if (canDelete) {
            html += '<form action="<?= base_url('admin/plans/delete/') ?>' + data + '" method="post" class="d-inline" onsubmit="return confirm(\'Yakin ingin menghapus paket ini?\')">';
            html += '<input type="hidden" name="' + csrfName + '" value="' + csrfHash + '">';
            html += '<button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button></form>';
          }
          return html;
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
      emptyTable: 'Belum ada data paket',
      paginate: { first: '«', previous: '‹', next: '›', last: '»' }
    }
  });

  $('#filter-status').on('change', function() { table.draw(); });
  $('#btn-reset').on('click', function() {
    $('#filter-status').val('').trigger('change');
  });
});
</script>
