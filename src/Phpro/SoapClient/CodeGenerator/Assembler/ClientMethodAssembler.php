<?php

namespace Phpro\SoapClient\CodeGenerator\Assembler;

use Phpro\SoapClient\Client;
use Phpro\SoapClient\CodeGenerator\Context\ContextInterface;
use Phpro\SoapClient\CodeGenerator\Context\ClientMethodContext;
use Phpro\SoapClient\CodeGenerator\Util\Normalizer;
use Phpro\SoapClient\Exception\AssemblerException;
use Zend\Code\Generator\MethodGenerator;

/**
 * Class ClientMethodAssembler
 *
 * @package Phpro\SoapClient\CodeGenerator\Assembler
 */
class ClientMethodAssembler implements AssemblerInterface
{
    /**
     * {@inheritdoc}
     */
    public function canAssemble(ContextInterface $context): bool
    {
        return $context instanceof ClientMethodContext;
    }

    /**
     * @param ContextInterface|ClientMethodContext $context
     *
     * @return bool
     * @throws AssemblerException
     */
    public function assemble(ContextInterface $context): bool
    {
        $class = $context->getClass();
        $class->setExtendedClass(Client::class);
        $method = $context->getMethod();
        try {
            $params = $method->getParameters();
            $param = array_shift($params);
            $class->removeMethod($method->getMethodName());
            $class->addMethodFromGenerator(
                MethodGenerator::fromArray(
                    [
                        'name'       => $method->getMethodName(),
                        'parameters' => $method->getParameters(),
                        'visibility' => MethodGenerator::VISIBILITY_PUBLIC,
                        'body'       => sprintf(
                            'return $this->call(\'%s\', $%s);',
                            Normalizer::getClassNameFromFQN($param->getType()),
                            $param->getName()
                        ),
                        // TODO: Use normalizer once https://github.com/phpro/soap-client/pull/61 is merged
                        'returntype' => '\\'.$method->getParameterNamespace().'\\'.$method->getReturnType(),
                    ]
                )
            );
        } catch (\Exception $e) {
            throw AssemblerException::fromException($e);
        }

        return true;
    }
}
