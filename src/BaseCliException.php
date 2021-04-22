<?php

namespace Antares\Support\BaseCli;

use Exception;

class BaseCliException extends Exception
{
    /**
     * Create a new exception for no data supplied
     *
     * @return static
     */
    public static function forNoDataSupplied()
    {
        return new static("No data supplied.\n");
    }

    /**
     * Create a new exception for already defined option
     *
     * @return static
     */
    public static function forAlreadyDefinedOption($option)
    {
        return new static("Already defined option: {$option}.\n");
    }

    /**
     * Create a new exception for already defined option
     *
     * @return static
     */
    public static function forAlreadyParsedOption($option)
    {
        return new static("Already parsed option: {$option}.\n");
    }

    /**
     * Create a new exception for option than does not accpect value
     *
     * @return static
     */
    public static function forOptionDoesNotAcceptValue($option)
    {
        return new static("Option {$option} does not accept value.\n");
    }

    /**
     * Create a new exception for option that needs a value
     *
     * @return static
     */
    public static function forOptionNeedsValue($option)
    {
        return new static("Option needs a value: {$option}.\n");
    }

    /**
     * Create a new exception for invalid property
     *
     * @return static
     */
    public static function forInvalidProperty($property, $option)
    {
        return new static("Invalid property '{$property}' in option {$option}.\n");
    }

    /**
     * Create a new exception for invalid property value
     *
     * @return static
     */
    public static function forInvalidPropertyValue($value, $property, $option)
    {
        return new static("Invalid value for property '{$property}' in option {$option}:\n" . print_r($value));
    }

    /**
     * Create a new exception for invalid token
     *
     * @return static
     */
    public static function forInvalidToken($token)
    {
        return new static("Invalid token: {$token}.\n");
    }
}
