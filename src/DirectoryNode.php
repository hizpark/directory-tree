<?php

declare(strict_types=1);

namespace Hizpark\DirectoryTree;

use Hizpark\DirectoryTree\Contract\NodeInterface;
use Hizpark\DirectoryTree\Exception\DirectoryTreeException;

class DirectoryNode implements NodeInterface
{
    private readonly string $path;

    private readonly ?NodeInterface $parent;

    private string $location;

    /**
     * @var ?NodeInterface[] $children
     */
    private ?array $children;

    public function __construct(string $path, ?NodeInterface $parent)
    {
        if (is_link($path) && $parent === null) {
            throw new DirectoryTreeException("Root node cannot be a symbolic link: {$path}");
        }

        $path = $this->resolvePath($path);

        // 確保路徑可讀且存在
        if (!file_exists($path)) {
            throw new DirectoryTreeException("Path does not exist: {$path}");
        }

        if (!is_readable($path)) {
            throw new DirectoryTreeException("Path is not readable: {$path}");
        }

        if ($parent !== null) {
            $parentPath = $this->resolvePath($parent->getPath());

            if (strpos($path, $parentPath . DIRECTORY_SEPARATOR) !== 0) {
                throw new DirectoryTreeException('Node is not a descendant of its parent');
            }
        }

        $this->path     = $path;
        $this->parent   = $parent;
        $this->location = ($parent !== null ? $parent->getLocation() . DIRECTORY_SEPARATOR : '') . basename($path);
        $this->children = is_dir($path) ? [] : null;
    }

    private function resolvePath(string $path): string
    {
        $realPath = $path;

        if (strpos($path, 'phar://') !== 0) {
            $realPath = realpath($path);

            if ($realPath === false) {
                throw new DirectoryTreeException("Cannot resolve real path: {$path}");
            }
        }

        return $realPath;
    }

    public function getParent(): ?NodeInterface
    {
        return $this->parent;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @return ?NodeInterface[]
     */
    public function getChildren(): ?array
    {
        return $this->children;
    }

    public function addChild(NodeInterface $child): void
    {
        if (!is_array($this->children)) {
            throw new DirectoryTreeException('Cannot add child to non-directory node');
        }

        $location = $child->getLocation();

        $this->children[$location] = $child;
    }
}
