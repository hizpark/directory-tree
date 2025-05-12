<?php

declare(strict_types=1);

namespace Hizpark\DirectoryTree\Memory;

use Hizpark\DirectoryTree\Node\NodeInterface;

class DirectoryTreeMemory implements TreeMemoryInterface
{
    private NodeInterface $root;

    public function __construct(NodeInterface $root)
    {
        $this->root = $root;
    }

    public function getRoot(): NodeInterface
    {
        return $this->root;
    }

    /**
     * @return NodeInterface[]
     */
    public function getAncestors(NodeInterface $node): array
    {
        $ancestors = [];

        while ($node = $node->getParent()) {
            $ancestors[] = $node;
        }

        return $ancestors;
    }

    /**
     * @return NodeInterface[]
     */
    public function getSiblings(NodeInterface $node): array
    {
        $parent = $node->getParent();

        if ($parent === null) {
            return [];
        }

        $children = $parent->getChildren();

        if (is_array($children)) {
            return array_filter(
                $children,
                fn (NodeInterface $sibling) => $sibling->getLocation() !== $node->getLocation(),
            );
        }

        return [];
    }

    /**
     * @return NodeInterface[]
     */
    public function getDescendants(NodeInterface $node): array
    {
        return $this->collectDescendants($node);
    }

    /**
     * @return NodeInterface[]
     */
    private function collectDescendants(NodeInterface $node): array
    {
        $descendants = [];
        $queue       = [$node];

        while (!empty($queue)) {
            /** @var NodeInterface $current */
            $current = array_shift($queue);

            $children = $current->getChildren();

            if (is_array($children)) {
                foreach ($children as $child) {
                    $descendants[] = $child;
                    $queue[]       = $child;
                }
            }
        }

        return $descendants;
    }
}
