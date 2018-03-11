<?php
namespace LaravelRocket\Generator\Objects;

use function ICanBoogie\singularize;

class Relation
{
    protected $name                  = '';
    protected $type                  = '';
    protected $column                = null;
    protected $referenceColumn       = null;
    protected $tableName             = '';
    protected $referenceTableName    = '';
    protected $intermediateTableName = '';

    const TYPE_BELONGS_TO      = 'belongsTo';
    const TYPE_HAS_MANY        = 'hasMany';
    const TYPE_HAS_ONE         = 'hasOne';
    const TYPE_BELONGS_TO_MANY = 'belongsToMany';

    const EDIT_TYPE_CHECKBOX      = 'checkbox';
    const EDIT_TYPE_RADIO_BUTTON  = 'radio_button';
    const EDIT_TYPE_SELECT_SINGLE = 'select_single';
    const EDIT_TYPE_SELECT_MULTI  = 'select_multi';

    public function __construct($name, $type, $tableName, $column, $referenceTableName, $referenceColumn, $intermediateTableName = '')
    {
        $this->name                  = $name;
        $this->type                  = $type;
        $this->tableName             = $tableName;
        $this->column                = $column;
        $this->referenceTableName    = $referenceTableName;
        $this->referenceColumn       = $referenceColumn;
        $this->intermediateTableName = $intermediateTableName;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function getColumn()
    {
        return $this->column;
    }

    public function isImage()
    {
        return ends_with(strtolower($this->name), 'image');
    }

    public function isFile()
    {
        return ends_with(strtolower($this->name), 'file') || ends_with(strtolower($this->name), 'image');
    }

    public function getViewName()
    {
        return title_case(str_replace('_', ' ', snake_case($this->name)));
    }

    public function getReferenceModel()
    {
        return ucfirst(camel_case(singularize($this->referenceTableName)));
    }

    public function getIntermediateTableName()
    {
        return $this->intermediateTableName;
    }

    public function isMultipleSelection()
    {
        return $this->type === self::TYPE_HAS_MANY || $this->type === self::TYPE_BELONGS_TO_MANY;
    }

    public function shouldIncludeInAPI()
    {
        if (!$this->isMultipleSelection()) {
            return true;
        }

        if (ends_with($this->name, '_type') || ends_with($this->name, 'role')) {
            return true;
        }

        return false;
    }

    public function detectEditType()
    {
        if ($this->isMultipleSelection()) {
            if (ends_with($this->name, '_type') || ends_with($this->name, 'role')) {
                return self::EDIT_TYPE_CHECKBOX;
            }

            return self::EDIT_TYPE_SELECT_MULTI;
        }

        if (ends_with($this->name, '_type') || ends_with($this->name, 'role')) {
            return self::EDIT_TYPE_RADIO_BUTTON;
        }

        return self::EDIT_TYPE_SELECT_SINGLE;
    }

    /**
     * @return string
     */
    public function getAPIName()
    {
        return camel_case($this->getName());
    }

    /**
     * @return string
     */
    public function getQueryName()
    {
        return snake_case($this->getName());
    }
}
