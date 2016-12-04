<?php
namespace NHP;
use NHP\AST;
use PhpParser\PrettyPrinter;

final class Main {
    private function __construct() { }

    public static function main(): void {
        $text = <<<'EOF'
val goldenRatio = 1.61803398875f;
val pi = 3.14159265359f;
def getGoldenRatio() =
    goldenRatio;
def getPi() =
    3.14159265359f;
def discardGoldenRatioAndPi() = {
    goldenRatio;
    pi;
    {};
};
def getGetGoldenRatio() = {
    getGoldenRatio;
};
def getGetPi() = {
    val pi = 3.14159265359f;
    def getPi() = {
        val pi = pi;
        pi;
    };
    getPi;
};
EOF;
        $lexer = new Lexer($text);
        $definitions = [];
        while (($token = $lexer->peek())[0] !== Lexer::EOF_TYPE) {
            $definitions[] = Parse::parseDefinition($lexer);
        }

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
