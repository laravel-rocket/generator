<?php
namespace LaravelRocket\Generator\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class BaseCommand extends Command
{
    /** @var \Illuminate\Config\Repository */
    protected $config;

    /** @var \Illuminate\Filesystem\Filesystem */
    protected $files;

    /** @var \Illuminate\View\Factory */
    protected $view;

    /** @var \LaravelRocket\Generator\Objects\Definitions */
    protected $json;

    /**
     * @param \Illuminate\Config\Repository     $config
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param \Illuminate\View\Factory          $view
     */
    public function __construct(
        \Illuminate\Config\Repository $config,
        \Illuminate\Filesystem\Filesystem $files,
        \Illuminate\View\Factory $view
    ) {
        $this->config = $config;
        $this->files  = $files;
        $this->view   = $view;
        parent::__construct();
    }

    protected function getAppJson()
    {
        $file    = $this->option('json');
        $default = false;
        if (empty($file)) {
            $default = true;
            $file    = base_path('documents/app.json');
        }

        if (!file_exists($file)) {
            if ($default) {
                $this->output('JSON file ( '.$file.' ) doesn\'t exist. This is default file path. You can specify file path with --json option.', 'error');
            } else {
                $this->output('JSON file  ( '.$file.' ) doesn\'t exist. Please check file path.', 'error');
            }

            return false;
        }

        $data = file_get_contents($file);

        $this->json = new Definitions($data);
    }

    /**
     * @param string $description
     * @param array  $options
     * @param int    $default
     * @param bool   $multiSelect
     *
     * @return mixed
     */
    protected function askOptions(string $description, array $options, int $default, $multiSelect = false)
    {
        $helper   = $this->getHelper('question');
        $question = new ChoiceQuestion(
            $description,
            $options,
            $default
        );
        if ($multiSelect) {
            $question->setMultiselect(true);
        }
        $question->setErrorMessage('Answer is invalid');
        $answer = $helper->ask($this->input, $this->output, $question);

        return $answer;
    }

    /**
     * @param string $description
     * @param bool   $default
     *
     * @return bool
     */
    protected function askYesNo(string $description, boolean $default)
    {
        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion($description, $default);

        return $helper->ask($this->input, $this->output, $question);
    }

    /**
     * @param string $description
     * @param string $default
     *
     * @return string
     */
    protected function askQuestion(string $description, string $default)
    {
        $helper   = $this->getHelper('question');
        $question = new Question($description, $default);
        $answer   = $helper->ask($this->input, $this->output, $question);

        return $answer;
    }

    /**
     * @param string      $message
     * @param string|null $color
     */
    protected function output(string $message, $color = null)
    {
        if (!empty($color)) {
            if (in_array($color, ['info', 'comment', 'error', 'question'])) {
                $this->output->writeln('<'.$color.'>'.$message.'</'.$color.'>');
            } else {
                $this->output->writeln('<fg='.$color.'>'.$message.'</>');
            }
        } else {
            $this->output->writeln($message);
        }
    }
}
