<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Daftar Order</h4>
        <div class="card-header-action">
          <?php if (activeGroupCan('orders.create')): ?>
          <a href="<?= base_url('admin/orders/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat Order
          </a>
          <?php endif; ?>
        </div>
      </div>
      <div class="card-body">
        <!-- Filters -->
        <div class="row mb-4">
          <div class="col-md-3">
            <label class="small font-weight-bold">Status</label>
            <select id="filter-status" class="form-control select2" multiple>
              <option value="pending" selected>Pending</option>
              <option value="awaiting_confirmation" selected>Menunggu Review</option>
              <option value="paid">Lunas</option>
              <option value="cancelled">Dibatalkan</option>
              <option value="expired">Kadaluarsa</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="small font-weight-bold">Paket</label>
            <select id="filter-plan" class="form-control select2">
              <option value="">Semua Paket</option>
              <?php if (!empty($plans)): ?>
                <?php foreach ($plans as $plan): ?>
                <option value="<?= $plan->id ?>"><?= esc($plan->name) ?></option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="small font-weight-bold">Dari Tanggal</label>
            <input type="date" id="filter-date-from" class="form-control">
          </div>
          <div class="col-md-2">
            <label class="small font-weight-bold">Sampai Tanggal</label>
            <input type="date" id="filter-date-to" class="form-control">
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button id="btn-reset" class="btn btn-outline-secondary btn-sm">
              <i class="fas fa-undo"></i> Reset
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped" id="table-orders" style="width:100%">
            <thead>
              <tr>
                <th class="text-center" width="50">#</th>
                <th>No. Order</th>
                <th>User</th>
                <th>Paket</th>
                <th>Jumlah</th>
                <th>Metode</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th width="80">Aksi</th>
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
  var canView = <?= activeGroupCan('orders.view') ? 'true' : 'false' ?>;

  $('.select2').select2({ width: '100%', placeholder: 'Pilih...' });

  var statusBadges = {
    'pending': 'badge-warning',
    'awaiting_confirmation': 'badge-info',
    'paid': 'badge-success',
    'cancelled': 'badge-danger',
    'expired': 'badge-secondary'
  };
  var statusLabels = {
    'pending': 'Pending',
    'awaiting_confirmation': 'Menunggu Review',
    'paid': 'Lunas',
    'cancelled': 'Dibatalkan',
    'expired': 'Kadaluarsa'
  };

  function formatRupiah(val) {
    return 'Rp ' + parseInt(val).toLocaleString('id-ID');
  }

  function formatDate(dateStr) {
    if (!dateStr) return '-';
    var d = new Date(dateStr);
    var dd = ('0' + d.getDate()).slice(-2);
    var mm = ('0' + (d.getMonth() + 1)).slice(-2);
    var hh = ('0' + d.getHours()).slice(-2);
    var mi = ('0' + d.getMinutes()).slice(-2);
    return dd + '/' + mm + '/' + d.getFullYear() + ' ' + hh + ':' + mi;
  }

  var table = $('#table-orders').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= base_url('admin/orders/ajax') ?>',
      data: function(d) {
        var status = $('#filter-status').val();
        d.status    = status ? status.join(',') : '';
        d.plan_id   = $('#filter-plan').val();
        d.date_from = $('#filter-date-from').val();
        d.date_to   = $('#filter-date-to').val();
      }
    },
    order: [[0, 'desc']],
    columns: [
      { data: 'id', className: 'text-center',
        render: function(data, type, row, meta) {
          return meta.row + meta.settings._iDisplayStart + 1;
        }
      },
      { data: 'order_number',
        render: function(data) { return '<code>' + $('<span>').text(data).html() + '</code>'; }
      },
      { data: 'username',
        render: function(data) { return $('<span>').text(data || '-').html(); }
      },
      { data: 'plan_name',
        render: function(data) { return $('<span>').text(data || '-').html(); }
      },
      { data: 'amount',
        render: function(data) { return formatRupiah(data); }
      },
      { data: 'payment_method',
        render: function(data) {
          return '<span class="badge badge-light">' + (data ? data.charAt(0).toUpperCase() + data.slice(1) : '-') + '</span>';
        }
      },
      { data: 'status',
        render: function(data) {
          var cls = statusBadges[data] || 'badge-light';
          var lbl = statusLabels[data] || data;
          return '<span class="badge ' + cls + '">' + lbl + '</span>';
        }
      },
      { data: 'created_at',
        render: function(data) { return formatDate(data); }
      },
      { data: 'order_number', orderable: false,
        render: function(data) {
          if (!canView) return '';
          return '<a href="<?= base_url('admin/orders/view/') ?>' + data + '" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i></a>';
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
      emptyTable: 'Belum ada data order',
      paginate: { first: '«', previous: '‹', next: '›', last: '»' }
    }
  });

  $('#filter-status, #filter-plan').on('change', function() { table.draw(); });
  $('#filter-date-from, #filter-date-to').on('change', function() { table.draw(); });
  $('#btn-reset').on('click', function() {
    $('#filter-status').val(null).trigger('change');
    $('#filter-plan').val('').trigger('change');
    $('#filter-date-from').val('');
    $('#filter-date-to').val('');
    table.draw();
  });
});
</script>
