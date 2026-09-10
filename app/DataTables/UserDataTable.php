<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\Html\Column;

class UserDataTable extends AppDataTable
{
    public function __construct()
    {
        $this->dataTableName = 'users';
        $this->actionViewBlade = 'users.datatables_actions';
    }

    /**
     * Get query source of dataTable.
     *
     * @return Builder
     */
    public function query(User $model)
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
            'name' => new Column(['title' => __('models/users.fields.name'), 'data' => 'name']),
            'email' => new Column(['title' => __('models/users.fields.email'), 'data' => 'email']),
            'username' => new Column(['title' => __('models/users.fields.username'), 'data' => 'username']),
        ];
    }
}
