<?php

declare(strict_types=1);

namespace Hizpark\DirectoryTree\Tests;

use Hizpark\DirectoryTree\DirectoryNode;
use PHPUnit\Framework\TestCase;

class DirectoryNodeTest extends TestCase
{
    private string $basePath;

    protected function setUp(): void
    {
        $this->basePath = sys_get_temp_dir() . '/test-dir-node';

        mkdir("{$this->basePath}/child1/grandchild1", 0o777, true);
        mkdir("{$this->basePath}/child1/grandchild2", 0o777, true);
        mkdir("{$this->basePath}/child2", 0o777, true);
        touch("{$this->basePath}/child2/file.txt");
    }

    protected function tearDown(): void
    {
        $this->deleteDir($this->basePath);
    }

    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $this->deleteDir($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    public function testGetLocation(): void
    {
        $node = new DirectoryNode("{$this->basePath}/child1", null);

        $this->assertSame('child1', $node->getLocation());
    }

    public function testGetParent(): void
    {
        $parentNode = new DirectoryNode($this->basePath, null);

        $childNode = new DirectoryNode("{$this->basePath}/child1", $parentNode);

        $this->assertSame($parentNode, $childNode->getParent());
    }

    public function testAddChild(): void
    {
        $parentNode = new DirectoryNode($this->basePath, null);

        $childNode = new DirectoryNode("{$this->basePath}/child1", $parentNode);

        $parentNode->addChild($childNode);

        $children = $parentNode->getChildren();

        $this->assertNotNull($children);
        $this->assertCount(1, $children);
        $this->assertSame($childNode, $children['test-dir-node/child1']);
    }

    public function testGetChildren(): void
    {
        $parentNode = new DirectoryNode($this->basePath, null);

        $childNode1 = new DirectoryNode("{$this->basePath}/child1", $parentNode);

        $childNode2 = new DirectoryNode("{$this->basePath}/child2", $parentNode);

        $parentNode->addChild($childNode1);
        $parentNode->addChild($childNode2);

        $children = $parentNode->getChildren();

        self::assertNotNull($children);
        self::assertCount(2, $children);
        self::assertSame($childNode1, $children['test-dir-node/child1']);
        self::assertSame($childNode2, $children['test-dir-node/child2']);
    }
}
