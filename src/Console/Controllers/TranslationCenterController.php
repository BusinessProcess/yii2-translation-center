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
use Pervozdanniy\TranslationStorage\Manager\DynamicManager;
use Pervozdanniy\TranslationStorage\Manager\StaticManager;
use Translate\StorageManager\Contracts\Storage as TranslationStorage;
use Translate\StorageManager\Manager;
use Translate\StorageManager\Storage\ElasticStorage;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\{Controller, ExitCode};
use yii\helpers\Console;

class TranslationCenterController extends Controller
{
    /**
     * @var array list of lang code where is localy code and value is code in Translation Center
     */
    public $langCodes = [
        'ru' => 'ru',
        'de' => 'de', 'en' => 'en', 'es' => 'es', 'el' => 'el', 'he' => 'he', 'it' => 'it', 'lt' => 'lt', 'mn' => 'mn',
        'pl' => 'pl', 'ar' => 'ar', 'sr' => 'sr', 'uk' => 'uk', 'zh' => 'zh', 'tr' => 'tr'
    ];

    /**
     * @var array list of static groups to update.
     */
    public $staticGroups = [];

    /**
     * @var array list of dynamic groups to update.
     */
    public $dynamicGroups = [];

    /**
     * Pull all static content from Translation Center
     *
     * @return int
     * @throws InvalidConfigException
     * @throws \Translate\StorageManager\Response\Exception
     */
    public function actionPullStaticSources()
    {
        return $this->pull($this->staticGroups);
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
        return $this->pull($this->dynamicGroups, false);
    }

    /**
     * @param string[] $groups
     * @param TranslationStorage $storage
     * @param bool $static
     *
     * @return int
     * @throws InvalidConfigException
     * @throws \Translate\StorageManager\Response\Exception
     */
    protected function pull($groups, $static = true)
    {
        $manager = Yii::createObject($static ? StaticManager::class : DynamicManager::class);

        try {
            foreach ($groups as $group) {
                $manager
                    ->setTracker(new PullProgressTracker())
                    ->updateGroup($group, array_values($this->langCodes));
            }
        } catch (\Exception $e) {
            var_dump($e);
            Console::endProgress('Fail: ' . $e->getMessage() . PHP_EOL);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}