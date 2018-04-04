<?php
namespace LaravelRocket\Generator\Objects;

use function ICanBoogie\singularize;

class Relation
{
    protected $uneditables = ['passwordResets', 'notifications'];
    protected $unlistables = ['passwordResets', 'notifications'];
    protected $unshowables = ['passwordResets', 'notifications'];

    protected $name = '';
    protected $type = '';

    /**
     * @var null|\TakaakiMizuno\MWBParser\Elements\Column
     */
    protected $column = null;
    /**
     * @var null|\TakaakiMizuno\MWBParser\Elements\Column
     */
    protected $referenceColumn = null;

    protected $tableName             = '';
    protected $referenceTableName    = '';
    protected $intermediateTableName = '';

    /**
     * @var string
     */
    protected $editFieldType = 'string';

    /**
     * @var array
     */
    protected $editFieldOptions = [];

    const TYPE_BELONGS_TO      = 'belongsTo';
    const TYPE_HAS_MANY        = 'hasMany';
    const TYPE_HAS_ONE         = 'hasOne';
    const TYPE_BELONGS_TO_MANY = 'belongsToMany';

    const EDIT_TYPE_CHECKBOX      = 'checkbox';
    const EDIT_TYPE_RADIO_BUTTON  = 'radio_button';
    const EDIT_TYPE_SELECT_SINGLE = 'select_single';
    const EDIT_TYPE_SELECT_MULTI  = 'select_multi';

    /**
     * Relation constructor.
     *
     * @param string                                   $type
     * @param string                                   $tableName
     * @param \TakaakiMizuno\MWBParser\Elements\Column $column
     * @param string                                   $referenceTableName
     * @param \TakaakiMizuno\MWBParser\Elements\Column $referenceColumn
     * @param string                                   $intermediateTableName
     */
    public function __construct($type, $tableName, $column, $referenceTableName, $referenceColumn, $intermediateTableName = '')
    {
        $this->type                  = $type;
        $this->tableName             = $tableName;
        $this->column                = $column;
        $this->referenceTableName    = $referenceTableName;
        $this->referenceColumn       = $referenceColumn;
        $this->intermediateTableName = $intermediateTableName;

        $this->editFieldType = $this->detectEditType();

        $this->setName();
    }

    protected function setName()
    {
        switch ($this->type) {
            case self::TYPE_BELONGS_TO:
                $this->name = camel_case(preg_replace('/_id$/', '', $this->column->getName()));
                break;
            case self::TYPE_HAS_MANY:
                $this->name = camel_case($this->referenceTableName);
                break;
            case self::TYPE_HAS_ONE:
                $this->name = camel_case(singularize($this->referenceTableName));
                break;
            case self::TYPE_BELONGS_TO_MANY:
                $this->name = camel_case($this->referenceTableName);
                break;
        }

        $this->name = camel_case($this->referenceTableName);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return string
     */
    public function getReferenceTableName()
    {
        return $this->referenceTableName;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return title_case(str_replace('_', ' ', snake_case($this->getName())));
    }

    /**
     * @return null|\TakaakiMizuno\MWBParser\Elements\Column
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return null|\TakaakiMizuno\MWBParser\Elements\Column
     */
    public function getReferenceColumn()
    {
        return $this->referenceColumn;
    }

    /**
     * @return bool
     */
    public function isRoles()
    {
        return $this->getType() === self::TYPE_HAS_MANY && $this->hasPostFix($this->getName(), 'roles');
    }

    /**
     * @return bool
     */
    public function isTypes()
    {
        return $this->getType() === self::TYPE_HAS_MANY && $this->hasPostFix($this->getName(), 'types');
    }

    /**
     * @return bool
     */
    public function isEditable(): bool
    {
        return !$this->hasPostFix($this->getName(), $this->uneditables) && ($this->getType() === self::TYPE_BELONGS_TO || $this->isRoles() || $this->isTypes());
    }

    /**
     * @return bool
     */
    public function isListable(): bool
    {
        return !$this->hasPostFix($this->getName(), $this->unlistables) && ($this->getType() === self::TYPE_BELONGS_TO || $this->isRoles() || $this->isTypes());
    }

    /**
     * @return bool
     */
    public function isShowable(): bool
    {
        if (!$this->hasPostFix($this->getName(), $this->unshowables)) {
            return false;
        }

        return !$this->hasPostFix($this->getName(), $this->unshowables) && ($this->getType() === self::TYPE_BELONGS_TO || $this->isRoles() || $this->isTypes());
    }

    /**
     * @return bool
     */
    public function shouldIncludeInAPI(): bool
    {
        if ($this->hasPostFix($this->getName(), $this->unshowables)) {
            return false;
        }

        return $this->isEditable() || $this->isShowable() || $this->isListable();
    }

    /**
     * @return bool
     */
    public function isImage()
    {
        return $this->hasPostFix($this->getName(), 'image');
    }

    /**
     * @return bool
     */
    public function isFile()
    {
        return $this->hasPostFix($this->getName(), ['file', 'image']);
    }

    /**
     * @return string
     */
    public function getViewName()
    {
        return title_case(str_replace('_', ' ', snake_case($this->name)));
    }

    /**
     * @return string
     */
    public function getReferenceModel()
    {
        return ucfirst(camel_case(singularize($this->referenceTableName)));
    }

    /**
     * @return string
     */
    public function getIntermediateTableName()
    {
        return $this->intermediateTableName;
    }

    /**
     * @return bool
     */
    public function isMultipleSelection()
    {
        return $this->type === self::TYPE_HAS_MANY || $this->type === self::TYPE_BELONGS_TO_MANY;
    }

    /**
     * @return string
     */
    public function getEditFieldType()
    {
        return $this->editFieldType;
    }

    /**
     * @return array
     */
    public function getEditFieldOptions()
    {
        return $this->editFieldOptions;
    }

    /**
     * @return string
     */
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

    /**
     * @param string       $haystack
     * @param array|string $needles
     *
     * @return bool
     */
    protected function hasPostFix($haystack, $needles)
    {
        if (!is_array($needles)) {
            $needles = [$needles];
        }

        foreach ($needles as $needle) {
            if ($haystack === $needle || ends_with($haystack, ucfirst($needle))) {
                return true;
            }
        }

        return false;
    }
}
