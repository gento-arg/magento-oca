<?php

namespace Gento\Oca\Model\Search;

use Magento\Framework\DataObject;

/**
 * @method Operatory setQuery(string $query)
 * @method string|null getQuery()
 * @method bool hasQuery()
 * @method Operatory setStart(int $startPosition)
 * @method int|null getStart()
 * @method bool hasStart()
 * @method Operatory setLimit(int $limit)
 * @method int|null getLimit()
 * @method bool hasLimit()
 * @method Operatory setResults(array $results)
 * @method array getResults()
 * @api
 * @since 100.0.2
 */
class Operatory extends DataObject
{
    /**
     * Load search results
     *
     * @return $this
     */
    public function load()
    {
        $result = [];
        $this->setResults($result);
        return $this;
    }
}
