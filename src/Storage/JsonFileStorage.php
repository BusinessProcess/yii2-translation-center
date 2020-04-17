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

namespace Kialex\TranslateCenter\Storage;

use Translate\StorageManager\Contracts\TranslationStorage;
use yii\helpers\FileHelper;
use yii\i18n\JsonMessageSource;
use Yii;

class JsonFileStorage implements TranslationStorage
{
    /**
     * @var string
     */
    public $dir = '@common/messages';
    /**
     * @var string
     */
    public $defaultGroup = 'site';

    /**
     * @var string
     */
    private $fileExt = 'json';

    /**
     * @inheritDoc
     *
     * @throws \Exception when something went wrong
     */
    public function insert(string $key, string $value, string $lang, string $group = null): bool
    {
        $langDir = \Yii::getAlias($this->dir) . '/' . $lang;
        if (!file_exists($langDir)) {
            FileHelper::createDirectory($langDir);
        }

        $file = $langDir . '/' . ($group ? : $this->defaultGroup) . '.' . $this->fileExt;

        if ($handle = @fopen($file, 'r+b')) {
            fseek($handle, -2, SEEK_END);
            fwrite($handle, ',' . PHP_EOL . '    ');
            fwrite($handle, '"' . $key . '": ' . '"' . addcslashes(preg_replace('/\s+/', ' ',$value), '"\\') . '"');
            fwrite($handle, PHP_EOL . '}');
        } else {
            $handle = fopen($file, 'w+b');
            fwrite($handle, json_encode([$key => $value], JSON_PRETTY_PRINT));
        }

        fclose($handle);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function clearGroup(string $group, array $langs = null): bool
    {
        $dirs = FileHelper::findDirectories(\Yii::getAlias($this->dir), ['recursive' => false]);
        if (!$dirs) {
            return true;
        }

        foreach ($dirs as $dir) {
            $dirParts = explode(DIRECTORY_SEPARATOR, $dir);
            if ($langs && !in_array(end($dirParts), $langs)) {
                continue;
            }
            $file = $dir . '/' . $group . '.' . $this->fileExt;
            if (file_exists($file) && !unlink($file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     * @throws \yii\base\ErrorException
     */
    public function clear(array $langs = null): bool
    {
        $dirs = FileHelper::findDirectories(\Yii::getAlias($this->dir), ['recursive' => false]);
        if (!$dirs) {
            return true;
        }

        foreach ($dirs as $dir) {
            if ($langs && !in_array($dir, $langs)) {
                continue;
            }
            FileHelper::removeDirectory($dir);
        }

        return true;
    }

    /**
     * @inheritDoc
     *
     * @throws \Exception
     */
    public function find(string $key, string $lang, string $group): ?string
    {
        throw new \Exception('Unsupported method. Use Yii::t() instead');
    }

    /**
     * @inheritDoc
     *
     * @throws \Exception
     */
    public function delete(string $key): bool
    {
        throw new \Exception('Unsupported method.');
    }

    /**
     * @inheritDoc
     *
     * @throws \Exception
     */
    public function findByGroup(string $group, string $lang): array
    {
        $r = new \ReflectionMethod(JsonMessageSource::class, 'loadMessages');
        $r->setAccessible(true);
        $class = Yii::$app->i18n->getMessageSource($group);
        return $r->invoke($class, $group, $lang ?: \Yii::$app->language);
    }
}
