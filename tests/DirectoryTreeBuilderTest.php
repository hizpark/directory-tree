<?php

declare(strict_types=1);

namespace Hizpark\DirectoryTree\Tests;

use Hizpark\DirectoryTree\DirectoryNode;
use Hizpark\DirectoryTree\DirectoryTreeBuilder;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class DirectoryTreeBuilderTest extends TestCase
{
    private string $basePath;

    protected function setUp(): void
    {
        $this->basePath = sys_get_temp_dir() . '/test-dir-builder';

        mkdir("{$this->basePath}/dirA/subA1", 0o777, true);
        mkdir("{$this->basePath}/dirA/subA2", 0o777, true);
        mkdir("{$this->basePath}/dirB/subB1", 0o777, true);
        mkdir("{$this->basePath}/dirB/subB2", 0o777, true);

        // 第三層
        mkdir("{$this->basePath}/dirA/subA1/deep1", 0o777, true);
        mkdir("{$this->basePath}/dirA/subA1/deep2", 0o777, true);

        // 檔案節點
        touch("{$this->basePath}/dirA/file1.txt");
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

    public function testBuild(): void
    {
        $rootNode = new DirectoryNode($this->basePath, null);

        $builder    = new DirectoryTreeBuilder();
        $treeMemory = $builder->build($rootNode);

        $this->assertSame($rootNode, $treeMemory->getRoot());

        $descendants = $treeMemory->getDescendants($rootNode);
        $this->assertGreaterThanOrEqual(7, count($descendants), '應該至少有 7 個 descendant：包含多層資料夾與檔案');
    }
}
