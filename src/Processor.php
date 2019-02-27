<?php

declare(strict_types=1);

namespace Clouding\Presto;

use Clouding\Presto\Connection\Connection;
use Clouding\Presto\Contracts\Collectorable;
use Clouding\Presto\Exceptions\ProcessorException;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Tightenco\Collect\Support\Collection;

class Processor
{
    /**
     * The statement uri.
     *
     * @var string
     */
    const STATEMENT_URI = '/v1/statement';

    /**
     * Resend request sleep microseconds.
     *
     * @var int
     */
    const SLEEP = 50000;

    /**
     * The state of failed.
     *
     * @var string
     */
    const FAILED = 'FAILED';

    /**
     * The connection information.
     *
     * @var \Clouding\Presto\Connection\Connection
     */
    protected $connection;

    /**
     * Http client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Response next uri.
     *
     * @var ?string
     */
    protected $nextUri = '';

    /**
     * Collect response data.
     *
     * @var \Clouding\Presto\Contracts\Collectorable
     */
    protected $collector;

    /**
     * Create a new instance.
     *
     * @param \Clouding\Presto\Connection\Connection  $connection
     * @param \GuzzleHttp\Client|null                 $client
     */
    public function __construct(Connection $connection, Client $client = null)
    {
        $this->connection = $connection;
        $this->client = $client ?? new Client();
    }

    /**
     * Handle connection query.
     *
     * @param  string                                    $query
     * @param  \Clouding\Presto\Contracts\Collectorable  $collector
     * @return \Tightenco\Collect\Support\Collection
     */
    public function execute(string $query, Collectorable $collector): Collection
    {
        $this->collector = $collector;

        $this->resolve($this->sendQuery($query));

        while ($this->continue()) {
            usleep(static::SLEEP);

            $this->resolve($this->sendNext());
        }

        return $this->collector->get();
    }

    /**
     * Send query request.
     *
     * @param  string  $query
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function sendQuery(string $query): ResponseInterface
    {
        $baseUri = $this->connection->getHost() . static::STATEMENT_URI;
        $headers = [
            'X-Presto-User' => $this->connection->getUser(),
            'X-Presto-Schema' => $this->connection->getSchema(),
            'X-Presto-Catalog' => $this->connection->getCatalog(),
        ];

        return $this->client->post($baseUri, ['headers' => $headers, 'body' => $query]);
    }

    /**
     * Send next query.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function sendNext(): ResponseInterface
    {
        return $this->client->get($this->nextUri);
    }

    /**
     * Resolve response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    protected function resolve(ResponseInterface $response)
    {
        $contents = json_decode($response->getBody()->getContents());

        $this->checkState($contents);

        $this->setNextUri($contents);

        $this->collector->collect($contents);
    }

    /**
     * Check response state.
     *
     * @param  object  $contents
     *
     * @throws \Clouding\Presto\Exceptions\ProcessorException
     */
    protected function checkState(object $contents)
    {
        if ($contents->stats->state === self::FAILED) {
            $message = "{$contents->error->errorName}: {$contents->error->message}";
            throw new ProcessorException($message);
        }
    }

    /**
     * Set next uri.
     *
     * @param  object  $contents
     */
    protected function setNextUri(object $contents)
    {
        $this->nextUri = $contents->nextUri ?? null;
    }

    /**
     * Determine if next uri is set or not.
     *
     * @return bool
     */
    protected function continue(): bool
    {
        return isset($this->nextUri);
    }
}
