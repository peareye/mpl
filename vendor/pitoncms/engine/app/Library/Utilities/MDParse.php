<?php

/**
 * PitonCMS (https://github.com/PitonCMS)
 *
 * @link      https://github.com/PitonCMS/Piton
 * @copyright Copyright (c) 2015 - 2019 Wolfgang Moritz
 * @license   https://github.com/PitonCMS/Piton/blob/master/LICENSE (MIT License)
 */

declare(strict_types=1);

namespace Piton\Library\Utilities;

use Parsedown;

/**
 * Piton Markdown Parser
 *
 * Modified parser to render single image lines without the paragraph tags
 * https://gist.github.com/fxck/d65255218de3611df3cd
 */
class MDParse extends Parsedown
{
    /**
     * Markdown image definition regex
     *
     * @var string
     */
    private $markdownImage = "~^!\[.*?\]\(.*?\)$~";

    /**
     * {@inheritdoc}
     */
    protected function paragraph($Line)
    {
        // Override if MD image tag
        if (1 === preg_match($this->markdownImage, $Line['text'])) {
            return $this->inlineImage($Line);
        }

        return parent::paragraph($Line);
    }
}
