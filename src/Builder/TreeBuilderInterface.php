<?php

declare(strict_types=1);

namespace Hizpark\DirectoryTree\Builder;

use Hizpark\DirectoryTree\Memory\TreeMemoryInterface;
use Hizpark\DirectoryTree\Node\NodeInterface;

interface TreeBuilderInterface
{
    public function build(NodeInterface $root): TreeMemoryInterface;
}
