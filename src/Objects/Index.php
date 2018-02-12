<?php
namespace LaravelRocket\Generator\Objects;

class Index
{
    /** @var \TakaakiMizuno\MWBParser\Elements\Index|\Doctrine\DBAL\Schema\Index */
    protected $index;

    /** @var \TakaakiMizuno\MWBParser\Elements\Column|\Doctrine\DBAL\Schema\Index */
    public function __construct($index)
    {
        $this->index = $index;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->index->getName();
    }

    /**
     * @return bool
     */
    public function isPrimary()
    {
        return $this->index->isPrimary();
    }

    /**
     * @return string
     */
    public function generateAddMigration()
    {
        $index   = $this->index;
        $type    = $index->isUnique() ? 'unique' : 'index';
        $names   = array_map(function ($column) {
            return $column->getName();
        }, $index->getColumns());
        $columns = '\''.implode('\',\'', $names).'\'';
        $line    = '$table->'.$type.'(['.$columns.'], \''.$this->getName().'\')';

        return $line;
    }

    /**
     * @return string
     */
    public function generateDropMigration()
    {
        $line = '$table->dropIndex(\''.$this->getName().'\')';

        return $line;
    }
}
