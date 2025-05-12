<?php

declare(strict_types=1);

namespace Hizpark\DirectoryTree\Viewer;

use Hizpark\DirectoryTree\Builder\DirectoryTreeBuilder;
use Hizpark\DirectoryTree\Exception\DirectoryTreeException;
use Hizpark\DirectoryTree\Node\DirectoryNode;
use Hizpark\DirectoryTree\Transformer\DirectoryTreeTransformer;

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
            default                    => throw new DirectoryTreeException(
                "Invalid format: {$format}. Allowed values: 1 (TEXT_TREE), 2 (TEXT_INDENTED), 3 (MARKDOWN_LIST), 4 (HTML_LIST)",
            ),
        };
    }
}
