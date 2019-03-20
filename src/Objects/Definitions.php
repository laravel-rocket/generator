<?php
namespace LaravelRocket\Generator\Objects;

class Definitions
{
    /** @var array */
    protected $json;

    public function __construct($json)
    {
        $this->json = json_decode($json, true);
    }

    /**
     * @param string|array $key
     * @param mixed        $default
     *
     * @return mixed
     */
    public function get($key, $default)
    {
        if (is_array($key)) {
            $key = implode('.', $key);
        }

        return array_get($this->json, $key, $default);
    }

    /**
     * @param string $table
     * @param string $column
     *
     * @return array
     */
    public function getColumnDefinition($table, $column)
    {
        return $this->get(['tables', $table, 'columns', $column], []);
    }

    /**
     * @param string $table
     * @param string $type
     *
     * @return array
     */
    public function getTableCRUDDefinition($table, $type)
    {
        return $this->get(['admin','cruds','ables', $table, $type], []);
    }

    /**
     * @param string $table
     *
     * @return array
     */
    public function getTableDefinition($table)
    {
        return $this->get(['tables', $table], []);
    }
}
