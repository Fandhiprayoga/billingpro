<div class="row" id="summary-cards">
  <div class="col-lg-4 col-md-6">
    <div class="card card-statistic-1">
      <div class="card-icon bg-primary"><i class="fas fa-money-bill-wave"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Total Pendapatan</h4></div>
        <div class="card-body" id="card-total">
          <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4 col-md-6">
    <div class="card card-statistic-1">
      <div class="card-icon bg-success"><i class="fas fa-shopping-cart"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Pembelian Baru</h4></div>
        <div class="card-body" id="card-new">
          <div class="spinner-border spinner-border-sm text-success" role="status"></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4 col-md-6">
    <div class="card card-statistic-1">
      <div class="card-icon bg-info"><i class="fas fa-sync-alt"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Perpanjangan / Topup</h4></div>
        <div class="card-body" id="card-renewal">
          <div class="spinner-border spinner-border-sm text-info" role="status"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-chart-line mr-1"></i> Laporan Pendapatan</h4>
      </div>
      <div class="card-body">

        <!-- Filters -->
        <div class="row mb-4">
          <div class="col-md-3">
            <label class="small font-weight-bold">Dari Tanggal</label>
            <input type="date" id="filter-date-from" class="form-control" value="<?= date('Y-m-01') ?>">
          </div>
          <div class="col-md-3">
            <label class="small font-weight-bold">Sampai Tanggal</label>
            <input type="date" id="filter-date-to" class="form-control" value="<?= date('Y-m-d') ?>">
          </div>
          <div class="col-md-2">
            <label class="small font-weight-bold">Tipe</label>
            <select id="filter-type" class="form-control">
              <option value="">Semua</option>
              <option value="new">Pembelian Baru</option>
              <option value="renewal">Perpanjangan</option>
            </select>
          </div>
          <div class="col-md-4 d-flex align-items-end">
            <button id="btn-filter" class="btn btn-primary mr-2">
              <i class="fas fa-search"></i> Filter
            </button>
            <button id="btn-reset" class="btn btn-outline-secondary mr-2">
              <i class="fas fa-undo"></i> Reset
            </button>

            <?php if (activeGroupCan('reports.export')): ?>
            <div class="dropdown">
              <button class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                <i class="fas fa-download"></i> Export
              </button>
              <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="#" id="export-excel"><i class="fas fa-file-excel mr-2 text-success"></i>Excel (.xls)</a>
                <a class="dropdown-item" href="#" id="export-csv"><i class="fas fa-file-csv mr-2 text-primary"></i>CSV (.csv)</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" id="export-pdf"><i class="fas fa-file-pdf mr-2 text-danger"></i>PDF (.pdf)</a>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Data Table -->
        <div class="table-responsive">
          <table class="table table-striped" id="table-revenue" style="width:100%">
            <thead>
              <tr>
                <th class="text-center" width="50">#</th>
                <th>Tanggal Bayar</th>
                <th>No. Order</th>
                <th>User</th>
                <th>Paket</th>
                <th>Tipe</th>
                <th class="text-right">Jumlah (Rp)</th>
              </tr>
            </thead>
            <tfoot>
              <tr class="font-weight-bold bg-light">
                <td colspan="6" class="text-right"><strong>Total:</strong></td>
                <td class="text-right" id="tfoot-total">-</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
