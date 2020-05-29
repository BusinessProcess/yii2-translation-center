<?php

namespace Kialex\TranslateCenter\Source;

use yii\i18n\MessageSource;
use Kialex\TranslateCenter\Storage\RedisStorage;

class RedisSource extends MessageSource
{
    /**
     * @var RedisStorage
     */
    protected $storage;

    /**
     * RedisSource constructor.
     * @param RedisStorage $storage
     * @param array $config
     */
    public function __construct(RedisStorage $storage, $config = [])
    {
        $this->storage = $storage;
        parent::__construct($config);
    }

    /**
     * @inheritDoc
     */
    protected function loadMessages($category, $language)
    {
        return $this->storage->findByGroup($category, $language);
    }
}