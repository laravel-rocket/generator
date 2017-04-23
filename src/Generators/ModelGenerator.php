<?php
namespace LaravelRocket\Generator\Generators;

class ModelGenerator extends Generator
{
    public function generate($name, $overwrite = false, $baseDirectory = null)
    {
        $modelName = $this->getModelName($name);
        $this->generateModel($modelName);
        $this->generatePresenter($modelName);
        $this->generateModelUnitTest($modelName);
        $this->generateModelFactory($modelName);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getModelName($name)
    {
        $className = $this->getClassName($name);

        return $className;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getModelClass($name)
    {
        $modelName = $this->getModelName($name);

        return '\\App\\Models\\'.$modelName;
    }

    /**
     * @param string $modelName
     *
     * @return string
     */
    protected function getDateTimeColumns($modelName)
    {
        $tableName = $this->getTableName($modelName);
        $ret       = [];
        $columns   = $this->getTableColumns($tableName);
        if ($columns) {
            foreach ($columns as $column) {
                if (!in_array($column->getType()->getName(), ['datetime', 'date', 'time'])) {
                    continue;
                }
                $columnName = $column->getName();
                if (!in_array($columnName, ['created_at', 'updated_at'])) {
                    $ret[] = $columnName;
                }
            }
        }

        $datetimes = count($ret) > 0 ? "'".implode("','", $ret)."'" : '';

        return $datetimes;
    }

    /**
     * @param string $modelName
     *
     * @return bool
     */
    protected function hasSoftDeleteColumn($modelName)
    {
        $tableName = $this->getTableName($modelName);
        $columns   = $this->getTableColumns($tableName);
        if ($columns) {
            foreach ($columns as $column) {
                $columnName = $column->getName();
                if ($columnName == 'deleted_at') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    protected function getTableList()
    {
        $hasDoctrine = interface_exists('Doctrine\DBAL\Driver');
        if (!$hasDoctrine) {
            return [];
        }

        $tables = \DB::getDoctrineSchemaManager()->listTables();
        $ret    = [];
        foreach ($tables as $table) {
            $ret[] = $table->getName();
        }

        return $ret;
    }

    /**
     * @param string $modelName
     *
     * @return bool
     */
    protected function generateModel($modelName)
    {
        $className = $this->getModelClass($modelName);
        $classPath = $this->convertClassToPath($className);

        $stubFilePath = $this->getStubPath('/model/model.stub');

        $tableName = $this->getTableName($modelName);
        $columns   = $this->getFillableColumns($modelName);

        $fillables = count($columns) > 0 ? "'".implode("',".PHP_EOL."        '",
                $this->getColumnNames($columns))."'," : '';

        $hasSoftDelete = $this->hasSoftDeleteColumn($tableName);

        return $this->generateFile($modelName, $classPath, $stubFilePath, [
            'TABLE'                 => $tableName,
            'FILLABLES'             => $fillables,
            'SOFT_DELETE_CLASS_USE' => $hasSoftDelete ? 'use Illuminate\Database\Eloquent\SoftDeletes;'.PHP_EOL : '',
            'SOFT_DELETE_USE'       => $hasSoftDelete ? 'use SoftDeletes;'.PHP_EOL : '',
            'DATETIMES'             => $this->getDateTimeColumns($modelName),
            'RELATIONS'             => $this->generateModelRelation($modelName),
        ]);
    }

    protected function getPath($name)
    {
        $className = $this->getClassName($name);

        return app_path('/Models/'.$className.'.php');
    }

    protected function generateModelRelation($modelName)
    {
        $relations = '';
        $tables    = $this->getTableList();

        $tableName = $this->getTableName($modelName);
        $columns   = $this->getFillableColumns($tableName);

        foreach ($columns as $column) {
            $columnName = $column->getName();
            if (preg_match('/^(.*_image)_id$/', $columnName, $matches)) {
                $relationName = \StringHelper::snake2Camel($matches[1]);
                $relations .= '    public function '.$relationName.'()'.PHP_EOL.'    {'.PHP_EOL.'        return $this->hasOne(\App\Models\Image::class, \'id\', \''.$columnName.'\');'.PHP_EOL.'    }'.PHP_EOL.PHP_EOL;
            } elseif (preg_match('/^(.*)_id$/', $columnName, $matches)) {
                $relationName = \StringHelper::snake2Camel($matches[1]);
                $className    = ucfirst($relationName);
                if (!$this->getPath($className)) {
                    continue;
                }
                $relations .= '    public function '.$relationName.'()'.PHP_EOL.'    {'.PHP_EOL.'        return $this->belongsTo(\App\Models\\'.$className.'::class, \''.$columnName.'\', \'id\');'.PHP_EOL.'    }'.PHP_EOL.PHP_EOL;
            }
        }

        return $relations;
    }

    /**
     * @param string $modelName
     *
     * @return bool
     */
    protected function generatePresenter($modelName)
    {
        $className    = '\\App\\Presenters\\'.$modelName.'Presenter';
        $classPath    = $this->convertClassToPath($className);
        $stubFilePath = __DIR__.'/../../stubs/model/presenter.stub';

        $tableName        = $this->getTableName($modelName);
        $columns          = $this->getFillableColumns($tableName);
        $multilingualKeys = [];
        foreach ($columns as $column) {
            if (preg_match('/^(.*)_en$/', $column->getName(), $matches)) {
                $multilingualKeys[] = $matches[1];
            }
        }
        $multilingualKeyString = count($multilingualKeys) > 0 ? "'".implode("','",
                array_unique($multilingualKeys))."'" : '';

        $imageFields = [];
        foreach ($columns as $column) {
            if (preg_match('/^(.*_image)_id$/', $column->getName(), $matches)) {
                $imageFields[] = $matches[1];
            }
        }
        $imageFieldString = count($imageFields) > 0 ? "'".implode("','", array_unique($imageFields))."'" : '';

        return $this->generateFile($modelName, $classPath, $stubFilePath, [
            'MULTILINGUAL_COLUMNS' => $multilingualKeyString,
            'IMAGE_COLUMNS'        => $imageFieldString,
        ]);
    }

    /**
     * @param string $modelName
     *
     * @return bool
     */
    protected function generateModelUnitTest($modelName)
    {
        $classPath    = base_path('/tests/Models/'.$modelName.'Test.php');
        $stubFilePath = $this->getStubPath('/model/model_unittest.stub');

        return $this->generateFile($modelName, $classPath, $stubFilePath);
    }

    /**
     * @param string $modelName
     *
     * @return bool
     */
    protected function generateModelFactory($modelName)
    {
        $className = $this->getModelClass($modelName);
        $tableName = $this->getTableName($modelName);

        $columns = $this->getFillableColumns($tableName);

        $factoryPath = base_path('/database/factories/ModelFactory.php');
        $key         = '/* NEW MODEL FACTORY */';

        $data = '$factory->define('.$className.'::class, function (Faker\Generator $faker) {'.PHP_EOL.'    return ['.PHP_EOL;
        foreach ($columns as $column) {
            $defaultValue = "''";
            switch ($column->getType()->getName()) {
                case 'bigint':
                case 'integer':
                case 'smallint':
                    $defaultValue = 0;
                    break;
                case 'string':
                case 'text':
                case 'binary':
                    $defaultValue = "''";
                    break;
                case 'datetime':
                    $defaultValue = '$faker->dateTime';
                    break;
                case 'boolean':
                    $defaultValue = '$faker->boolean';
                    break;
            }
            switch ($column->getName()) {
                case 'name':
                    $defaultValue = '$faker->name';
                    break;
                case 'email':
                    $defaultValue = '$faker->unique()->safeEmail';
                    break;
                case 'password':
                    $defaultValue = 'bcrypt(\'secret\')';
                    break;
            }
            $data .= "        '".$column->getName()."' => ".$defaultValue.','.PHP_EOL;
        }
        $data .= '    ];'.PHP_EOL.'});'.PHP_EOL.PHP_EOL;

        $this->replaceFile([
            $key => $data,
        ], $factoryPath);

        return true;
    }
}
