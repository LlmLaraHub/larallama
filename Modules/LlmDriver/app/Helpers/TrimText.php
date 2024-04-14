<?php

namespace LlmLaraHub\LlmDriver\Helpers;

use voku\helper\StopWords;
use Wamania\Snowball\StemmerFactory;

/**
 * This is ChatGPT code
 */
class TrimText
{
    const ARTICLES_PREPOSITIONS = [
        'english' => ['the', 'a', 'an', 'in', 'on', 'at', 'for', 'to', 'of'],
    ];

    const NEGATION_WORDS = [
        'spanish' => [
            'no', 'ni', 'nunca', 'jamas', 'tampoco', 'nadie', 'nada', 'ninguno', 'ninguna', 'ningunos', 'ningunas', 'ningun',
        ],
        'english' => [
            'no', 'nor', 'not', 'don', 'dont', 'ain', 'aren', 'arent', 'couldn', 'couldnt', 'didn', 'didnt', 'doesn', 'doesnt',
            'hadn', 'hadnt', 'hasn', 'hasnt', 'haven', 'havent', 'isn', 'isnt', 'mightn', 'mightnt', 'mustn', 'mustnt',
            'needn', 'neednt', 'shan', 'shant', 'shouldn', 'shouldnt', 'wasn', 'wasnt', 'weren', 'werent', 'won', 'wont',
            'wouldn', 'wouldnt',
        ],
    ];

    protected StopWords $stopWords;

    public function handle(string $content): string
    {
        $this->stopWords = new StopWords();

        return $this->trim($content);
    }

    /**
     * @TODO
     * add language to meta_data
     */
    public function trim(
        string $text,
        string $language = 'en',
        bool $removeSpaces = false,
        bool $removeStopwords = true,
        bool $removePunctuation = false,
        bool $stemmer = true
    ): string {
        if ($stemmer) {
            $stemmer = StemmerFactory::create($language);
        }

        $stopwords = $this->stopWords->getStopWordsFromLanguage($language);

        $text = str_replace(["'", '’'], '', $text);

        $tokenized = preg_split('/\s+/', $text);

        if ($removeStopwords) {
            $wordsToExclude = array_merge($stopwords, self::ARTICLES_PREPOSITIONS[$language] ?? []);
            $wordsToExclude = array_diff($wordsToExclude, self::NEGATION_WORDS[$language] ?? []);

            $tokenized = array_filter($tokenized, function ($word) use ($wordsToExclude) {
                return ! in_array(strtolower($word), $wordsToExclude);
            });
        }

        $tokenized = array_values($tokenized);

        $words = $tokenized;

        if ($stemmer) {

            $words = array_map(function ($word) use ($stemmer) {
                return $stemmer->stem($word);
            }, $tokenized);

            $words = array_values($words);

            // Restore title_case and uppercase after stemming
            $caseRestored = [];
            for ($i = 0; $i < count($words); $i++) {
                $word = $words[$i];
                if (ctype_upper(substr($tokenized[$i], 0, 1))) {
                    $word = ucfirst($word);
                } elseif (ctype_upper($tokenized[$i])) {
                    $word = strtoupper($word);
                }
                $caseRestored[] = $word;
            }
            $words = $caseRestored;
        }

        $joinStr = $removeSpaces ? '' : ' ';
        $trimmed = implode($joinStr, $words);

        if (! $removePunctuation) {
            $trimmed = preg_replace('/\s([?.!,:;])/', '$1', $trimmed);
        }

        $trimmed = preg_replace('/[^\x20-\x7F]/', '', $trimmed);

        return $trimmed;
    }
}
