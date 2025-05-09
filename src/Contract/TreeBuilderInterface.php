<?php

declare(strict_types=1);

namespace Hizpark\DirectoryTree\Contract;

interface TreeBuilderInterface
{
    public function build(NodeInterface $root): TreeMemoryInterface;
}
