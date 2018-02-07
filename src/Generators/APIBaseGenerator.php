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
     * @param \TakaakiMizuno\SwaggerParser\Objects\Base $definition
     */
    protected $definition;

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
     * @param \TakaakiMizuno\SwaggerParser\Objects\Base         $definition
     * @param \TakaakiMizuno\SwaggerParser\Objects\V20\Document $osa
     * @param \LaravelRocket\Generator\Services\DatabaseService $databaseService
     * @param \LaravelRocket\Generator\Objects\Definitions      $json
     *
     * @return bool
     */
    public function generate($name, $definition, $osa, $databaseService, $json): bool
    {
        $this->json            = $json;
        $this->databaseService = $databaseService;

        if (!$this->canGenerate()) {
            return false;
        }

        $this->setTarget($name, $definition, $osa);
        $this->setVersion();
        $this->preprocess();

        $variables = $this->getVariables();

        $view = $this->getView();
        $path = $this->getPath();

        if (file_exists($path)) {
            unlink($path);
        }
        $this->fileService->render($view, $path, $variables, true, true);

        return true;
    }

    /**
     * @param string                                            $name
     * @param \TakaakiMizuno\SwaggerParser\Objects\Base         $definition
     * @param \TakaakiMizuno\SwaggerParser\Objects\V20\Document $osa
     */
    public function setTarget($name, $definition, $osa)
    {
        $this->name       = $name;
        $this->definition = $definition;
        $this->osa        = $osa;
    }

    protected function getBasicVariables()
    {
        $data = [
            'versionNamespace' => $this->versionNamespace,
        ];

        return $data;
    }

    /**
     * @return bool
     */
    protected function canGenerate(): bool
    {
        return true;
    }

    protected function preprocess()
    {
    }

    /**
     * @return string
     */
    protected function getPath(): string
    {
        return '';
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return '';
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
