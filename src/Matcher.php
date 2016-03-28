<?php

namespace Yuloh\PatternRecognition;

class Matcher implements \JsonSerializable
{
    /**
     * An array of registered patterns.
     *
     * @var array
     */
    private $matches;

    /**
     * An array of the data registered for patterns.
     *
     * @var array
     */
    private $data;

    const ROOT_MATCH_WEIGHT  = 1;
    const GLOB_MATCH_WEIGHT  = 2;
    const EXACT_MATCH_WEIGHT = 3;

    /**
     * Register a pattern, and the data that will be returned if an input matches.
     * Both keys and values are considered to be strings. Other types are converted to strings.
     *
     * @param array  $pattern
     * @param mixed $data
     *
     * @return $this
     */
    public function add(array $pattern, $data)
    {
        $key = $this->key($pattern);

        $this->matches[$key] = $pattern;
        $this->data[$key]    = $data;

        return $this;
    }

    /**
     * Remove this pattern, and it's data, from the matcher.
     *
     * @param  array  $pattern
     * @return $this
     */
    public function remove(array $pattern)
    {
        $key = $this->key($pattern);

        unset($this->matches[$key]);
        unset($this->data[$key]);

        return $this;
    }

    /**
     * Serializes the object to a value that can be serialized natively by json_encode().
     *
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Serializes the matcher to an array of the registered matches and data.
     *
     * @return array
     */
    public function toArray()
    {
        $serialized = [];
        foreach ($this->matches as $key => $match) {
            $data = $this->data[$this->key($match)];
            $serialized[] = [
                'match' => $match,
                'data'  => $data
            ];
        }

        return $serialized;
    }

    /**
     * Return the unique match for this pattern, or null if not found.
     * The key value pairs of the pattern are matched against the patterns previously added,
     * and the most specifc pattern wins. Unknown key value pairs in the pattern are ignored.
     *
     * @param  array  $pattern The pattern to search for.
     * @return mixed           The registered data for the match if found, otherwise null.
     */
    public function find(array $pattern)
    {
        $bestMatch = $bestMatchScore = null;

        foreach ($this->matches as $match) {
            $matchScore = $this->getMatchScore($match, $pattern);

            if ($matchScore > $bestMatchScore) {
                $bestMatch      = $match;
                $bestMatchScore = $matchScore;
            }
        }

        return !is_null($bestMatch) ? $this->getData($bestMatch) : null;
    }

    /**
     * Return the data for the given match.
     *
     * @param  array $match
     * @return mixed
     */
    private function getData($match)
    {
        return $this->data[$this->key($match)];
    }

    /**
     * Returns a number indicating how well the pattern matches the subject.
     *
     * @param  array  $subject
     * @param  array  $pattern
     * @return int
     */
    private function getMatchScore(array $subject, array $pattern)
    {
        if (empty($subject)) {
            return self::ROOT_MATCH_WEIGHT;
        }

        $matches        = $this->getMatches($subject, $pattern);
        $globMatches    = $this->getGlobMatches($subject, $pattern);
        $matchScore     = count($matches) >= count($subject) ? count($matches) : 0;
        $globMatchScore = count($globMatches) >= count($subject) ? count($globMatches) : 0;

        if ($globMatchScore > 0) {
            $globMatchScore += self::GLOB_MATCH_WEIGHT;
        }

        if ($matchScore > 0) {
            $matchScore += self::EXACT_MATCH_WEIGHT;
        }

        return max($globMatchScore, $matchScore);
    }

    /**
     * Return an array of key value pairs in $subject that are present in $pattern.
     *
     * @param  array $subject
     * @param  array $pattern
     * @return array
     */
    private function getMatches(array $subject, array $pattern)
    {
        return array_intersect_assoc($subject, $pattern);
    }

    /**
     * Return an array of key value pairs in $subject that are present in $pattern as glob matches.
     *
     * @param  array $subject
     * @param  array $pattern
     * @return array
     */
    private function getGlobMatches(array $subject, array $pattern)
    {
        return array_uintersect_assoc(
            $subject,
            $pattern,
            function ($globPattern, $string) {
                return fnmatch($globPattern, $string) ? 0 : -1;
            }
        );
    }

    /**
     * Get the key used to index a match internally.
     *
     * @param  array $match
     * @return string
     */
    private function key($match)
    {
        return serialize($match);
    }
}