$(function() {
  var baseExportUrl = '<?= base_url('admin/reports/revenue/export') ?>';

  function getFilterParams() {
    return {
      date_from: $('#filter-date-from').val(),
      date_to:   $('#filter-date-to').val(),
      type:      $('#filter-type').val()
    };
  }

  function buildExportUrl(format) {
    var params = getFilterParams();
    params.format = format;
    return baseExportUrl + '?' + $.param(params);
  }

  function formatRupiah(num) {
    return new Intl.NumberFormat('id-ID').format(num);
  }

  // Summary cards
  function loadSummary() {
    var params = getFilterParams();
    $.getJSON('<?= base_url('admin/reports/revenue/summary') ?>', params, function(data) {
      $('#card-total').html('Rp ' + formatRupiah(data.total.amount) + '<br><small class="text-muted">' + data.total.count + ' transaksi</small>');
      $('#card-new').html('Rp ' + formatRupiah(data.new.amount) + '<br><small class="text-muted">' + data.new.count + ' transaksi</small>');
      $('#card-renewal').html('Rp ' + formatRupiah(data.renewal.amount) + '<br><small class="text-muted">' + data.renewal.count + ' transaksi</small>');
    });
  }

  // DataTable
  var table = $('#table-revenue').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= base_url('admin/reports/revenue/ajax') ?>',
      data: function(d) {
        d.date_from = $('#filter-date-from').val();
        d.date_to   = $('#filter-date-to').val();
        d.type      = $('#filter-type').val();
      }
    },
    order: [[1, 'desc']],
    columns: [
      { data: 'id', className: 'text-center',
        render: function(data, type, row, meta) {
          return meta.row + meta.settings._iDisplayStart + 1;
        }
      },
      { data: 'paid_at',
        render: function(data) {
          if (!data) return '-';
          var d = new Date(data);
          var dd = ('0'+d.getDate()).slice(-2);
          var mm = ('0'+(d.getMonth()+1)).slice(-2);
          return dd+'/'+mm+'/'+d.getFullYear()+' '+('0'+d.getHours()).slice(-2)+':'+('0'+d.getMinutes()).slice(-2);
        }
      },
      { data: 'order_number',
        render: function(data) { return '<code class="small">' + $('<span>').text(data).html() + '</code>'; }
      },
      { data: 'username',
        render: function(data) { return $('<span>').text(data || '-').html(); }
      },
      { data: 'plan_name',
        render: function(data, type, row) {
          var name = $('<span>').text(data || '-').html();
          if (row.duration_days) name += ' <small class="text-muted">(' + row.duration_days + 'hr)</small>';
          return name;
        }
      },
      { data: 'type',
        render: function(data) {
          if (data === 'renewal') {
            return '<span class="badge badge-info"><i class="fas fa-sync-alt mr-1"></i>Perpanjangan</span>';
          }
          return '<span class="badge badge-primary"><i class="fas fa-shopping-cart mr-1"></i>Pembelian</span>';
        }
      },
      { data: 'amount', className: 'text-right font-weight-bold',
        render: function(data) { return 'Rp ' + formatRupiah(data); }
      }
    ],
    drawCallback: function(settings) {
      // Calculate footer total from all server-side data
      var api = this.api();
      var pageTotal = 0;
      api.column(6, { page: 'current' }).data().each(function(val) {
        pageTotal += parseFloat(val) || 0;
      });
      $('#tfoot-total').html('<strong>Rp ' + formatRupiah(pageTotal) + '</strong>');
    },
    language: {
      processing: 'Memuat...',
      search: 'Cari:',
      lengthMenu: 'Tampilkan _MENU_ data',
      info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
      infoEmpty: 'Tidak ada data',
      infoFiltered: '(difilter dari _MAX_ total data)',
      zeroRecords: 'Tidak ada transaksi yang cocok',
      emptyTable: 'Belum ada data pendapatan.',
      paginate: { first: '«', previous: '‹', next: '›', last: '»' }
    }
  });

  // Filter button
  $('#btn-filter').on('click', function() {
    table.draw();
    loadSummary();
  });

  // Reset button
  $('#btn-reset').on('click', function() {
    $('#filter-date-from').val('<?= date('Y-m-01') ?>');
    $('#filter-date-to').val('<?= date('Y-m-d') ?>');
    $('#filter-type').val('');
    table.draw();
    loadSummary();
  });

  // Export buttons
  $('#export-excel').on('click', function(e) { e.preventDefault(); window.location.href = buildExportUrl('excel'); });
  $('#export-csv').on('click', function(e) { e.preventDefault(); window.location.href = buildExportUrl('csv'); });
  $('#export-pdf').on('click', function(e) { e.preventDefault(); window.open(buildExportUrl('pdf'), '_blank'); });

  // Initial load
  loadSummary();
});
</script>
