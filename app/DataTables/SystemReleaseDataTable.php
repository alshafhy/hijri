<?php

namespace App\DataTables;

use App\Models\SystemRelease;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\Html\Column;

class SystemReleaseDataTable extends AppDataTable
{
    public function __construct()
    {
        $this->dataTableName = 'systemReleases';
        $this->actionViewBlade = 'system_releases.datatables_actions';
    }

    /**
     * Get query source of dataTable.
     *
     * @return Builder
     */
    public function query(SystemRelease $model)
    {
        return $model->newQuery();
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            'index' => $this->getIndexCol(),
            'version_number' => new Column(['title' => __('models/systemReleases.fields.version_number'), 'data' => 'version_number']),
            'release_date' => new Column(['title' => __('models/systemReleases.fields.release_date'), 'data' => 'release_date']),
        ];
    }
}
