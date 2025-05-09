<?php

declare(strict_types=1);

namespace Hizpark\DirectoryTree\Contract;

interface TreeMemoryInterface
{
    public function getRoot(): NodeInterface;

    /**
     * @return NodeInterface[]
     */
    public function getAncestors(NodeInterface $node): array;

    /**
     * @return NodeInterface[]
     */
    public function getSiblings(NodeInterface $node): array;

    /**
     * @return NodeInterface[]
     */
    public function getDescendants(NodeInterface $node): array;
}
