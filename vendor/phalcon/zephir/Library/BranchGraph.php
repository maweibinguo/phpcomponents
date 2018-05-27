<?php

/*
 +--------------------------------------------------------------------------+
 | Zephir                                                                   |
 | Copyright (c) 2013-present Zephir Team (https://zephir-lang.com/)        |
 |                                                                          |
 | This source file is subject the MIT license, that is bundled with this   |
 | package in the file LICENSE, and is available through the world-wide-web |
 | at the following url: http://zephir-lang.com/license.html                |
 +--------------------------------------------------------------------------+
*/

namespace Zephir;

/**
 * BranchGraph
 *
 * Represents a group of branch nodes
 */
class BranchGraph
{
    protected $root;

    protected $branchMap;

    /**
     * Adds a leaf to the branch tree
     *
     * @param Branch $branch
     */
    public function addLeaf(Branch $branch)
    {
        if (isset($this->branchMap[$branch->getUniqueId()])) {
            $branchNode = $this->branchMap[$branch->getUniqueId()];
        } else {
            $branchNode = new BranchGraphNode($branch);
        }
        $branchNode->increase();

        $tempBranch = $branch->getParentBranch();
        while ($tempBranch) {
            if (isset($this->branchMap[$tempBranch->getUniqueId()])) {
                $parentBranchNode = $this->branchMap[$tempBranch->getUniqueId()];
            } else {
                $parentBranchNode = new BranchGraphNode($tempBranch);
                $this->branchMap[$tempBranch->getUniqueId()] = $parentBranchNode;
            }
            $parentBranchNode->insert($branchNode);
            $branchNode = $parentBranchNode;
            $tempBranch = $tempBranch->getParentBranch();
            if (!$tempBranch) {
                $this->root = $parentBranchNode;
            }
        }
    }

    /**
     * Returns the tree's root node
     *
     * @return BranchGraphNode
     */
    public function getRoot()
    {
        return $this->root;
    }
}