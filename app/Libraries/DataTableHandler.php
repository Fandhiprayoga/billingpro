<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * DataTableHandler
 *
 * Library untuk memproses request DataTables server-side.
 * Mendukung: paging, ordering, global search, column search, dan filter custom.
 */
class DataTableHandler
{
    protected IncomingRequest $request;
    protected BaseBuilder $builder;
    protected BaseBuilder $countBuilder;
    protected array $columnMap = [];
    protected int $totalRecords = 0;

    public function __construct(IncomingRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Set builder utama (sudah di-join / di-select)
     */
    public function setBuilder(BaseBuilder $builder): static
    {
        $this->builder = $builder;
        return $this;
    }

    /**
     * Set builder terpisah untuk menghitung total record (tanpa filter).
     */
    public function setCountBuilder(BaseBuilder $countBuilder): static
    {
        $this->countBuilder = $countBuilder;
        return $this;
    }

    /**
     * Set mapping kolom DataTables index => nama kolom database.
     * Contoh: [0 => 'users.id', 1 => 'users.username', ...]
     */
    public function setColumnMap(array $columnMap): static
    {
        $this->columnMap = $columnMap;
        return $this;
    }

    /**
     * Proses request dan kembalikan response array untuk DataTables.
     */
    public function process(): array
    {
        $draw   = (int) $this->request->getGet('draw');
        $start  = (int) $this->request->getGet('start');
        $length = (int) $this->request->getGet('length');
        $search = $this->request->getGet('search')['value'] ?? '';
        $order  = $this->request->getGet('order') ?? [];

        // Total records (tanpa filter apapun)
        $totalBuilder = isset($this->countBuilder) ? $this->countBuilder : clone $this->builder;
        $this->totalRecords = $totalBuilder->countAllResults(false);

        // Global search
        if (!empty($search)) {
            $searchableColumns = array_values($this->columnMap);
            $this->builder->groupStart();
            foreach ($searchableColumns as $i => $col) {
                if (empty($col)) continue;
                if ($i === 0) {
                    $this->builder->like($col, $search);
                } else {
                    $this->builder->orLike($col, $search);
                }
            }
            $this->builder->groupEnd();
        }

        // Filtered count (setelah search, sebelum paging)
        $filteredBuilder = clone $this->builder;
        $filteredCount = $filteredBuilder->countAllResults(false);

        // Ordering
        if (!empty($order)) {
            foreach ($order as $o) {
                $colIdx = (int) $o['column'];
                $dir    = strtolower($o['dir']) === 'desc' ? 'DESC' : 'ASC';
                if (isset($this->columnMap[$colIdx]) && !empty($this->columnMap[$colIdx])) {
                    $this->builder->orderBy($this->columnMap[$colIdx], $dir);
                }
            }
        }

        // Paging
        if ($length > 0) {
            $this->builder->limit($length, $start);
        }

        $data = $this->builder->get()->getResult();

        return [
            'draw'            => $draw,
            'recordsTotal'    => $this->totalRecords,
            'recordsFiltered' => $filteredCount,
            'data'            => $data,
        ];
    }
}
