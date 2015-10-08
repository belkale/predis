<?php

/*
 * This file is part of the Predis package.
 *
 * (c) Daniele Alessandri <suppakilla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Predis\Cluster\Distributor;

/**
 * This class implements an hashring-based distributor that uses the same
 * algorithm of libketama to distribute keys in a cluster using client-side
 * sharding.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 * @author Lorenzo Castelli <lcastelli@gmail.com>
 */
class KetamaRing extends HashRing
{
    const DEFAULT_REPLICAS = 160;

    /**
     * @param int   $replicas         Number of replicas in the ring.
     * @param mixed $nodeHashCallback Callback returning a string used to calculate the hash of nodes.
     */
    public function __construct($replicas = self::DEFAULT_REPLICAS, $nodeHashCallback = null){
      parent::__construct($replicas, $nodeHashCallback);
    }
    /**
     * {@inheritdoc}
     */
    protected function addNodeToRing(&$ring, $node, $totalNodes, $replicas, $weightRatio)
    {
        $nodeObject = $node['object'];
        $nodeHash = $this->getNodeHash($nodeObject);
        $replicas = (int) floor($weightRatio * $totalNodes * $replicas);

        for ($i = 0; $i < $replicas; ++$i) {
            $key = $this->hash("$nodeHash-$i");
            $ring[$key] = $nodeObject;

        }
    }

    /**
     * {@inheritdoc}
     */
    public function hash($value)
    {
        $hash = unpack('V', md5($value, true));

        return $hash[1];
    }

    /**
     * {@inheritdoc}
     */
    protected function wrapAroundStrategy($upper, $lower, $ringKeysCount)
    {
        // Binary search for the first item in ringkeys with a value greater
        // or equal to the key. If no such item exists, return the first item.
        return $lower < $ringKeysCount ? $lower : 0;
    }
}
