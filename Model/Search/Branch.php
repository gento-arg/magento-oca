<?php

namespace Gento\Oca\Model\Search;

use Magento\Framework\DataObject;

/**
 * @method Branch setQuery(string $query)
 * @method string|null getQuery()
 * @method bool hasQuery()
 * @method Branch setStart(int $startPosition)
 * @method int|null getStart()
 * @method bool hasStart()
 * @method Branch setLimit(int $limit)
 * @method int|null getLimit()
 * @method bool hasLimit()
 * @method Branch setResults(array $results)
 * @method array getResults()
 * @api
 * @since 100.0.2
 */
class Branch extends DataObject
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
