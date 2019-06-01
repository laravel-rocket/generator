<?php
namespace LaravelRocket\Generator\Objects;

use Illuminate\Support\Str;
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
    const EDIT_TYPE_IMAGE         = 'image';
    const EDIT_TYPE_FILE          = 'file';

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

        $this->setName();
        $this->editFieldType = $this->detectEditType();
    }

    protected function setName()
    {
        switch ($this->type) {
            case self::TYPE_BELONGS_TO:
                $this->name = Str::camel(preg_replace('/_id$/', '', $this->column->getName()));

                return;
            case self::TYPE_HAS_MANY:
                $this->name = Str::camel($this->referenceTableName);

                return;
            case self::TYPE_HAS_ONE:
                $this->name = Str::camel(singularize($this->referenceTableName));

                return;
            case self::TYPE_BELONGS_TO_MANY:
                $this->name = Str::camel($this->referenceTableName);

                return;
        }

        $this->name = Str::camel($this->referenceTableName);
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
        return Str::title(str_replace('_', ' ', Str::snake($this->getName())));
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
        if ($this->hasPostFix($this->getName(), $this->uneditables)) {
            return false;
        }

        if ($this->getType() === self::TYPE_BELONGS_TO) {
            return false;
        }

        return $this->isRoles() || $this->isTypes();
    }

    /**
     * @return bool
     */
    public function isListable(): bool
    {
        return !$this->hasPostFix($this->getName(), $this->unlistables)
            && ($this->getType() === self::TYPE_BELONGS_TO || $this->isRoles() || $this->isTypes());
    }

    /**
     * @return bool
     */
    public function isShowable(): bool
    {
        return !$this->hasPostFix($this->getName(), $this->unshowables)
            && ($this->getType() === self::TYPE_BELONGS_TO || $this->isRoles() || $this->isTypes());
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
        return Str::title(str_replace('_', ' ', Str::snake($this->name)));
    }

    /**
     * @return string
     */
    public function getReferenceModel()
    {
        return ucfirst(Str::camel(singularize($this->referenceTableName)));
    }

    /**
     * @return string
     */
    public function getIntermediateTableName()
    {
        return $this->intermediateTableName;
    }

    /**
     * @return string
     */
    public function getIntermediateTableModel()
    {
        return ucfirst(Str::camel(singularize($this->intermediateTableName)));
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
            if (Str::endsWith($this->name, '_type') || Str::endsWith($this->name, 'role')) {
                return self::EDIT_TYPE_CHECKBOX;
            }

            return self::EDIT_TYPE_SELECT_MULTI;
        }

        if (Str::endsWith($this->name, '_type') || Str::endsWith($this->name, 'role')) {
            return self::EDIT_TYPE_RADIO_BUTTON;
        }

        if ($this->isImage()) {
            return self::EDIT_TYPE_IMAGE;
        }

        if ($this->isFile()) {
            return self::EDIT_TYPE_FILE;
        }

        return self::EDIT_TYPE_SELECT_SINGLE;
    }

    /**
     * @return string
     */
    public function getAPIName()
    {
        return Str::camel($this->getName());
    }

    /**
     * @return string
     */
    public function getQueryName()
    {
        return Str::snake($this->getName());
    }

    /**
     * @return string
     */
    public function getInterestedColumnName()
    {
        if ($this->isRoles()) {
            return 'role';
        }

        if ($this->isTypes()) {
            return 'type';
        }

        return 'id';
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
            if ($haystack === $needle || Str::endsWith($haystack, ucfirst($needle))) {
                return true;
            }
        }

        return false;
    }
}
