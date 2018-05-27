<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Handler;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Logs to a MongoDB database.
 *
 * usage example:
 *
 *   $log = new Logger('application');
 *   $mongodb = new MongoDBHandler(new \Mongo("mongodb://localhost:27017"), "logs", "prod");
 *   $log->pushHandler($mongodb);
 *
 * @author Thomas Tourlourat <thomas@tourlourat.com>
 */
class MysqlDBHandler extends AbstractProcessingHandler
{
    protected $pdo;

    public function __construct($pdo, $level = Logger::DEBUG, $bubble = true)
    {
        if(!$pdo instanceof \PDO) {
            throw new \InvalidArgumentException("is not a pdo object");
        }

        $this->pdo = $pdo;

        parent::__construct($level, $bubble);
    }

    protected function write(array $record)
    {
        if(!is_array($record) || empty($record)) {
            throw new \InvalidArgumentException("record is not an array or is empty");
        }
        $sql = <<<SQL
                insert into ApiRecord ('api_url', 'api_params', 'api_response', 'request_id', 'api_addtime') 
                values ({$record["api_url"]}, {$record["api_params"]}, {$record["api_response"]}, {$record["request_id"]}, {$record["api_addtime"]});
SQL;
        $this->pdo->exec($sql);
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter()
    {
        return new NormalizerFormatter();
    }
}