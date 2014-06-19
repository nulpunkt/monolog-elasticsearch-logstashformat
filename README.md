# Elasticsearch with logstash formatter

This handler lets you put logs into Elasticsearch in the Logstash format, 
which makes visualization with Kibana very easy.

## Recommended setup

```php
$client = new Elasticsearch\Client(['hosts' => ['http://example.com:9200']]);
$formatter = new Monolog\Formatter\LogstashFormatter('application', null, null, '', 1);
$handler = new Monolog\ElasticLogstashHandler($client, ['type' => 'invoicing-logs']);
$handler->setFormatter($formatter);


$log = new Monolog\Logger('invoicing');
$log->pushHandler($handler);
$log->warn('new sale', ['user_id' => 42, 'product_id' => 7537]);
```
