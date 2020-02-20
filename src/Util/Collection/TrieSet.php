<?php

namespace EzAd\Util\Collection;

/**
 * ### IGNORE THIS CLASS, THIS WAS A WASTE OF TIME, PHP VARIABLE OVERHEAD MAKES THIS WORSE ###
 * ### Will probably use some type of URL compression + hashing to reduce robot memory use ###
 *
 * Stores a unique set of strings in a trie structure for memory savings. Great for detecting visited
 * URLs in bots since they almost always share similar prefixes.
 *
 * Looking at a possible implementation, instead of splitting on characters like normal tries,
 * it will support custom splits that you send in an array:
 *
 * -- http://www.truevalue.com/Lawn/Chair.aspx
 * $trie = new TrieSet();
 * $trie->add(['http://www.truevalue.com', 'Lawn', 'Chair.aspx'])
 *
 * @package EzAd\Util\Collection
 */
class TrieSet
{
    /**
     * @var \stdClass
     */
    private $root;

    public function __construct()
    {
        $this->root = new \stdClass();
        $this->root->children = [];
    }

    public function add(array $key)
    {
        $node = $this->root;
        $len = count($key);

        for ( $i = 0; $i < $len; $i++ ) {
            $k = $key[$i];
            if ( isset($node->children[$k]) ) {
                $node = $node->children[$k];
            } else {
                $node->children[$k] = new \stdClass();
                $node = $node->children[$k];
                if ( $i < $len - 1 ) {
                    $node->children = [];
                }
            }
        }

        $node->value = true;
    }

    public function contains(array $key)
    {
        $node = $this->root;

        foreach ( $key as $k ) {
            if ( isset($node->children[$k]) ) {
                $node = $node->children[$k];
            } else {
                return false;
            }
        }

        return $node->value;
    }

    public function export($file)
    {
        file_put_contents($file, serialize($this->root));
    }
}
