<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Daftar User</h4>
        <div class="card-header-action">
          <?php if (activeGroupCan('users.create')): ?>
          <a href="<?= base_url('admin/users/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah User
          </a>
          <?php endif; ?>
        </div>
      </div>
      <div class="card-body">
        <!-- Filters -->
        <div class="row mb-4">
          <div class="col-md-3">
            <label class="small font-weight-bold">Role</label>
            <select id="filter-role" class="form-control select2">
              <option value="">Semua Role</option>
              <option value="superadmin">Super Admin</option>
              <option value="admin">Admin</option>
              <option value="manager">Manager</option>
              <option value="user">User</option>
            </select>
          </div>
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
          <table class="table table-striped" id="table-users" style="width:100%">
            <thead>
              <tr>
                <th class="text-center" width="50">#</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
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
  var canEdit = <?= activeGroupCan('users.edit') ? 'true' : 'false' ?>;
  var canDelete = <?= activeGroupCan('users.delete') ? 'true' : 'false' ?>;
  var currentUserId = <?= auth()->id() ?>;

  $('.select2').select2({ width: '100%' });

  var table = $('#table-users').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= base_url('admin/users/ajax') ?>',
      data: function(d) {
        d.role   = $('#filter-role').val();
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
      { data: 'username',
        render: function(data) {
          return '<img alt="avatar" src="<?= base_url('assets/img/avatar/avatar-1.png') ?>" class="rounded-circle mr-1" width="35"> ' + $('<span>').text(data).html();
        }
      },
      { data: 'email',
        render: function(data) { return $('<span>').text(data).html(); }
      },
      { data: 'groups', orderable: false,
        render: function(data) {
          if (!data || data.length === 0) return '<span class="badge badge-secondary">No Role</span>';
          var badges = { superadmin: 'badge-danger', admin: 'badge-warning', manager: 'badge-info' };
          return data.map(function(g) {
            var cls = badges[g] || 'badge-primary';
            return '<span class="badge ' + cls + '">' + g.charAt(0).toUpperCase() + g.slice(1) + '</span>';
          }).join(' ');
        }
      },
      { data: 'active',
        render: function(data) {
          return data == 1
            ? '<span class="badge badge-success">Aktif</span>'
            : '<span class="badge badge-danger">Nonaktif</span>';
        }
      },
      { data: 'id', orderable: false,
        render: function(data) {
          var html = '';
          if (canEdit) {
            html += '<a href="<?= base_url('admin/users/edit/') ?>' + data + '" class="btn btn-sm btn-info" title="Edit"><i class="fas fa-edit"></i></a> ';
          }
          if (canDelete && data != currentUserId) {
            html += '<form action="<?= base_url('admin/users/delete/') ?>' + data + '" method="post" class="d-inline" onsubmit="return confirm(\'Yakin ingin menghapus user ini?\')">';
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
      emptyTable: 'Belum ada data user',
      paginate: { first: '«', previous: '‹', next: '›', last: '»' }
    }
  });

  $('#filter-role, #filter-status').on('change', function() { table.draw(); });
  $('#btn-reset').on('click', function() {
    $('#filter-role').val('').trigger('change');
    $('#filter-status').val('').trigger('change');
  });
});
</script>
