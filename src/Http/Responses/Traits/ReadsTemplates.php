<?php
/*
 * This file is part of nattvara/detectify-guestbook.
 *
 * (c) Ludwig Kristoffersson <ludwig@kristoffersson.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Guestbook\Http\Responses\Traits;

use Guestbook\Http\Responses\Exceptions\TemplateException;

trait ReadsTemplates {

    /**
     * Resource directory
     *
     * @var string
     */
    protected static $resourceDir = '';

    /**
     * Create new html response
     *
     * @param string|null $template
     */
    public function __construct(?string $template = null) {
        if (!$template) {
            $this->setResponseBody('');
            return;
        }
        $this->setResponseBody($this->readTemplate($template));
    }

    /**
     * Set the resource directory to load html from
     *
     * @param string $resourceDir
     * @return void
     */
    public static function setResourceDirectory(string $resourceDir) {
        self::$resourceDir = $resourceDir;
    }

    /**
     * Read template
     *
     * @param  string $template
     * @return string           html
     * @throws TemplateException
     */
    protected function readTemplate(string $template): string {
        $file = sprintf('%s/%s', self::$resourceDir, $template);
        if (!file_exists($file)) {
            throw new TemplateException(sprintf('Unkown template \'%s\'', $template));
        }
        $fp     = fopen($file, 'r');
        $html   = fread($fp, filesize($file));
        fclose($fp);
        return $html;
    }

}
