<?php

namespace Tsuka\DB\Value;

final class UniqueViolation
{
    /**
     * @var string
     */
    private $constraint = '';

    /**
     * @var string
     */
    private $key = '';

    /**
     * @var string
     */
    private $value = '';

    /**
     * @param string $constraint
     * @param string $key
     * @param string $value
     */
    final public function __construct($constraint, $key, $value)
    {
        $this->constraint = $constraint;
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @param string $error
     * @return self
     */
    public static function createFromPostgresError(string $error)
    {
        $matches = [];
        $match = preg_match(
            '/constraint "([a-zA-Z0-9_-]+)".*Key \(([a-zA-Z0-9_-]+)\)=\(([a-zA-Z0-9_-]+)\)/ms',
            $error,
            $matches
        );

        if (!$match) {
            return new self('', '', '');
        }

        return new self(...array_slice($matches, 1));
    }

    /**
     * @param string $template
     * @return string
     */
    public function format(string $template)
    {
        $vars = [
            '%constraint%',
            '%key%',
            '%value%',
        ];

        $values = [
            $this->constraint,
            $this->key,
            $this->value,
        ];

        return str_replace($vars, $values, $template);
    }
}
