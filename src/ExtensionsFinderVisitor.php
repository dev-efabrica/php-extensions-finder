<?php

namespace ExtensionsFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;
use ReflectionClass;
use ReflectionFunction;

class ExtensionsFinderVisitor extends NodeVisitorAbstract
{
    private $coreExtensions = ['core', 'date', 'pcre', 'reflection', 'spl', 'standard'];

    private $extensions = [];

    public function enterNode(Node $node)
    {
        if ($node instanceof StaticCall || $node instanceof StaticPropertyFetch || $node instanceof ClassConstFetch || $node instanceof New_) {
            if (!$node->class instanceof Name) {
                return null;
            }
            $name = (string) $node->class;
            if (!class_exists($name, false)) {
                return null;
            }
            $extensionName = (new ReflectionClass($name))->getExtensionName();
            $this->addExtension($extensionName, $name, $node->class->getAttribute('startLine'));
        } elseif ($node instanceof FuncCall && $node->name instanceof Name) {
            $name = (string) $node->name;
            if (!function_exists($name)) {
                return null;
            }
            $extensionName = (new ReflectionFunction($name))->getExtensionName();
            $this->addExtension($extensionName, $name, $node->name->getAttribute('startLine'));
        } elseif ($node instanceof ConstFetch) {
            $name = (string) $node->name;
            $all = get_defined_constants(true);
            foreach ($all as $ext => $consts) {
                if (isset($consts[$name])) {
                    $this->addExtension($ext, $name, $node->name->getAttribute('startLine'));
                    break;
                }
            }
        }
        return null;
    }
    private function addExtension(string $extName, string $token, int $line): void
    {
        if (!$extName) {
            return;
        }
        $extName = strtolower($extName);
        if (in_array($extName, $this->coreExtensions)) {
            return;
        }
        $this->extensions['ext-' . $extName][] = [
            'token' => $token,
            'line' => $line,
        ];
    }

    public function getExtensions(): array
    {
        ksort($this->extensions);
        return $this->extensions;
    }

}
