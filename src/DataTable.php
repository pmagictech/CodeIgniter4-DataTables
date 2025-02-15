<?php

namespace Pmagictech\DataTables;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class DataTable
{

    /**
     * DataTableQuery object.
     *
     * @var DataTableQuery
     */
    private $query;


    /**
     * DataTableColumnDefs object.
     *
     * @var DataTableColumnDefs
     */
    private $columnDefs;


    /**
     * Primary key of the table
     *
     * @var string
     */
    private $primaryKey;


    /**
     * Builder from CodeIgniter Query Builder
     * @param  BaseBuilder | Model $builder
     */
    public function __construct($builder, $primaryKey = 'id')
    {
        if (is_subclass_of($builder, '\CodeIgniter\BaseModel') && method_exists($builder, 'builder')) {
            $builder = $builder->builder();
        }
        $this->query      = new DataTableQuery($builder);
        $this->columnDefs = new DataTableColumnDefs($builder, $primaryKey);
        $this->primaryKey = $primaryKey;
    }


    /**
     * Make a DataTable instance from builder.
     *
     * Builder from CodeIgniter Query Builder
     * @param  BaseBuilder $builder
     */
    public static function of($builder, $primaryKey = 'id')
    {
        return new self($builder, $primaryKey);
    }


    /**
     * postQuery
     *
     * @param \Closure $postQuery
     */
    public function postQuery($postQuery)
    {
        $this->query->setPostQuery($postQuery);
        return $this;
    }

    /**
     * custom Filter
     * @param \Closure function
     */
    public function filter($filterFunction)
    {
        $this->query->filter($filterFunction);
        return $this;
    }

    /**
     * @param \Closure|string $rowClass
     */
    public function setRowClass($rowClass)
    {
        $this->query->setRowClass($rowClass);
        return $this;
    }


    /**
     * Add numbering to first column
     * @param String $column
     */
    public function addNumbering($column = NULL)
    {
        $this->columnDefs->addNumbering($column, $this->primaryKey);
        return $this;
    }


    /**
     * Add extra column
     *
     * @param String $column
     * @param \Closure $callback
     * @param String|int $position
     */
    public function add($column, $callback, $position = 'last')
    {
        $this->columnDefs->add($column, $callback, $position, $this->primaryKey);
        return $this;
    }


    /**
     * Edit column
     *
     * @param String $column
     * @param \Closure $callback
     */
    public function edit($column, $callback)
    {
        $this->columnDefs->edit($column, $callback);
        return $this;
    }

    /**
     * Format column
     *
     * @param String|Array $column
     * @param \Closure $callback
     */
    public function format($column, $callback)
    {
        if (is_array($column)) {
            foreach ($column as $item) {
                $this->columnDefs->format($item, $callback);
            }
        } else
            $this->columnDefs->format($column, $callback);

        return $this;
    }


    /**
     * Hide column
     *
     * @param String $column
     */
    public function hide($column)
    {
        $this->columnDefs->remove($column);
        return $this;
    }


    /**
     * Set Searchable columns
     * @param String|Array
     */
    public function setSearchableColumns($columns)
    {
        $this->columnDefs->setSearchable($columns);
        return $this;
    }


    /**
     * Add Searchable columns
     * @param String|Array
     */
    public function addSearchableColumns($columns)
    {
        $this->columnDefs->addSearchable($columns);
        return $this;
    }


    /**
     * Return the result of the DataTable
     *
     */
    public function getResult($returnAsObject = false)
    {
        if (Request::get('draw'))
            return $this->handleDrawRequest($returnAsObject);

        else if (Request::get('action'))
            return $this->handleActionRequest($returnAsObject);

        return self::handleError('no datatable request detected');
    }


    private function handleDrawRequest(bool $returnAsObject)
    {
        if ($returnAsObject !== NULL)
            $this->columnDefs->returnAsObject($returnAsObject);

        $this->query->setColumnDefs($this->columnDefs);

        return [
            'draw'              => Request::get('draw'),
            'recordsTotal'      => $this->query->countAll(),
            'recordsFiltered'   => $this->query->countFiltered(),
            'data'              => $this->query->getDataResult(),
        ];
    }


    private function handleActionRequest(bool $returnAsObject)
    {
        if ($returnAsObject !== NULL)
            $this->columnDefs->returnAsObject($returnAsObject);

        $this->query->setColumnDefs($this->columnDefs);

        $data = Request::get('data');

        switch (Request::get('action')) {
            case 'create':
                return [
                    'data' => $this->query->insertData($data)
                ];

            case 'edit':
                return [
                    'data' => $this->query->updateData($data, $this->primaryKey)
                ];

            case 'remove':
                return [
                    'data' => $this->query->deleteData($data, $this->primaryKey)
                ];

            default:
                return self::handleError('no datatable request detected');
        }
    }


    public static function handleError(string $message)
    {
        return [
            'data' => [], // Empty data to prevent error on client side.
            'error' => $message,
        ];
    }
}   // End of DataTables Library Class.
