<?php
namespace NHP;
use NHP\AST;
use PhpParser\PrettyPrinter;

final class Main {
    private function __construct() { }

    public static function main(): void {
        $text = <<<'EOF'
val goldenRatio = 3.14159265359f;
EOF;
        $lexer = new Lexer($text);
        while (($token = $lexer->read())[0] !== Lexer::EOF_TYPE) {
            var_dump($token);
        }

        $definitions = [
            new AST\VariableDefinition(
                'goldenRatio',
                new AST\FloatLiteralExpression(1.61803398875)
            ),
            new AST\FunctionDefinition(
                'getGoldenRatio',
                new AST\BlockExpression([
                    new AST\VariableDefinition(
                        'diameterCircumferenceRatio',
                        new AST\FloatLiteralExpression(3.14159265359)
                    ),
                    new AST\VariableExpression('goldenRatio'),
                ])
            ),
        ];

        $analyzer = new Analyzer(Scope::globalScope(), []);
        foreach ($definitions as $definition) {
            $analyzer->analyzeDefinition($definition);
        }

        $stmts = [];
        foreach ($definitions as $definition) {
            $stmts = array_merge($stmts, CodeGen::codeGenDefinitionToStmts($definition));
        }
        $prettyPrinter = new PrettyPrinter\Standard();
        echo $prettyPrinter->prettyPrintFile($stmts);
    }
}
