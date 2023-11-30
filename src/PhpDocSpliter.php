<?php

namespace Parisprobr\PhpConfluence;


class PhpDocSpliter
{
    const START_TAG            = '@confluence';
    const END_TAG              = '@endconfluence';
    const SPACE_TAG            = '@space';
    const TITLE_TAG            = '@title';
    const CONTENT_TAG          = '@content';
    const SEPARATOR_CONTENT    = "\n--------\n";

    private $codeDirectory;

    public function __construct($codeDirectory)
    {
        $this->codeDirectory = $codeDirectory;
    }

    public function getFilesWithPhpDocSpliter()
    {
        $cmd = 'find ' . $this->codeDirectory . '/ -maxdepth 120 \( -name vendor \) -prune -o -type f -name "*.php" -exec grep -l "@confluence" {} \;';
        $phpFiles = explode("\n", trim(`$cmd`));
        return $phpFiles;
    }

    public function getComents($phpFiles)
    {
        if (empty($phpFiles)) {
            return false;
        }
        foreach ($phpFiles as $phpFile) {
            if (!$phpFile) {
                continue;
            }
            $content = file_get_contents($phpFile);
            $matchs = $this->pregMatchAllComments($content);
            $matchs = $this->limpaMatchs($matchs);
            $formatedComents = $this->formatComents($matchs);
        }
        return $formatedComents;
    }

    private function getTagOneLine($match, $tag)
    {
        preg_match('/'.$tag.'\ (.)*/', $match, $output_array);
        return trim(str_replace($tag, '', $output_array[0]));
    }


    private function getLinesAfterTag($match, $tag)
    {   
        if(!strstr($match, $tag)){
            return '';
        }
        $parts = explode($tag, $match);
        return trim(array_pop($parts));
    }

    private function formatComents($matchs)
    {
        $formatedComents = array();

        if (empty($matchs)) {
            return array();
        }
        foreach ($matchs as $key => $match) {
            $formated['Title']   = $this->getTagOneLine($match, self::TITLE_TAG);
            $formated['Space']   = $this->getTagOneLine($match, self::SPACE_TAG);
            $formated['Content'] = $this->getLinesAfterTag($match, self::CONTENT_TAG);
            $contentKey = $formated['Title'] . '-' . $formated['Space'];
            if (array_key_exists($contentKey, $formatedComents)) {
                $formatedComents[$contentKey]['Content'] .= self::SEPARATOR_CONTENT . $formated['Content'];
                continue;
            }
            $formatedComents[$contentKey] = $formated;
        }
        return $formatedComents;
    }

    private function limpaMatchs($matchs)
    {
        if (empty($matchs)) {
            return array();
        }
        foreach ($matchs as $key => $match) {
            $matchs[$key] = trim(preg_replace('/^\s*\*/m', '', $match));
        }

        return $matchs;
    }

    private function pregMatchAllComments($content)
    {
        if (empty($content)) {
            return array();
        }

        $pattern = '#' . self::START_TAG . '(.*?)' . self::END_TAG . '#s';
        preg_match_all($pattern, $content, $matches);

        if (!isset($matches[1])) {
            return array();
        }
        return $matches[1];
    }
}
