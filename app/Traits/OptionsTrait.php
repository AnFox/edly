<?php

namespace App\Traits;

use Illuminate\Support\Arr;

/**
 * Implements the functionality to manage options field, when data stores as JSON array.
 *
 */
trait OptionsTrait
{

    /**
     * @param bool $asArray
     * @return mixed
     */
    public function getOptions(bool $asArray = false)
    {
        $optionsFieldName = $this->optionsField ?? 'options';
        $options = $this->$optionsFieldName;

        if ($asArray) {
            $options = json_decode($options, true);
        }

        return $options;
    }

    /**
     * @param $options
     * @return false|string
     */
    public function setOptions($options)
    {
        if (is_array($options)) {
            $options = json_encode($options);
        }

        $optionsFieldName = $this->optionsField ?? 'options';
        $this->$optionsFieldName = $options;

        return $this->save();
    }

    /**
     * @param string $name
     * @param null $default
     * @return string|null
     */
    public function getOption(string $name, $default = null)
    {
        $result = Arr::get($this->getOptions(true), $name);

        return is_null($result) ? $default : $result;
    }

    /**
     * @param $name
     * @param $value
     * @return false|string
     */
    public function setOption($name, $value)
    {
        $options = $this->getOptions(true);

        Arr::set($options, $name, $value);

        return $this->setOptions($options);
    }

    /**
     * @param string $name
     * @return false|string
     */
    public function unsetOption(string $name)
    {
        $options = $this->getOptions(true);

        Arr::forget($options, $name);

        return $this->setOptions($options);
    }

}