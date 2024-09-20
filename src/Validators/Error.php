<?php

namespace LaravelRocket\Generator\Validators;

class Error
{
    public const LEVEL_ERROR   = 'error';
    public const LEVEL_WARNING = 'warning';
    public const LEVEL_INFO    = 'info';

    /** @var string */
    protected $message = '';

    /** @var string */
    protected $level = '';

    /** @var array */
    protected $suggestions = [];

    /** @var string */
    protected $referenceUrl = '';

    /** @var string */
    protected $target = '';

    public function __construct($message, $level, $target, $suggestions = [], $referenceUrl = '')
    {
        $this->message = $message;
        $this->level   = $level;
        $this->target  = $target;
        if (!is_array($suggestions)) {
            $suggestions = [$suggestions];
        }
        $this->suggestions  = $suggestions;
        $this->referenceUrl = $referenceUrl;
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

    public function getReferenceUrl(): string
    {
        return $this->referenceUrl;
    }
}
