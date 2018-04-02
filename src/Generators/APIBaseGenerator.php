<?php
namespace LaravelRocket\Generator\Generators;

class APIBaseGenerator extends BaseGenerator
{
    /**
     * @var string
     */
    protected $versionNamespace = 'V1';

    /** @var string $name */
    protected $name;

    /**
     * @param \TakaakiMizuno\SwaggerParser\Objects\Base $object
     */
    protected $object;

    /**
     * @var \LaravelRocket\Generator\Objects\Table[]
     */
    protected $tables;

    /**
     * @param \TakaakiMizuno\SwaggerParser\Objects\V20\Document $osa
     */
    protected $osa;

    /**
     * @var \LaravelRocket\Generator\Objects\Definitions
     */
    protected $json;

    /** @var \LaravelRocket\Generator\Services\DatabaseService $database */
    protected $databaseService;

    /**
     * @param string                                            $name
     * @param \TakaakiMizuno\SwaggerParser\Objects\Base         $object
     * @param \TakaakiMizuno\SwaggerParser\Objects\V20\Document $osa
     * @param \LaravelRocket\Generator\Services\DatabaseService $databaseService
     * @param \LaravelRocket\Generator\Objects\Definitions      $json
     * @param \TakaakiMizuno\MWBParser\Elements\Table[]         $tables
     *
     * @return bool
     */
    public function generate($name, $object, $osa, $databaseService, $json, $tables): bool
    {
        $this->json            = $json;
        $this->tables          = $tables;
        $this->databaseService = $databaseService;

        $this->setTarget($name, $object, $osa);
        $this->setVersion();
        $this->preprocess();

        if (!$this->canGenerate()) {
            return false;
        }

        $variables = $this->getVariables();

        $view = $this->getView();
        $path = $this->getPath();

        if (file_exists($path)) {
            unlink($path);
        }
        $this->fileService->render($view, $path, $variables);

        return true;
    }

    /**
     * @param string                                            $name
     * @param \TakaakiMizuno\SwaggerParser\Objects\Base         $object
     * @param \TakaakiMizuno\SwaggerParser\Objects\V20\Document $osa
     */
    public function setTarget($name, $object, $osa)
    {
        $this->name   = $name;
        $this->object = $object;
        $this->osa    = $osa;
    }

    protected function getBasicVariables()
    {
        $data = [
            'name'             => $this->name,
            'versionNamespace' => $this->versionNamespace,
        ];

        return $data;
    }

    protected function preprocess()
    {
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        return [];
    }

    protected function setVersion()
    {
        $version   = $this->osa->info->version;
        $fragments = explode('.', $version);
        $major     = (int) $fragments[0];
        if ($major < 0) {
            $major = 1;
        }
        $this->versionNamespace = 'V'.$major;
    }
}
