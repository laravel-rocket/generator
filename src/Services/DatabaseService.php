<?php
namespace LaravelRocket\Generator\Services;

class DatabaseService
{
    /** @var \Illuminate\Config\Repository */
    protected $config;

    /** @var \Illuminate\Filesystem\Filesystem */
    protected $files;

    /** @var string */
    protected $databaseName;

    /** @var \PDO */
    protected $pdo;

    /** @var \Illuminate\Database\Connection $connection */
    protected $connection;

    /**
     * @param \Illuminate\Config\Repository     $config
     * @param \Illuminate\Filesystem\Filesystem $files
     */
    public function __construct(
        \Illuminate\Config\Repository $config,
        \Illuminate\Filesystem\Filesystem $files
    ) {
        $this->config                 = $config;
        $this->files                  = $files;
        $this->setPDO(false);
    }

    /**
     * @param bool $setDatabase
     */
    protected function setPDO($setDatabase=true)
    {
        $setting = 'rocket';

        $driver   = config('database.connections.'.$setting.'.driver');
        $host     = config('database.connections.'.$setting.'.host');
        $port     = config('database.connections.'.$setting.'.port');
        $username = config('database.connections.'.$setting.'.username');
        $password = config('database.connections.'.$setting.'.password');
        $database = config('database.connections.'.$setting.'.database');

        if ($setDatabase) {
            $this->pdo          = new \PDO("{$driver}:host={$host};port={$port};dbname={$database}", $username, $password);
        } else {
            $this->pdo          = new \PDO("{$driver}:host={$host};port={$port}", $username, $password);
        }
        $this->databaseName = $database;
        $this->connection   = \DB::connection($setting);
    }

    /**
     * @return string
     */
    public function resetDatabase(): string
    {
        $this->pdo->query('DROP DATABASE IF EXISTS '.$this->databaseName);
        $this->pdo->query('CREATE DATABASE '.$this->databaseName);

        $this->migrate();

        return $this->databaseName;
    }

    public function migrate()
    {
        \Artisan::call('migrate', ['--database' => 'rocket']);
    }

    public function dropDatabase()
    {
        $this->pdo->query('DROP DATABASE IF EXISTS '.$this->databaseName);
    }

    /**
     * @param string $tableName
     *
     * @return bool
     */
    public function checkTableExists(string $tableName): bool
    {
        try {
            $result = $this->pdo->query("SELECT 1 FROM $tableName LIMIT 1");
        } catch (\Exception $e) {
            return false;
        }

        return $result !== false;
    }

    /**
     * @return array|\Doctrine\DBAL\Schema\AbstractSchemaManager
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getSchema()
    {
        $hasDoctrine = interface_exists('Doctrine\DBAL\Driver');
        if (!$hasDoctrine) {
            return [];
        }

        /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
        $platform = $this->connection->getDoctrineConnection()->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('json', 'string');

        /** @var \Doctrine\DBAL\Schema\AbstractSchemaManager $schema */
        $schema = $this->connection->getDoctrineSchemaManager();

        return $schema;
    }

    /**
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAllTables(): array
    {
        $schema = $this->getSchema();

        $names           = $schema->listTableNames();
        $tableExceptions = ['migrations'];

        $result = [];
        foreach ($names as $name) {
            if (!in_array($name, $tableExceptions)) {
                $result[] = $name;
            }
        }

        return $result;
    }

    /**
     * @param string $tableName
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getTableColumns($tableName): array
    {
        $schema    = $this->getSchema();
        $columns   = $schema->listTableColumns($tableName);

        return $columns;
    }
}
