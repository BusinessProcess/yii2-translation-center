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

namespace Kialex\TranslateCenter\Console\Tracker;

use Pervozdanniy\TranslationStorage\Contracts\ProgressTracker;
use yii\helpers\Console;

class PullProgressTracker implements ProgressTracker
{
    private $currentDone = 0;

    /**
     * @inheritDoc
     */
    public function beforeStart(): void
    {
        Console::startProgress(0, 100, 'Calculations number of items ...', false);
    }

    /**
     * @inheritDoc
     */
    public function afterFinish(): void
    {
        Console::endProgress();
    }

    /**
     * @inheritDoc
     */
    public function afterBatch(array $response): void
    {
        $this->currentDone += count($response['items']);
        $total = $response['meta']['totalItems'];
        Console::updateProgress($this->currentDone, $total, 'Progress: ');
    }

    /**
     * @inheritDoc
     */
    public function beforeBatch(int $page): void
    {
    }
}
