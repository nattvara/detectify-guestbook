<?php
/*
 * This file is part of nattvara/detectify-guestbook.
 *
 * (c) Ludwig Kristoffersson <ludwig@kristoffersson.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Guestbook\Http\Validation\Rules;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Pwnedpasswords extends Rule {

    /**
     * @var Client
     */
    private $client;

    /**
     * Execute rule on request input
     *
     * @param  array  $requestInput
     * @return void
     */
    public function execute(array $requestInput) {

        $sha1 = hash('sha1', $requestInput[$this->field]);

        $hashes = $this->fetchHashes(substr($sha1, 0, 5));
        if (!$hashes) {
            $this->pass();
            return;
        }

        $found = false;
        foreach ($hashes as $hash) {
            if (strtolower($hash['sha1']) === strtolower($sha1)) {
                $found = $hash;
            }
        }

        if (!$found) {
            $this->pass();
            return;
        }

        if ($found['count'] > 1000) {
            $this->fail(Rule::SEVERITY_DANGER, '"{{field}}" was securely tested against real world data-leaks and your password apears to be really bad. Please consider another one. Read more at https://haveibeenpwned.com');
            return;
        }

        if ($found['count'] > 100) {
            $this->fail(Rule::SEVERITY_WARNING, '"{{field}}" was securely tested against real world data-leaks and your password apears to be weak. Please consider another one. Read more at https://haveibeenpwned.com');
            return;
        }

        $this->pass();
    }

    /**
     * Fetch hashes from pwnedpasswords endpoint
     *
     * @param  string $first5Chars first 5 chars of sha1 of password
     * @return array|bool          false if any error, else array of password
     */
    private function fetchHashes(string $first5Chars) {

        if (!$this->client) {
            $this->client = new Client;
        }

        $url = sprintf('%s%s', getenv('pwnedpasswords_endpoint'), $first5Chars);

        try {
            $response = (string) $this->client->request('GET', $url)->getBody();
        } catch (RequestException $e) {
            return false;
        }

        $passwords = explode(PHP_EOL, $response);
        foreach ($passwords as $key => $value) {
            if (trim($value) == '') {
                unset($passwords[$key]);
                continue;
            }
            $passwords[$key] = [
                'sha1'  => $first5Chars . explode(':', $value)[0],
                'count' => explode(':', $value)[1],
            ];
        }

        return $passwords;
    }

    /**
     * Read options
     *
     * @param  array  $options
     * @return void
     */
    public function readOptions(array $options) {
        if (in_array('mock', array_keys($options))) {
            $this->client = $options['mock'];
        }
    }
}
