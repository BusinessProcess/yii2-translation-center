<?php

namespace Kialex\TranslateCenter\Storage;

use Pervozdanniy\TranslationStorage\Contracts\Bulk as BulkActions;
use Pervozdanniy\TranslationStorage\Contracts\Storage\StaticStorage as TranslationStorage;
use yii\base\BaseObject;
use yii\di\Instance;
use yii\redis\Connection;

class RedisStorage extends BaseObject implements TranslationStorage, BulkActions
{
    /**
     * @var string|Connection the Redis [[Connection]] object or the application component ID of the Redis [[Connection]].
     */
    public $redis = 'redis';

    /**
     * Initializes the redis Cache component.
     * This method will initialize the [[redis]] property to make sure it refers to a valid redis connection.
     * @throws \yii\base\InvalidConfigException if [[redis]] is invalid.
     */
    public function init()
    {
        parent::init();
        $this->redis = Instance::ensure($this->redis, Connection::className());
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, string $value, string $lang, string $group): bool
    {
        return (bool)$this->redis->executeCommand('SET', [$this->buildKey($key, $lang, $group), $value]);
    }

    /**
     * @inheritDoc
     */
    public function bulkInsert(array $data): bool
    {
        $options = [];
        $keys = array_walk($data, function ($dataInfo) use (&$options) {
            array_push($options, $this->buildKey($dataInfo['key'], $dataInfo['lang'], $dataInfo['group']));
            array_push($options, $dataInfo['value']);
        });
        if ($options) {
            return $this->redis->executeCommand('MSET', $options);
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function find(string $key, string $lang, string $group): ?string
    {
        return $this->redis->executeCommand('GET', [$this->buildKey($key, $lang, $group)]);
    }

    /**
     * @inheritDoc
     */
    public function findByGroup(string $group, string $lang): array
    {
        $keys = $this->redis->executeCommand('KEYS', ["$lang\:$group\:*"]);
        if (!$keys) {
            return [];
        }
        $values = $this->redis->executeCommand('MGET', $keys);
        $keys = array_map(function ($key) {
            return substr(strrchr($key, ':'), 1);
        }, $keys);
        return array_combine($keys, $values);
    }

    /**
     * @inheritDoc
     */
    public function clearGroup(string $group, array $langs = null): bool
    {
        if (!$langs) {
            $keys = $this->redis->executeCommand('KEYS', ["*\:$group\:*"]);
        } else {
            $keys = [];
            foreach ($langs as $lang) {
                $langKeys = $this->redis->executeCommand('KEYS', ["$lang\:$group\:*"]);
                array_walk($langKeys, function ($key) use (&$keys) {
                    array_push($keys, $key);
                });
            }
        }

        if ($keys) {
            return $this->redis->executeCommand('DEL', $keys);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function clear(array $langs = null): bool
    {
        if (!$langs) {
            $keys = $this->redis->executeCommand('KEYS', ["*\:*\:*"]);
        } else {
            $keys = [];
            foreach ($langs as $lang) {
                $langKeys = $this->redis->executeCommand('KEYS', ["$lang\:*\:*"]);
                array_walk($langKeys, function ($key) use (&$keys) {
                    array_push($keys, $key);
                });
            }
        }

        if ($keys) {
            return $this->redis->executeCommand('DEL', $keys);
        }

        return true;
    }

    /**
     * Build key
     * @param string $key
     * @param string $lang
     * @param string $group
     * @return string
     */
    protected function buildKey(string $key, string $lang, string $group): string
    {
        return "$lang:$group:$key";
    }
}
