<?php
namespace hwcvod\vod\model;

abstract class BaseRequest
{
    abstract public function validate();

    public $serializedNamedParam = array();

    /**
     * @return array
     */
    public function getSerializedNamedParam()
    {
        return $this->serializedNamedParam;
    }

    /**
     * @param array $serializedNamedParam
     */
    public function setSerializedNamedParam(array $serializedNamedParam)
    {
        $this->serializedNamedParam = $serializedNamedParam;
    }
}
