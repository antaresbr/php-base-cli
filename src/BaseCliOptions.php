<?php

namespace Antares\Support\BaseCli;

use Antares\Support\Arr;
use Antares\Support\Options\AbstractOptions;
use Antares\Support\Options\OptionsException;
use Antares\Support\Str;

class BaseCliOptions extends AbstractOptions
{
    /**
     * Valid properties
     */
    public const VALID_PROPERTIES = [
        'labels',
        'help',
        'required',
        'valued',
        'multiple',
        'validValues',
        'default',
        'stopHere',
    ];

    /**
     * Class construtor
     *
     * @param array $prototypes
     */
    public function __construct($prototypes)
    {
        $this->setPrototypes($prototypes);
    }

    public function help(array $list = [], $includeLabels = true, $initialTab = '    ')
    {
        $help = '';

        foreach ($this->prototypes as $option => $prototype) {
            if (empty($list) or in_array($option, $list)) {
                if (!empty($help)) {
                    $help .= "\n";
                }
                $tab = $initialTab;
                if ($includeLabels) {
                    $help = Str::join("\n", $help, $tab . implode(', ', $prototype['labels']));
                    $tab .= '    ';
                }
                foreach ($prototype['help'] as $line) {
                    $help = Str::join("\n", $help, $tab . $line);
                }
            }
        }

        return $help;
    }

    /**
     * Set options prototypes
     *
     * @param array $prototypes
     * @return void
     */
    public function setPrototypes(array $prototypes)
    {
        $this->reset();
        $this->prototypes = [];

        if (empty($prototypes)) {
            throw BaseCliException::forNoDataSupplied();
        }

        foreach ($prototypes as $option => $properties) {
            if (Arr::has($this->prototypes, $option)) {
                throw BaseCliException::forAlreadyDefinedOption($option);
            }

            $props = [
                'labels' => [$option],
                'help' => [],
                'required' => false,
                'valued' => false,
                'multiple' => false,
                'validValues' => [],
                'default' => null,
                'stopHere' => false,
            ];

            foreach ($properties as $propKey => $propValue) {
                if (!in_array($propKey, static::VALID_PROPERTIES)) {
                    throw BaseCliException::forInvalidProperty($propKey, $option);
                }

                if (Str::icIn($propKey, 'lables', 'help', 'validValues')) {
                    if (is_string($propValue)) {
                        $glue = ($propKey == 'help') ? "\n" : '|';
                        $propValue = explode($glue, $propValue);
                    }
                    if (!is_array($propValue)) {
                        throw BaseCliException::forInvalidPropertyValue($propValue, $propKey, $option);
                    }
                }

                if (Str::icIn($propKey, 'required', 'valued', 'multiple', 'stopHere')) {
                    $value = is_string($propValue) ? strtolower($propValue) : $propValue;
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    if ($value === null) {
                        throw BaseCliException::forInvalidPropertyValue($propValue, $propKey, $option);
                    }
                    $propValue = $value;
                }

                $props[$propKey] = $propValue;
            }

            $this->prototypes[$option] = $props;
        }
    }

    /**
     * Get option from given token
     *
     * @param string $token
     * @return bool|array
     */
    public function optionFromToken($token)
    {
        foreach ($this->prototypes as $option => $prototype) {
            if (in_array($token, $prototype['labels'])) {
                return $option;
            }
        }

        return false;
    }

    /**
     * Parse params
     *
     * @param array $params
     * @param bool $ignoreInvalidTokens
     * @return static
     */
    public function parse(array &$params, $ignoreInvalidTokens = false)
    {
        $this->reset();

        $pix = 0;
        while ($pix < count($params)) {
            $token = Arr::first(array_splice($params, $pix, 1));
            $tp = strpos($token, '=');
            if ($tp === false) {
                $tokenKey = $token;
                $tokenValue = null;
            } else {
                $tokenKey = substr($token, 0, $tp + 1);
                $tokenValue = substr($token, $tp + 1);
            }

            $option = $this->optionFromToken($tokenKey);
            if ($option === false) {
                if ($ignoreInvalidTokens) {
                    array_splice($params, $pix, 0, $token);
                    $pix++;
                    continue;
                } else {
                    throw BaseCliException::forInvalidToken($tokenKey);
                }
            }
            $prototype = $this->getPrototype($option);

            if ($this->has($option) and !$prototype['multiple']) {
                throw BaseCliException::forAlreadyParsedOption($tokenKey);
            }

            if (!$prototype['valued']) {
                if ($tokenValue !== null) {
                    throw BaseCliException::forOptionDoesNotAcceptValue($tokenKey);
                }
                $this->set($option, $tokenKey);
                if ($prototype['stopHere']) {
                    break;
                } else {
                    continue;
                }
            }

            $values = [];

            $nextToken = $tokenValue;
            while (true == true) {
                if ($nextToken === null) {
                    $nextToken = ($pix < count($params)) ? $params[$pix] : null;
                }
                if (
                    $nextToken === null or
                    ($nextToken !== $tokenValue and substr($nextToken, 0, 1) == '-') or
                    (!$prototype['multiple'] and !empty($values))
                ) {
                    break;
                }

                if ($tokenValue === null) {
                    $nextToken = Arr::first(array_splice($params, $pix, 1));
                } else {
                    $tokenValue = null;
                }

                if (!empty($prototype['validValues']) and !$this->isValidValue($nextToken, $prototype['validValues'])) {
                    throw OptionsException::forInvalidValue($tokenKey, $prototype['validValues'], $nextToken);
                }

                if (!in_array($nextToken, $values)) {
                    $values[] = $nextToken;
                }

                $nextToken = null;
            }

            if (empty($values)) {
                throw BaseCliException::forOptionNeedsValue($tokenKey);
            }

            if ($prototype['multiple']) {
                $this->set($option, array_merge(Arr::wrap($this->get($option)), $values));
            } else {
                $this->set($option, $values[0]);
            }

            if ($prototype['stopHere']) {
                break;
            } else {
                continue;
            }
        }

        foreach ($this->prototypes as $option => $prototype) {
            if (!$this->has($option) and $prototype['default'] !== null) {
                $this->set($option, $prototype['default']);
            }
            if (!$this->has($option) and $prototype['required']) {
                throw BaseCliException::forOptionNeedsValue($option);
            }
        }

        return $this;
    }

    /**
     * Make a brand new params options object
     *
     * @param array $prototypes The protorypes applied to this object
     * @param array $params Params data to be used in this object
     * @return static
     */
    public static function make(array $prototypes, array $params)
    {
        $opt = new static($prototypes);
        $opt->parse($params);

        return $opt;
    }
}
