<?php

declare(strict_types=1);

namespace Clouding\Presto;

use Clouding\Presto\Collectors\AssocCollector;
use Clouding\Presto\Collectors\Collector;
use Tightenco\Collect\Support\Collection;

class QueryBuilder
{
    /**
     * The processor for query.
     *
     * @var \Clouding\Presto\Processor
     */
    protected $processor;

    /**
     * The raw of query.
     *
     * @var string
     */
    protected $raw = '';

    /**
     * Create a new query builder instance.
     *
     * @param \Clouding\Presto\Processor $processor
     */
    public function __construct(Processor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * Set raw query.
     *
     * @param  string  $query
     * @return \Clouding\Presto\QueryBuilder
     */
    public function raw(string $query): QueryBuilder
    {
        $this->raw = $query;

        return $this;
    }

    /**
     * Execute the query statement.
     *
     * @return \Tightenco\Collect\Support\Collection
     */
    public function get(): Collection
    {
        return $this->processor->execute($this->toSql(), new Collector());
    }

    /**
     * Execute the query statement with assoc column.
     *
     * @return \Tightenco\Collect\Support\Collection
     */
    public function getAssoc(): Collection
    {
        return $this->processor->execute($this->toSql(), new AssocCollector());
    }

    /**
     * Get raw query statement.
     *
     * @return string
     */
    public function toSql(): string
    {
        return $this->raw;
    }
}
