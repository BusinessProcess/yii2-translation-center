<?php
/**
 * ---------------------------------------------------------------------
 * @author Vladislav Dneprov <vladislav.dneprov1995@gmail.com>
 * @link https://www.linkedin.com/in/vladislav-dneprov/ Linkedin profile
 * @link https://github.com/kialex Github
 * ---------------------------------------------------------------------
 *
 * @version 1.0.0
 * @package express-test
 */

namespace Kialex\TranslateCenter\Console\Controllers;

use Kialex\TranslateCenter\{Client, Console\Tracker\PullProgressTracker, Storage\JsonFileStorage};
use Kialex\TranslateCenter\ParcerTranslateStorage;
use Translate\StorageManager\Contracts\TranslationStorage;
use Translate\StorageManager\Manager;
use Translate\StorageManager\Storage\ElasticStorage;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\{Controller, ExitCode};
use yii\helpers\Console;

class TranslationCenterController extends Controller
{
    /**
     * List of lang code where is localy code and value is code in Translation Center
     */
    const LANG_CODE = [
        'ru' => 'ru', // It is primary language!! Put your primary language first!!
        'de' => 'de', 'en' => 'en', 'es' => 'es', 'el' => 'el', 'he' => 'he', 'it' => 'it', 'lt' => 'lt', 'mn' => 'mn',
        'pl' => 'pl', 'ar' => 'ar', 'sr' => 'sr', 'uk' => 'uk', 'zh' => 'zh', 'tr' => 'tr'
    ];

    public $staticSources = ['static_1', 'static_2', 'static_3'];
    public $dynamicSources = ['dynamic_1', 'dynamic_2', 'dynamic_3'];

    /**
     * Pull all static content from Translation Center
     *
     * @return int
     * @throws InvalidConfigException
     * @throws \Translate\StorageManager\Response\Exception
     */
    public function actionPullStaticSources()
    {
        return $this->pull($this->staticSources, Yii::createObject(JsonFileStorage::class));
    }

    /**
     * @param string[] $groups
     * @param TranslationStorage $storage
     *
     * @return int
     * @throws InvalidConfigException
     * @throws \Translate\StorageManager\Response\Exception
     */
    protected function pull($groups, TranslationStorage $storage)
    {
        $manager = new Manager(Yii::createObject(Client::class), $storage, new ParcerTranslateStorage());

        try {
            foreach ($groups as $group) {
                $manager
                    ->setTracker(new PullProgressTracker())
                    ->updateGroup($group, array_values(self::LANG_CODE));
            }
        } catch (\Exception $e) {
            Console::endProgress('Fail: ' . $e->getMessage() . PHP_EOL);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * Pull all dynamic content from Translation Center
     *
     * @return int
     * @throws InvalidConfigException
     * @throws \Translate\StorageManager\Response\Exception
     */
    public function actionPullDynamicSources()
    {
        return $this->pull($this->dynamicSources, Yii::createObject(ElasticStorage::class));
    }
}