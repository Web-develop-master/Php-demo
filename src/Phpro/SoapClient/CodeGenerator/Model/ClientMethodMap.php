<?php

namespace Phpro\SoapClient\CodeGenerator\Model;

use Phpro\SoapClient\Soap\Engine\Metadata\Collection\MethodCollection;
use Phpro\SoapClient\Soap\Engine\Metadata\Model\Method;

/**
 * Class ClientMethodMap
 *
 * @package Phpro\SoapClient\CodeGenerator\Model
 */
class ClientMethodMap
{
    /**
     * @var ClientMethod[]
     */
    private $methods;

    /**
     * ClientMethodMap constructor.
     *
     * @param array|ClientMethod[] $methods
     */
    public function __construct(array $methods)
    {
        $this->methods = $methods;
    }

    public static function fromMetadata(string $parameterNamespace, MethodCollection $collection): self
    {
        return new self($collection->map(function (Method $method) use ($parameterNamespace) {
            return ClientMethod::fromMetadata($parameterNamespace, $method);
        }));
    }

    public static function fromSoapClient(\SoapClient $client, $parameterNamespace = '') : ClientMethodMap
    {
        $clientMethods = [];
        foreach ($client->__getFunctions() as $method) {
            $clientMethods[] = ClientMethod::createFromExtSoapFunctionString($method, $parameterNamespace);
        }

        return new self($clientMethods);
    }

    /**
     * @return ClientMethod[]
     */
    public function getMethods() : array
    {
        return $this->methods;
    }
}
