<?php
namespace LaravelRocket\Generator\Objects;

class Column
{
    protected $uneditables     = ['id', 'remember_token', 'created_at', 'deleted_at', 'updated_at'];
    protected $unlistables     = ['id', 'remember_token', 'created_at', 'updated_at', 'password', 'deleted_at'];
    protected $unlistableTypes = ['text', 'mediumtext', 'longtext'];

    /** @var \TakaakiMizuno\MWBParser\Elements\Column */
    protected $column;

    /** @var \TakaakiMizuno\MWBParser\Elements\Column */
    public function __construct($column)
    {
        $this->column = $column;
    }

    public function getName()
    {
        return $this->column->getName();
    }

    /**
     * @return bool
     */
    public function isEditable(): bool
    {
        return !in_array($this->column->getName(), $this->uneditables);
    }

    /**
     * @return bool
     */
    public function isListable(): bool
    {
        if (in_array($this->column->getName(), $this->unlistables)) {
            return false;
        }

        if (in_array($this->column->getType(), $this->unlistableTypes)) {
            return false;
        }

        return true;
    }

    public function isQueryable(): bool
    {
        if (ends_with($this->column->getName(), 'name')) {
            return true;
        }

        return false;
    }

    /**
     * @param $relations
     * @param array $definitions
     *
     * @return array
     */
    public function getEditFieldType($relations, $definitions): array
    {
        $name = $this->column->getName();

        $type = strtolower(array_get($definitions, 'type', $this->column->getType()));

        if (starts_with($type, 'bool') || (starts_with($name, 'is_') || starts_with($name, 'has_')) && ($type === 'int' || $type === 'tinyint')) {
            return ['boolean', []];
        }

        if (ends_with($name, 'image_id') && ($type === 'int' || $type === 'bigint')) {
            return ['image', null];
        }

        if (ends_with($name, 'file_id') && ($type === 'int' || $type === 'bigint')) {
            return ['file', null];
        }

        if (ends_with($name, 'type') || $type === 'type') {
            return ['select', array_get($definitions, 'options', [])];
        }

        if ($name === 'password') {
            return ['password', null];
        }

        if (ends_with($name, 'country_code') && $type === 'varchar') {
            return ['country', null];
        }

        if (ends_with($name, 'gender') && $type === 'varchar') {
            return ['select', array_get($definitions, 'options', [[
                'name'  => 'Male',
                'value' => 'male',
            ], [
                'name'  => 'Female',
                'value' => 'female',
            ]])];
        }

        if (in_array($type, ['text', 'mediumtext', 'longtext', 'smalltext', 'tinytext'])) {
            return ['textarea', null];
        }

        return ['text', null];
    }
}
