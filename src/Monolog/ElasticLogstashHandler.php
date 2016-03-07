<?php

namespace Monolog;

class ElasticLogstashHandler extends \Monolog\Handler\AbstractProcessingHandler
{
    /**
     * @param Client  $client   ElasticSearch Client object
     * @param array   $options  Handler configuration
     * @param integer $level    The minimum logging level at which this handler will be triggered
     * @param Boolean $bubble   Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct($client, array $options = array(), $level = \Monolog\Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->client = $client;
        $this->options = array_merge(
            array(
                'index'          => 'logstash-'.date('Y.m.d'),      // Elastic index name
                'type'           => 'logs',       // Elastic document type
                'ignore_error'   => false,          // Suppress exceptions
            ),
            $options
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function write(array $record)
    {
        try {
            $this->client->index(
                [
                    'index' => $this->options['index'],
                    'type' => $this->options['type'],
                    'timeout' => '50ms',
                    'body' => json_decode($record['formatted'], true)
                ]
            );
        } catch (\Exception $e) {
            // Well that didn't pan out...
			if (!$this->options['ignore_error']) {
				throw $e;
			}
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatter(\Monolog\Formatter\FormatterInterface $formatter)
    {
        return parent::setFormatter($formatter);
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter()
    {
        return new \Monolog\Formatter\LogstashFormatter('');
    }

    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records)
    {
        foreach ($records as $record) {
            $this->write($records);
        }
    }
}
