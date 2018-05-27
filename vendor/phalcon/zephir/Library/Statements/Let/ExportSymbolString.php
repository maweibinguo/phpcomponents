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

namespace Zephir\Statements\Let;

use Zephir\CompilationContext;
use Zephir\Variable as ZephirVariable;
use Zephir\CompiledExpression;
use Zephir\Statements\LetStatement;

/**
 * ExportSymbolString
 *
 * Exports a symbol to the current PHP symbol table using a variable as parameter
 */
class ExportSymbolString
{
    /**
     * Compiles {"var"} = {expr}
     *
     * @param string $variable
     * @param ZephirVariable $symbolVariable
     * @param CompiledExpression $resolvedExpr
     * @param CompilationContext $compilationContext,
     * @param array $statement
     */
    public function assign($variable, ZephirVariable $symbolVariable = null, CompiledExpression $resolvedExpr = null, CompilationContext $compilationContext = null, $statement = null)
    {
        $codePrinter = $compilationContext->codePrinter;

        $variable = $compilationContext->symbolTable->getTempVariable('variable', $compilationContext, $statement);
        $variable->setMustInitNull(true);

        $letStatement = new LetStatement(array(
            'type' => 'let',
            'assignments' => array(
                array(
                    'assign-type' => 'variable',
                    'variable' => $variable->getName(),
                    'operator' => 'assign',
                    'expr' => array(
                        'type'  => $resolvedExpr->getType(),
                        'value' => $resolvedExpr->getCode(),
                        'file'  => $statement['file'],
                        'line'  => $statement['line'],
                        'char'  => $statement['char'],
                    ),
                    'file'  => $statement['file'],
                    'line'  => $statement['line'],
                    'char'  => $statement['char'],
                )
            )
        ));
        $letStatement->compile($compilationContext);

        $symbol = $compilationContext->backend->getVariableCode($variable);
        $codePrinter->output('if (zephir_set_symbol_str(SS("' . $statement["variable"] . '"), ' . $symbol . ' TSRMLS_CC) == FAILURE) {');
        $codePrinter->output('  return;');
        $codePrinter->output('}');
    }
}
