<?php

namespace App\CustomClass;

class CustomFunctions
{

    public static function EmailContentParser($content, $data)
    {$content = str_replace('{{ ', '{{', $content);
        $content = str_replace(' }}', '}}', $content);
        $parsed = preg_replace_callback('/{{(.*?)}}/', function ($matches) use ($data) {
            list($shortCode, $index) = $matches;

            if (isset($data[$index])) {
                return $data[$index];
            } else {
                return '';
                //throw new Exception("Shortcode {$shortCode} not found in template id {$this->id}", 1);
            }

        }, $content);

        return $parsed;
    }

}
