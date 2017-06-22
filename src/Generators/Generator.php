<?php
namespace LaravelRocket\Generator\Generators;

use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Factory as ViewFactory;

abstract class Generator
{
    /** @var \Illuminate\Config\Repository */
    protected $config;

    /** @var \Illuminate\Filesystem\Filesystem */
    protected $files;

    /** @var \Illuminate\View\Factory */
    protected $view;

    /** @var string */
    protected $errorString;

    /** @var bool */
    protected $overwrite;

    /**
     * @param \Illuminate\Config\Repository     $config
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param \Illuminate\View\Factory          $view
     */
    public function __construct(
        ConfigRepository $config,
        Filesystem $files,
        ViewFactory $view
    ) {
        $this->config = $config;
        $this->files  = $files;
        $this->view   = $view;
    }

    /**
     * @param string      $name
     * @param bool        $overwrite
     * @param string|null $baseDirectory
     */
    abstract public function generate($name, $overwrite = false, $baseDirectory = null);

    /**
     * @param bool $overwrite
     */
    public function setOverwrite($overwrite)
    {
        $this->overwrite = $overwrite;
    }

    public function shouldOverwrite()
    {
        return $this->overwrite;
    }

    /**
     * @param array  $data
     * @param string $stubPath
     *
     * @return string
     */
    protected function replace($data, $stubPath)
    {
        $stub = $this->files->get($stubPath);

        foreach ($data as $key => $value) {
            $templateKey = '%%'.$key.'%%';
            $stub        = str_replace($templateKey, $value, $stub);
        }

        return $stub;
    }

    /**
     * @param array  $data
     * @param string $filePath
     *
     * @return bool
     */
    protected function replaceFile($data, $filePath)
    {
        if (!$this->files->exists($filePath)) {
            return false;
        }
        $content = $this->files->get($filePath);
        foreach ($data as $key => $value) {
            $content = str_replace($key, $value.$key, $content);
        }
        $this->files->put($filePath, $content);

        return true;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
    }

    protected function getClassName($name)
    {
        $names = array_slice(explode('\\', $name), -1, 1);

        return count($names) ? $names[0] : $name;
    }

    /**
     * @param string $modelName
     *
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    protected function getFillableColumns($modelName)
    {
        $ret       = [];
        $tableName = $this->getTableName($modelName);

        $fillableNames = [];
        $modelFullName = '\\App\\Models\\'.$modelName;
        $classExists   = class_exists($modelFullName);

        $columns = $this->getTableColumns($tableName);
        if ($classExists) {
            /** @var \LaravelRocket\Foundation\Models\Base $modelObject */
            $modelObject = new $modelFullName();
            if (!empty($modelObject)) {
                $fillableNames = $modelObject->getFillableColumns();
            }
        } else {
            if ($columns) {
                foreach ($columns as $column) {
                    $columnName = $column->getName();
                    if (!in_array($columnName, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                        $fillableNames[] = $columnName;
                    }
                }
            }
        }

        if ($columns) {
            foreach ($columns as $column) {
                if ($column->getAutoincrement()) {
                    continue;
                }
                $columnName = $column->getName();

                if (in_array($columnName, $fillableNames)) {
                    $ret[] = $column;
                }
            }
        }

        return $ret;
    }

    /**
     * @param string $modelName
     *
     * @return string
     */
    protected function getTableName($modelName)
    {
        $modelName     = $this->getModelName($modelName);
        $modelFullName = '\\App\\Models\\'.$modelName;

        $classExists = class_exists($modelFullName);
        if ($classExists) {
            return $modelFullName::getTableName();
        } else {
            $name    = \StringHelper::pluralize(\StringHelper::camel2Snake($modelName));
            $columns = $this->getTableColumns($name);
            if (count($columns)) {
                return $name;
            }

            $name    = \StringHelper::singularize(\StringHelper::camel2Snake($modelName));
            $columns = $this->getTableColumns($name);
            if (count($columns)) {
                return $name;
            }

            return \StringHelper::pluralize(\StringHelper::camel2Snake($modelName));
        }
    }

    /**
     * @param string $tableName
     *
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    protected function getTableColumns($tableName)
    {
        $hasDoctrine = interface_exists('Doctrine\DBAL\Driver');
        if (!$hasDoctrine) {
            return [];
        }

        $platform = \DB::getDoctrineConnection()->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('json', 'string');

        $schema = \DB::getDoctrineSchemaManager();

        $columns = $schema->listTableColumns($tableName);

        return $columns;
    }

    /**
     * @param \Doctrine\DBAL\Schema\Column[] $columns
     *
     * @return string[]
     */
    protected function getColumnNames($columns)
    {
        $result = [];
        foreach ($columns as $column) {
            $result[] = $column->getName();
        }

        return $result;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    protected function alreadyExists($path)
    {
        return $this->files->exists($path);
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param string $path
     *
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }

    /**
     * @param string $class
     *
     * @return string
     */
    protected function convertClassToPath($class)
    {
        return base_path(str_replace('\\', '/', $class).'.php');
    }

    /**
     * @param string $string
     */
    protected function error($string)
    {
        $this->errorString = $string;
    }

    /**
     * @param string $modelName
     * @param string $classPath
     * @param string $stubFilePath
     * @param array  $additionalData
     *
     * @return bool
     */
    protected function generateFile($modelName, $classPath, $stubFilePath, $additionalData = [])
    {
        if ($this->alreadyExists($classPath)) {
            if ($this->shouldOverwrite()) {
                $this->files->delete($classPath);
            } else {
                $this->error($classPath.' already exists.');

                return false;
            }
        }

        $pathInfo  = pathinfo($classPath);
        $className = $pathInfo['filename'];

        $this->makeDirectory($classPath);

        $defaultData = [
            'MODEL' => $modelName,
            'model' => lcfirst($modelName),
            'CLASS' => $className,
            'class' => lcfirst($className),

        ];
        $data = $additionalData;
        foreach ($defaultData as $key => $value) {
            if (!array_key_exists($key, $data)) {
                $data[$key] = $value;
            }
        }

        $content = $this->replace($data, $stubFilePath);

        if (empty($content)) {
            return false;
        }

        $this->files->put($classPath, $content);

        return true;
    }

    protected function getStubPath($path)
    {
        $stubFilePath = resource_path('stubs'.$path);

        if ($this->files->exists($stubFilePath)) {
            return $stubFilePath;
        }

        $stubFilePath = __DIR__.'/../../stubs'.$path;

        return $stubFilePath;
    }
}
