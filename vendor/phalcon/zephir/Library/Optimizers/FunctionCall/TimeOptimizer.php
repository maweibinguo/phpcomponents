<?php

/*
 +--------------------------------------------------------------------------+
 | Zephir                                                                   |
 | Copyright (c) 2013-present Zephir Team (https://zephir-lang.com/)        |
 |                                                                          |
 | This source file is subject the MIT license, that is bundled with this   |
 | package in the file LICENSE, and is available through the world-wide-web |
 | at the following url: http://zephir-lang.com/license.html                |
 +--------------------------------------------------------------------------+
*/

namespace Zephir\Optimizers\FunctionCall;

use Zephir\Call;
use Zephir\CompilationContext;
use Zephir\Compiler\CompilerException;
use Zephir\CompiledExpression;
use Zephir\Optimizers\OptimizerAbstract;

/**
 * TimeOptimizer
 *
 * Optimizes calls to 'time' using internal function
 */
class TimeOptimizer extends OptimizerAbstract
{
    /**
     * @param array $expression
     * @param Call $call
     * @param CompilationContext $context
     * @return bool|CompiledExpression|mixed
     * @throws CompilerException
     */
    public function optimize(array $expression, Call $call, CompilationContext $context)
    {
        if (isset($expression['parameters'])) {
            return false;
        }

        /**
         * Process the expected symbol to be returned
         */
        $call->processExpectedReturn($context);

        $symbolVariable = $call->getSymbolVariable(true, $context);
        if ($symbolVariable->isNotVariableAndString()) {
            throw new CompilerException("Returned values by functions can only be assigned to variant variables", $expression);
        }

        $context->headersManager->add('kernel/time');
        $symbolVariable->setDynamicTypes('long');

        if ($call->mustInitSymbolVariable()) {
            $symbolVariable->initVariant($context);
        }
        $symbol = $context->backend->getVariableCode($symbolVariable);
        $context->codePrinter->output('zephir_time(' . $symbol . ');');
        return new CompiledExpression('variable', $symbolVariable->getRealName(), $expression);
    }
}
