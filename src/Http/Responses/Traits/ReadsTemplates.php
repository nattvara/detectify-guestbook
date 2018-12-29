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
     * Create new response
     *
     * @param string|null $template
     * @param array       $variables
     */
    public function __construct(?string $template = null, array $variables = []) {
        if (!$template) {
            $this->setResponseBody('');
            return;
        }
        $template = $this->readTemplate($template);
        foreach ($variables as $name => $value) {
            $template = str_replace(
                sprintf('{{%s}}', $name),
                htmlspecialchars($value),
                $template
            );
        }
        $this->setResponseBody($template);
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
