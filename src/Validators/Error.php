<?php
namespace LaravelRocket\Generator\Validators;

class Error
{
    const LEVEL_ERROR   = 'error';
    const LEVEL_WARNING = 'warning';
    const LEVEL_INFO    = 'info';

    /** @var string */
    protected $message = '';

    /** @var string */
    protected $level = '';

    /** @var array */
    protected $suggestions = [];

    /** @var string */
    protected $target = '';

    public function __construct($message, $level, $target, $suggestions = [])
    {
        $this->message = $message;
        $this->level   = $level;
        $this->target  = $target;
        if (!is_array($suggestions)) {
            $suggestions = [$suggestions];
        }
        $this->suggestions = $suggestions;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * @return array
     */
    public function getSuggestions(): array
    {
        return $this->suggestions;
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }
}
