<?php

declare(strict_types=1);

namespace Hizpark\DirectoryTree;

use Hizpark\DirectoryTree\Contract\NodeInterface;
use Hizpark\DirectoryTree\Contract\TreeMemoryInterface;
use Hizpark\DirectoryTree\Exception\DirectoryTreeException;
use SimpleXMLElement;

class DirectoryTreeTransformer
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(TreeMemoryInterface $tree): array
    {
        return $this->buildArray($tree->getRoot());
    }

    /**
     * @return array<string, mixed>
     */
    private function buildArray(NodeInterface $node): array
    {
        $data = [
            'path'           => $node->getPath(),
            'location'       => $node->getLocation(),
            'parentLocation' => $node->getParent()?->getLocation(),
        ];

        $children = $node->getChildren();

        if (is_array($children)) {
            $data['children'] = [];
            $children         = array_values($children);

            foreach ($children as $child) {
                $data['children'][] = $this->buildArray($child);
            }
        }

        return $data;
    }

    /**
     * 将树结构转换为 JSON 字符串
     */
    public function toJson(TreeMemoryInterface $tree, int $flags = JSON_PRETTY_PRINT): string
    {
        $data = $this->toArray($tree);

        $json = json_encode($data, $flags | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            $error = json_last_error_msg();

            throw new DirectoryTreeException("Failed to encode directory tree to JSON: {$error}");
        }

        return $json;
    }

    public function toXML(TreeMemoryInterface $tree): string
    {
        $xml = new SimpleXMLElement('<directory/>');
        $this->buildXML($tree->getRoot(), $xml);

        $result = $xml->asXML();

        if ($result === false) {
            throw new DirectoryTreeException('Failed to generate XML from tree structure');
        }

        return $result;
    }

    private function buildXML(NodeInterface $node, SimpleXMLElement $xml): void
    {
        if (is_dir($node->getPath())) {
            $dir = $xml->addChild('directory');
            $dir->addAttribute('location', $node->getLocation());
            $dir->addAttribute('path', $node->getPath());

            $children = $node->getChildren();

            if (is_array($children)) {
                $children = array_values($children);

                foreach ($children as $child) {
                    $this->buildXML($child, $dir);
                }
            }
        } else {
            $file = $xml->addChild('file');
            $file->addAttribute('location', $node->getLocation());
            $file->addAttribute('path', $node->getPath());
        }
    }

    public function toHtmlList(TreeMemoryInterface $tree): string
    {
        return '<ul>' . $this->buildHtml($tree->getRoot()) . '</ul>';
    }

    private function buildHtml(NodeInterface $node): string
    {
        $label    = htmlspecialchars(basename($node->getPath()));
        $location = htmlspecialchars($node->getLocation());
        $path     = htmlspecialchars($node->getPath());
        $html     = "<li data-location=\"{$location}\" data-path=\"{$path}\">{$label}";

        if (is_dir($node->getPath())) {
            $html .= '<ul>';

            $children = $node->getChildren();

            if (is_array($children)) {
                $children = array_values($children);

                foreach ($children as $child) {
                    $html .= $this->buildHtml($child);
                }
            }

            $html .= '</ul>';
        }

        $html .= '</li>';

        return $html;
    }

    public function toMarkdownList(TreeMemoryInterface $tree, int $indentSize = 2): string
    {
        return $this->buildMarkdownList($tree->getRoot(), 0, $indentSize);
    }

    private function buildMarkdownList(NodeInterface $node, int $depth = 0, int $indentSize = 2): string
    {
        $isRoot = is_dir($node->getPath()) && $node->getParent() === null;
        $indent = $isRoot ? '' : str_repeat(' ', $depth * $indentSize);
        $line   = $indent . ($isRoot ? '' : '- ') . basename($node->getPath()) . PHP_EOL;

        $children = $node->getChildren();

        if (is_array($children)) {
            $children = array_values($children);

            foreach ($children as $child) {
                $line .= $this->buildMarkdownList($child, $depth + 1, $indentSize);
            }
        }

        return $line;
    }

    public function toTextIndented(TreeMemoryInterface $tree, int $indentSize = 2): string
    {
        return $this->buildTextIndented($tree->getRoot(), 0, $indentSize);
    }

    private function buildTextIndented(NodeInterface $node, int $depth = 0, int $indentSize = 2): string
    {
        $isRoot = is_dir($node->getPath()) && $node->getParent() === null;
        $indent = $isRoot ? '' : str_repeat(' ', $depth * $indentSize);
        $line   = $indent . basename($node->getPath()) . PHP_EOL;

        $children = $node->getChildren();

        if (is_array($children)) {
            $children = array_values($children);

            foreach ($children as $child) {
                $line .= $this->buildTextIndented($child, $depth + 1, $indentSize);
            }
        }

        return $line;
    }

    public function toTextTree(TreeMemoryInterface $tree): string
    {
        return $this->buildTextTree($tree->getRoot());
    }

    private function buildTextTree(NodeInterface $node, string $prefix = '', bool $isLast = true): string
    {
        $isRoot = is_dir($node->getPath()) && $node->getParent() === null;

        $line = '';

        if (!$isRoot) {
            $line .= $prefix . ($isLast ? '└── ' : '├── ');
        }

        $line .= basename($node->getPath()) . PHP_EOL;

        $children = $node->getChildren();

        if (is_array($children)) {
            $children = array_values($children);
            $count    = count($children);

            foreach ($children as $i => $child) {
                $childIsLast = ($i === $count - 1);
                $newPrefix   = $prefix . ($isRoot ? '' : ($isLast ? '    ' : '│   '));
                $line .= $this->buildTextTree($child, $newPrefix, $childIsLast);
            }
        }

        return $line;
    }
}
