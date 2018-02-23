<?php
namespace LaravelRocket\Generator\Objects;

class Column
{
    protected $uneditables     = ['id', 'remember_token', 'created_at', 'deleted_at', 'updated_at'];
    protected $unlistables     = ['id', 'remember_token', 'created_at', 'updated_at', 'password', 'deleted_at'];
    protected $unlistableTypes = ['text', 'mediumtext', 'longtext'];
    protected $unshowables     = ['remember_token', 'password'];

    /** @var \TakaakiMizuno\MWBParser\Elements\Column|\Doctrine\DBAL\Schema\Column */
    protected $column;

    /** @var \TakaakiMizuno\MWBParser\Elements\Column|\Doctrine\DBAL\Schema\Column */
    public function __construct($column)
    {
        $this->column = $column;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->column->getName();
    }

    /**
     * @return string
     */
    public function getType()
    {
        $type = $this->column->getType();

        if (get_class($this->column) == \Doctrine\DBAL\Schema\Column::class || !is_string($type)) {
            return $type->getName();
        }

        return $type;
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
        if (in_array($this->getName(), $this->unlistables)) {
            return false;
        }

        if (in_array($this->getType(), $this->unlistableTypes)) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isShowable(): bool
    {
        return !in_array($this->column->getName(), $this->unshowables);
    }

    /**
     * @return bool
     */
    public function isQueryable(): bool
    {
        if (ends_with($this->getName(), 'name')) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isNullable(): bool
    {
        if (get_class($this->column) == \Doctrine\DBAL\Schema\Column::class) {
            return !$this->column->getNotnull();
        }

        return $this->column->isNullable();
    }

    /**
     * @return mixed|null|string
     */
    public function getDefaultValue()
    {
        if (get_class($this->column) == \Doctrine\DBAL\Schema\Column::class) {
            return $this->column->getDefault();
        }

        return $this->column->getDefaultValue();
    }

    /**
     * @param $relations
     * @param array $definitions
     *
     * @return array
     */
    public function getEditFieldType($relations, $definitions): array
    {
        $name = $this->getName();

        $type = strtolower(array_get($definitions, 'type', $this->getType()));

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

        if (ends_with($name, 'currency_code') && $type === 'varchar') {
            return ['currency', null];
        }

        if ($type === 'date') {
            return ['date', null];
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

    /**
     * @param string|null $previousColumnName
     *
     * @return string
     */
    public function generateAddMigration($previousColumnName = null)
    {
        $column  = $this->column;
        $postfix = '';
        switch ($this->getType()) {
            case 'tinyint':
                $type = 'boolean';
                break;
            case 'bigint':
                $type = $column->isUnsigned() ? 'unsignedBigInteger' : 'bigInteger';
                break;
            case 'int':
                $type = $column->isUnsigned() ? 'unsignedInteger' : 'integer';
                break;
            case 'timestamp':
            case 'timestamp_f':
                $type = 'timestamp';
                break;
            case 'date':
                $type = 'date';
                break;
            case 'varchar':
            case 'string':
                $type = 'string';
                if ($column->getLength() != 255) {
                    $postfix = ', '.$column->getLength();
                }
                break;
            case 'text':
                $type = 'text';
                break;
            case 'mediumtext':
                $type = 'mediumtext';
                break;
            case 'longtext':
                $type = 'longtext';
                break;
            case 'decimal':
                $type    = 'decimal';
                $postfix = ', '.$column->getPrecision().', '.$column->getScale();
                break;
            default:
                $type = 'unknown';
        }
        $line = '$table->'.$type.'(\''.$this->getName().'\''.$postfix.')';

        if ($this->isNullable()) {
            $line .= '->nullable()';
        }
        if (!is_null($this->getDefaultValue()) && $this->getDefaultValue() !== '') {
            $defaultValue = $this->getDefaultValue();
            if ($defaultValue == "''") {
                $defaultValue = '';
            }
            switch ($this->getType()) {
                case 'tinyint':
                    $defaultValue = (int) $defaultValue == 1 ? 'true' : 'false';
                    $line .= '->default('.$defaultValue.')';
                    break;
                case 'bigint':
                case 'int':
                    $line .= '->default('.((int) $defaultValue).')';
                    break;
                case 'timestamp':
                case 'timestamp_f':
                case 'date':
                case 'varchar':
                case 'text':
                case 'mediumtext':
                case 'longtext':
                    $line .= '->default(\''.$defaultValue.'\')';
                    break;
                case 'decimal':
                    $line .= '->default('.((float) $defaultValue).')';
                    break;
                default:
                    $line .= '->default(\''.$defaultValue.'\')';
                    break;
            }
        }
        if (!empty($previousColumnName)) {
            $line .= '->after(\''.$previousColumnName.'\')';
        }

        return $line;
    }

    /**
     * @return string
     */
    public function generateDropMigration()
    {
        $line = '$table->dropColumn(\''.$this->column->getName().'\')';

        return $line;
    }
}
