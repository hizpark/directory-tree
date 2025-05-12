<?php

declare(strict_types=1);

namespace Hizpark\DirectoryTree\Tests\Memory;

use Hizpark\DirectoryTree\Memory\DirectoryTreeMemory;
use Hizpark\DirectoryTree\Node\DirectoryNode;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class DirectoryTreeMemoryTest extends TestCase
{
    private string $basePath;

    protected function setUp(): void
    {
        $this->basePath = sys_get_temp_dir() . '/test-tree-memory';

        mkdir("{$this->basePath}/level1a/level2a", 0o777, true);
        mkdir("{$this->basePath}/level1a/level2b", 0o777, true);
        mkdir("{$this->basePath}/level1b", 0o777, true);
        touch("{$this->basePath}/level1b/file.txt");
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->basePath);
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        /** @var SplFileInfo $item */
        foreach ($iterator as $item) {
            $path = $item->getRealPath();

            if ($item->isDir()) {
                rmdir($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    private function buildTestTree(): DirectoryNode
    {
        // 根節點
        $rootNode = new DirectoryNode($this->basePath, null);

        // 第一層
        $level1aNode = new DirectoryNode("{$this->basePath}/level1a", $rootNode);
        $level1bNode = new DirectoryNode("{$this->basePath}/level1b", $rootNode);

        // 第二層
        $level2aNode = new DirectoryNode("{$this->basePath}/level1a/level2a", $level1aNode);
        $level2bNode = new DirectoryNode("{$this->basePath}/level1a/level2b", $level1aNode);

        // 文件節點
        $fileNode = new DirectoryNode("{$this->basePath}/level1b/file.txt", $level1bNode);

        // 添加子節點
        $rootNode->addChild($level1aNode);
        $rootNode->addChild($level1bNode);

        $level1aNode->addChild($level2aNode);
        $level1aNode->addChild($level2bNode);

        $level1bNode->addChild($fileNode);

        return $rootNode;
    }

    public function testGetAncestors(): void
    {
        $rootNode = $this->buildTestTree();

        $children = $rootNode->getChildren();
        $this->assertIsArray($children);
        $this->assertArrayHasKey('test-tree-memory/level1a', $children);

        $level1aNode = $children['test-tree-memory/level1a'];

        $level1aChildren = $level1aNode->getChildren();
        $this->assertIsArray($level1aChildren);
        $this->assertArrayHasKey('test-tree-memory/level1a/level2a', $level1aChildren);

        $level2aNode = $level1aChildren['test-tree-memory/level1a/level2a'];

        $treeMemory = new DirectoryTreeMemory($rootNode);
        $ancestors  = $treeMemory->getAncestors($level2aNode);

        $this->assertCount(2, $ancestors);
        $this->assertSame($level1aNode, $ancestors[0]);
        $this->assertSame($rootNode, $ancestors[1]);
    }

    public function testGetSiblings(): void
    {
        $rootNode = $this->buildTestTree();

        $children = $rootNode->getChildren();
        $this->assertIsArray($children);
        $this->assertArrayHasKey('test-tree-memory/level1a', $children);
        $this->assertArrayHasKey('test-tree-memory/level1b', $children);

        $child1Node = $children['test-tree-memory/level1a'];
        $child2Node = $children['test-tree-memory/level1b'];

        $treeMemory = new DirectoryTreeMemory($rootNode);
        $siblings   = $treeMemory->getSiblings($child1Node);

        $this->assertCount(1, $siblings);
        $this->assertSame($child2Node, $siblings['test-tree-memory/level1b']);
    }

    public function testGetDescendants(): void
    {
        $rootNode    = $this->buildTestTree();
        $treeMemory  = new DirectoryTreeMemory($rootNode);
        $descendants = $treeMemory->getDescendants($rootNode);

        $this->assertCount(5, $descendants);

        $children = $rootNode->getChildren();
        $this->assertIsArray($children);
        $this->assertArrayHasKey('test-tree-memory/level1a', $children);
        $this->assertArrayHasKey('test-tree-memory/level1b', $children);

        $level1a = $children['test-tree-memory/level1a'];
        $level1b = $children['test-tree-memory/level1b'];

        $level1aChildren = $level1a->getChildren();
        $this->assertIsArray($level1aChildren);
        $this->assertArrayHasKey('test-tree-memory/level1a/level2a', $level1aChildren);
        $this->assertArrayHasKey('test-tree-memory/level1a/level2b', $level1aChildren);

        $level2a = $level1aChildren['test-tree-memory/level1a/level2a'];
        $level2b = $level1aChildren['test-tree-memory/level1a/level2b'];

        $level1bChildren = $level1b->getChildren();
        $this->assertIsArray($level1bChildren);
        $this->assertArrayHasKey('test-tree-memory/level1b/file.txt', $level1bChildren);

        $file = $level1bChildren['test-tree-memory/level1b/file.txt'];

        // Assert the correct order of descendants
        $this->assertSame($descendants[0], $level1a);
        $this->assertSame($descendants[1], $level1b);
        $this->assertSame($descendants[2], $level2a);
        $this->assertSame($descendants[3], $level2b);
        $this->assertSame($descendants[4], $file);
    }
}
