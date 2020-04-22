<?php

namespace Kialex\TranslateCenter;

use Translate\StorageManager\Response\Parser;

class ParcerTranslateStorage extends Parser
{
    /**
     * @inheritDoc
     */
    public function parseBody(array $response): array
    {
        $body = [];
        $group = reset($response['meta']['tags']);
        foreach ($response['items'] as $item) {
            if (is_array($item['value'])) {
                foreach ($item['value'] as $lang => $value) {
                    $body[] = [
                        'key' => $item['key'],
                        'value' => $value,
                        'lang' => $lang,
                        'group' => $group
                    ];
                }
            } else {
                $body[] = [
                    'key' => $item['key'],
                    'value' => $item['value'],
                    'lang' => reset($response['meta']['langs']), // Get group from meta
                    'group' => $group
                ];
            }
        }

        return $body;
    }
}