<?php

namespace App\Services;

use DB;
use Carbon\Carbon;

abstract class BaseService
{
    /**
     * @var Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * @var string
     * 
     * TODO: confirm if eloquent $primaryKey change can serve
     * same purpose of this without affecting relations retrieval
     * 
     * TODO: Consder moving these properties settings to db
     * or app config, also consider creating a BaseClass or 
     * trait for these properties
     */
    protected $identifier = 'id';

    /**
     * Pagination count
     * 
     * @var int
     */
    protected $paginate = 10;

    /**
     * Sort direction
     * 
     * @var string
     */
    protected $orderDirection = 'asc';

    /**
     * Sort condition
     * 
     * @var string
     */
    protected $orderBy = 'created_at';

    /**
     * Fetch a list of existing models 
     * @param bool $chainable = false
     * 
     * @return Model[];
     */
    public function all(bool $chainable = false)
    {
        $this->model = $this->model->all();

        return $chainable ? $this->model : $this->model;
    }

    /**
     * Fetch a list of existing models 
     * @param bool $chainable = false
     * 
     * @return Model[];
     */
    public function list(bool $chainable = false)
    {
        $this->model = $this->model->all();

        return $chainable ? $this->model : $this->model;
    }

    /**
     * Creates a new model
     * 
     * @param array  $payload
     * @param bool $chainable = false
     * 
     * @return Model
     */
    public function create(array $payload, bool $chainable = false)
    {
        $this->model = $this->model->create( $payload );

        return $chainable ? $this->model : $this->model;
    }

    /**
     * Modifies an existing model
     * 
     * @param int $id
     * @param array $payload
     * @param bool $chainable = false
     * 
     * @return Model
     */
    public function edit(int $id, array $payload, bool $chainable = false)
    {
        $this->model = $this->model->where([$this->identifier => $id])->firstOrFail();
        $this->model->update($payload);

        return $chainable ? $this->model : $this->model;
    }

    /**
     * Deletes a single specified model
     * Only if a model is editable
     * 
     * @param int $id
     * @param bool $chainable = false
     * 
     * @return Model
     */
    public function delete(int $id, bool $chainable = false)
    {
        $this->model = $this->model->where($this->identifier, $id)->firstOrFail();
        $this->model->delete();

        return $chainable ? $this->model : $this->model;
    }

    /**
     * Find a model by id
     * 
     * @param int $id
     * @param bool $chainable = false
     * 
     * @return Model
     */
    public function find( int $id, bool $chainable = false)
    {
        $this->model = $this->model->where($this->identifier, $id);

        return $chainable ? $this->model : $this->model->firstOrFail();
    }

    /**
     * Find a model by columnName
     * 
     * @param string $columnName
     * @param mixed $value
     * @param bool $chainable = false
     * 
     * @return Model
     */
    public function findByColumn( string $columnName, $value, bool $chainable = false)
    {
        $this->model = $this->model->where($columnName, $value);

        return $chainable ? $this->model : $this->model->first();
    }

    /**
     * Find a model by multiple columnName
     * 
     * @param array $columnNames
     * @param mixed $value
     * @param bool $chainable = false
     * 
     * @return Model
     */
    public function findByColumns( array $columnNames, $value, bool $chainable = false)
    {
        $this->model = $this->model->whereColumns($columnNames, $value);

        return $chainable ? $this->model : $this->model->first();
    }

    /**
     * Order model instance
     * 
     * @param string $column = null
     * @param string $direction = null
     * @param bool $chainable = false
     * 
     * @return Model
     */
    public function orderBy(string $column = null, string $direction = null, bool $chainable = false)
    {
        $this->model = $this->model->orderBy( 
            $direction ?? $this->orderBy,
            $column ?? $this->orderDirection
        );

        return $chainable ? $this->model : $this->model->get();
    }

    public function paginate()
    {
        
    }

    public function cache()
    {

    }

    // where, whereIn, latest, inRandomOrder, skip, take
}