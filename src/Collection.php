<?php declare(strict_types=1);


namespace Jeekens\Basics;


use Countable;
use ArrayAccess;
use Traversable;
use ArrayIterator;
use IteratorAggregate;
use Jeekens\Basics\Spl\Arrayable;

class Collection implements ArrayAccess, Countable, IteratorAggregate, Arrayable
{

    /**
     * @var array
     */
    protected $items;


    public function __construct($items = [])
    {
        $this->items = $this->getItemsToArray($items);
    }


    public function count()
    {
        return count($this->items);
    }


    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }


    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }


    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }


    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }


    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }


    protected function getItemsToArray($items)
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof Arrayable) {
            return $items->toArray();
        } elseif ($items instanceof Traversable) {
            return iterator_to_array($items);
        }

        return (array) $items;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, $this->items);
    }

    /**
     * @param $items
     *
     * @return Collection
     */
    public function diff($items)
    {
        return new static(array_diff($this->items, $this->getItemsToArray($items)));
    }

    /**
     * @param $items
     * @param callable $callback
     *
     * @return Collection
     */
    public function diffUsing($items, callable $callback)
    {
        return new static(array_udiff($this->items, $this->getItemsToArray($items), $callback));
    }

    /**
     * @param $items
     *
     * @return Collection
     */
    public function diffAssoc($items)
    {
        return new static(array_diff_assoc($this->items, $this->getItemsToArray($items)));
    }

    /**
     * @param $items
     * @param callable $callback
     *
     * @return Collection
     */
    public function diffAssocUsing($items, callable $callback)
    {
        return new static(array_diff_uassoc($this->items, $this->getItemsToArray($items), $callback));
    }

    /**
     * @param $items
     *
     * @return Collection
     */
    public function diffKeys($items)
    {
        return new static(array_diff_key($this->items, $this->getItemsToArray($items)));
    }

    /**
     * @param $items
     * @param callable $callback
     *
     * @return Collection
     */
    public function diffKeysUsing($items, callable $callback)
    {
        return new static(array_diff_ukey($this->items, $this->getItemsToArray($items), $callback));
    }

    /**
     * @param callable $callback
     *
     * @return $this
     */
    public function each(callable $callback)
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }
        return $this;
    }

    /**
     * @param callable|null $callback
     *
     * @return Collection
     */
    public function filter(callable $callback = null)
    {
        if ($callback) {
            return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
        }

        return new static(array_filter($this->items));
    }

    /**
     * @param $value
     * @param callable $callback
     * @param callable|null $default
     *
     * @return $this
     */
    public function when($value, callable $callback, callable $default = null)
    {
        if ($value) {
            return $callback($this, $value);
        } elseif ($default) {
            return $default($this, $value);
        }

        return $this;
    }

    /**
     * @param $value
     * @param callable $callback
     * @param callable|null $default
     *
     * @return Collection
     */
    public function unless($value, callable $callback, callable $default = null)
    {
        return $this->when(! $value, $callback, $default);
    }

    /**
     * @return Collection
     */
    public function flip()
    {
        return new static(array_flip($this->items));
    }

    /**
     * @param $items
     *
     * @return Collection
     */
    public function intersect($items)
    {
        return new static(array_intersect($this->items, $this->getItemsToArray($items)));
    }

    /**
     * @param $items
     *
     * @return Collection
     */
    public function intersectByKeys($items)
    {
        return new static(array_intersect_key(
            $this->items, $this->getItemsToArray($items)
        ));
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * @return bool
     */
    public function isNotEmpty()
    {
        return ! $this->isEmpty();
    }

    /**
     * @return Collection
     */
    public function keys()
    {
        return new static(array_keys($this->items));
    }

    /**
     * @param callable $callback
     *
     * @return Collection
     */
    public function map(callable $callback)
    {
        $keys = array_keys($this->items);
        $items = array_map($callback, $this->items, $keys);

        return new static(array_combine($keys, $items));
    }

    /**
     * @param $items
     *
     * @return Collection
     */
    public function merge($items)
    {
        return new static(array_merge($this->items, $this->getItemsToArray($items)));
    }

    /**
     * @param $items
     *
     * @return Collection
     */
    public function mergeRecursive($items)
    {
        return new static(array_merge_recursive($this->items, $this->getItemsToArray($items)));
    }

    /**
     * @param $values
     *
     * @return Collection
     */
    public function combine($values)
    {
        return new static(array_combine($this->all(), $this->getItemsToArray($values)));
    }

    /**
     * @param $keys
     *
     * @return Collection
     */
    public function only($keys)
    {
        if (is_null($keys)) {
            return new static($this->items);
        }

        if ($keys instanceof Arrayable) {
            $keys = $keys->toArray();
        }

        $keys = is_array($keys) ? $keys : func_get_args();

        return new static(Arr::only($this->items, $keys));
    }

    /**
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->items);
    }

    /**
     * @param $limit
     *
     * @return mixed
     */
    public function take($limit)
    {
        if ($limit < 0) {
            return $this->slice($limit, abs($limit));
        }
        return $this->slice(0, $limit);
    }

    /**
     * @param $offset
     * @param null $length
     *
     * @return Collection
     */
    public function slice($offset, $length = null)
    {
        return new static(array_slice($this->items, $offset, $length, true));
    }

    /**
     * @param $source
     *
     * @return Collection
     */
    public function concat($source)
    {
        $result = new static($this);

        foreach ($source as $item) {
            $result->push($item);
        }

        return $result;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function push($value)
    {
        $this->offsetSet(null, $value);

        return $this;
    }

    /**
     * @param $items
     *
     * @return Collection
     */
    public function replace($items)
    {
        return new static(array_replace($this->items, $this->getItemsToArray($items)));
    }

    /**
     * @param bool $preserve_keys
     *
     * @return Collection
     */
    public function reverse(bool $preserve_keys = false)
    {
        return new static(array_reverse($this->items, $preserve_keys));
    }

    /**
     * @param $items
     *
     * @return Collection
     */
    public function replaceRecursive($items)
    {
        return new static(array_replace_recursive($this->items, $this->getItemsToArray($items)));
    }

    /**
     * @return mixed
     */
    public function shift()
    {
        return array_shift($this->items);
    }

    /**
     * @param callable|null $callback
     *
     * @return Collection
     */
    public function sort(callable $callback = null)
    {
        $items = $this->items;
        $callback
            ? uasort($items, $callback)
            : asort($items);
        return new static($items);
    }

    /**
     * @param int $options
     * @param bool $descending
     *
     * @return Collection
     */
    public function sortKeys($options = SORT_REGULAR, $descending = false)
    {
        $items = $this->items;
        $descending ? krsort($items, $options) : ksort($items, $options);

        return new static($items);
    }

    /**
     * @return Collection
     */
    public function values()
    {
        return new static(array_values($this->items));
    }

    /**
     * @param $size
     * @param $value
     *
     * @return Collection
     */
    public function pad($size, $value)
    {
        return new static(array_pad($this->items, $size, $value));
    }

}