<?php

declare(strict_types=1);

namespace Hizpark\DirectoryTree\Tests;

use DOMDocument;
use Hizpark\DirectoryTree\DirectoryNode;
use Hizpark\DirectoryTree\DirectoryTreeMemory;
use Hizpark\DirectoryTree\DirectoryTreeTransformer;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class DirectoryTreeTransformerTest extends TestCase
{
    private string $basePath;

    protected function setUp(): void
    {
        $this->basePath = sys_get_temp_dir() . '/test-tree-transformer';

        mkdir("{$this->basePath}/level1a/level2a/level3a", 0o777, true);
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

    private function createTreeMemory(): DirectoryTreeMemory
    {
        $rootNode = new DirectoryNode($this->basePath, null);

        $level1a = new DirectoryNode("{$this->basePath}/level1a", $rootNode);
        $level1b = new DirectoryNode("{$this->basePath}/level1b", $rootNode);

        $level2a = new DirectoryNode("{$this->basePath}/level1a/level2a", $level1a);
        $level2b = new DirectoryNode("{$this->basePath}/level1a/level2b", $level1a);

        $level3a = new DirectoryNode("{$this->basePath}/level1a/level2a/level3a", $level2a);
        $file    = new DirectoryNode("{$this->basePath}/level1b/file.txt", $level1b);

        $rootNode->addChild($level1a);
        $rootNode->addChild($level1b);

        $level1a->addChild($level2a);
        $level1a->addChild($level2b);

        $level2a->addChild($level3a);
        $level1b->addChild($file);

        return new DirectoryTreeMemory($rootNode);
    }

    public function testToArray(): void
    {
        $memory      = $this->createTreeMemory();
        $transformer = new DirectoryTreeTransformer();
        $array       = $transformer->toArray($memory);

        $this->assertIsArray($array['children']);
        $this->assertSame(basename($this->basePath), $array['location']);
    }

    public function testToJson(): void
    {
        $memory      = $this->createTreeMemory();
        $transformer = new DirectoryTreeTransformer();
        $json        = $transformer->toJson($memory);

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertSame(basename($this->basePath), $decoded['location']);
    }

    public function testToXML(): void
    {
        $memory      = $this->createTreeMemory();
        $transformer = new DirectoryTreeTransformer();
        $xml         = $transformer->toXML($memory);

        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('<directory', $xml);
        $this->assertTrue(
            str_contains($xml, '<file') || str_contains($xml, '<directory'),
            'Expected XML to contain either <file> or <directory> node.',
        );

        $dom = new DOMDocument();
        $this->assertTrue($dom->loadXML($xml), 'Generated XML is not valid.');
    }

    public function testToTextTree(): void
    {
        $memory      = $this->createTreeMemory();
        $transformer = new DirectoryTreeTransformer();
        $text        = $transformer->toTextTree($memory);

        $this->assertStringContainsString('├──', $text);
        $this->assertStringContainsString('└──', $text);
    }

    public function testToTextIndented(): void
    {
        $memory      = $this->createTreeMemory();
        $transformer = new DirectoryTreeTransformer();
        $text        = $transformer->toTextIndented($memory);

        // 根節點不應有縮進
        $this->assertStringContainsString(basename($this->basePath), $text);

        $this->assertStringContainsString(basename($this->basePath), $text);
        $this->assertStringContainsString('  level1a', $text);
        $this->assertStringContainsString('  level1b', $text);
    }

    public function testToMarkdownList(): void
    {
        $memory      = $this->createTreeMemory();
        $transformer = new DirectoryTreeTransformer();
        $markdown    = $transformer->toMarkdownList($memory);

        // 根節點不應有 - 前綴
        $this->assertStringContainsString(basename($this->basePath), $markdown);

        // 子節點應有縮排與 - 前綴，這裡是預設 2 空格
        $this->assertStringContainsString('  - level1a', $markdown);
        $this->assertStringContainsString('  - level1b', $markdown);
    }

    public function testToHtmlList(): void
    {
        $memory      = $this->createTreeMemory();
        $transformer = new DirectoryTreeTransformer();
        $html        = $transformer->toHtmlList($memory);

        $root     = $memory->getRoot();
        $location = $root->getLocation();
        $path     = $root->getPath();

        $this->assertStringContainsString('<ul>', $html);
        $this->assertStringContainsString("<li data-location=\"{$location}\" data-path=\"{$path}\">" . basename($this->basePath), $html);
    }
}
