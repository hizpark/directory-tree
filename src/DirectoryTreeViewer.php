<?php

declare(strict_types=1);

namespace Hizpark\DirectoryTree;

use Hizpark\DirectoryTree\Exception\DirectoryTreeException;

class DirectoryTreeViewer
{
    public const FORMAT_TEXT_TREE     = 1;
    public const FORMAT_TEXT_INDENTED = 2;
    public const FORMAT_MARKDOWN_LIST = 3;
    public const FORMAT_HTML_LIST     = 4;

    public function render(string $path, int $format = self::FORMAT_TEXT_TREE): string
    {
        if (!is_dir($path)) {
            throw new DirectoryTreeException("Provided path is not a directory: {$path}");
        }

        $path        = rtrim($path, DIRECTORY_SEPARATOR);
        $tree        = (new DirectoryTreeBuilder())->build(new DirectoryNode($path, null));
        $transformer = new DirectoryTreeTransformer();

        return match ($format) {
            self::FORMAT_TEXT_TREE     => $transformer->toTextTree($tree),
            self::FORMAT_TEXT_INDENTED => $transformer->toTextIndented($tree),
            self::FORMAT_MARKDOWN_LIST => $transformer->toMarkdownList($tree),
            self::FORMAT_HTML_LIST     => $transformer->toHtmlList($tree),
            default                    => throw new DirectoryTreeException("Invalid format: {$format}. Use FORMAT_* constants"),
        };
    }
}
