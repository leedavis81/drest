<?php
namespace Drest\Query;

use Doctrine\ORM\EntityManager;
use Drest\Configuration;
use Drest\DrestException;
use Drest\Mapping\RouteMetaData;
use DrestCommon\Request\Request;
use DrestCommon\ResultSet;

/**
 * Handles processing logic for expose fields.
 * @author Lee
 *
 */
class ExposeFields implements \Iterator
{
    /**
     * Current iteration position
     * @var integer $position
     */
    private $position = 0;

    /**
     * Expose fields to be used - multidimensional array
     * @var array $fields
     */
    private $fields;

    /**
     * The matched route
     * @var RouteMetaData $route
     */
    protected $route;

    /**
     * The route expose array - If explicitly set it overrides any default config settings
     * @var array $route_expose
     */
    protected $route_expose;

    /**
     * An array of classes that are registered on default expose depth.
     * Temporary used during processExposeDepth to prevent you traversing up and down bi-directional relations
     * @var array $registered_expose_classes ;
     */
    protected $registered_expose_classes = array();


    /**
     * Create an instance of ExposeFields - use create() method
     * @param RouteMetaData $route - requires a matched route
     */
    private function __construct(RouteMetaData $route)
    {
        $this->route = $route;
        $this->route_expose = $route->getExpose();
    }

    /**
     * Create an instance of ExposeFields
     * @param RouteMetaData $route - requires a matched route
     * @return \Drest\Query\ExposeFields
     */
    public static function create(RouteMetaData $route)
    {
        return new self($route);
    }

    /**
     * Set the default exposure fields using the configured exposure depth
     * @param EntityManager $em
     * @param integer $exposureDepth
     * @param integer $exposureRelationsFetchType
     * @return ExposeFields $this object instance
     */
    public function configureExposeDepth(EntityManager $em, $exposureDepth = 0, $exposureRelationsFetchType = null)
    {
        if (!empty($this->route_expose)) {
            $this->fields = $this->route_expose;
        } else {
            $this->processExposeDepth(
                $this->fields,
                $this->route->getClassMetaData()->getClassName(),
                $em,
                $exposureDepth,
                $exposureRelationsFetchType
            );
        }
        return $this;
    }

    /**
     * Configure the expose object to filter out fields that are not allowed to be use by the client.
     * Unlike the configuring of the Pull request, this function will return the formatted array in a ResultSet object
     * This is only applicable for a HTTP push (POST/PUT/PATCH) call
     * @param array $pushed - the data push on the request
     * @throws \Drest\DrestException
     * @return \DrestCommon\ResultSet
     *
     * @todo: this should follow the same pattern as configurePullRequest
     */
    public function configurePushRequest($pushed)
    {
        // Offset the array by one of it has a string key and is size of 1
        if (sizeof($pushed) == 1 && is_string(key($pushed))) {
            $rootKey = key($pushed);
            $pushed = $this->filterPushExpose($pushed[key($pushed)], $this->fields);

            return ResultSet::create($pushed, $rootKey);
        } else {
            throw DrestException::unableToHandleACollectionPush();
        }
    }

    /**
     * Filter out requested expose fields against what's allowed
     * @param array $requested - The requested expose definition
     * @param array $actual - current allowed expose definition
     * @return array $request - The requested expose data with non-allowed data stripped off
     */
    protected function filterPushExpose($requested, $actual)
    {
        $actual = (array)$actual;
        foreach ($requested as $requestedKey => $requestedValue) {
            if ($requestedKey !== 0 && in_array($requestedKey, $actual)) {
                continue;
            }

            if (is_array($requestedValue)) {
                if (is_string($requestedKey) && isset($actual[$requestedKey])) {
                    $requested[$requestedKey] = $this->filterPushExpose($requestedValue, $actual[$requestedKey]);
                    continue;
                } elseif (is_int($requestedKey)) {
                    $requested[$requestedKey] = $this->filterPushExpose($requestedValue, $actual);
                    continue;
                }
            } else {
                if (in_array($requestedKey, $actual)) {
                    continue;
                }
            }
            unset($requested[$requestedKey]);
        }
        return $requested;
    }


    /**
     * Configure the expose object to filter out fields that have been explicitly requested by the client.
     * This is only applicable for a HTTP pull (GET) call. For configuring
     * @param array $requestOptions
     * @param Request $request
     * @return ExposeFields $this object instance
     */
    public function configurePullRequest(array $requestOptions, Request $request)
    {
        if (empty($this->route_expose)) {
            $exposeString = '';
            foreach ($requestOptions as $requestOption => $requestValue) {
                switch ($requestOption) {
                    case Configuration::EXPOSE_REQUEST_HEADER:
                        $exposeString = $request->getHeaders($requestValue);
                        break;
                    case Configuration::EXPOSE_REQUEST_PARAM:
                        $exposeString = $request->getParams($requestValue);
                        break;
                    case Configuration::EXPOSE_REQUEST_PARAM_GET:
                        $exposeString = $request->getQuery($requestValue);
                        break;
                    case Configuration::EXPOSE_REQUEST_PARAM_POST:
                        $exposeString = $request->getPost($requestValue);
                        break;
                }
            }
            if (!empty($exposeString)) {
                $requestedExposure = $this->parseExposeString($exposeString);
                $this->filterRequestedExpose($requestedExposure, $this->fields);
                $this->fields = $requestedExposure;
            }
        }

        return $this;
    }


    /**
     * An awesome solution was posted on (link below) to parse these using a regex
     * http://stackoverflow.com/questions/16415558/regex-top-level-contents-from-a-string
     *
     *   preg_match_all(
     *       '/(?<=\[)     # Assert that the previous characters is a [
     *         (?:         # Match either...
     *          [^[\]]*    # any number of characters except brackets
     *         |           # or
     *          \[         # an opening bracket
     *          (?R)       # containing a match of this very regex
     *          \]         # followed by a closing bracket
     *         )*          # Repeat as needed
     *         (?=\])      # Assert the next character is a ]/x',
     *       $string, $result, PREG_PATTERN_ORDER);
     *
     * @todo: Adapt the parser to use the regex above (will also need alter it to grab parent keys)
     *
     * Parses an expose string into an array
     * Example: "username|email_address|profile[id|lastname|addresses[id]]|phone_numbers"
     * @param string $string
     * @return array $result
     * @throws InvalidExposeFieldsException - if any syntax error occurs, or unable to parse the string
     */
    protected function parseExposeString($string)
    {
        $string = trim($string);
        if (preg_match("/[^a-zA-Z0-9\[\]\|_]/", $string) === 1) {
            throw InvalidExposeFieldsException::invalidExposeFieldsString();
        }

        $results = array();
        $this->recurseExposeString(trim($string, '|'), $results);
        return $results;
    }

    /**
     * Recursively process the passed expose string
     * @param string $string - the string to be processed
     * @param array $results - passed by reference
     * @throws InvalidExposeFieldsException if unable to correctly parse the square brackets.
     */
    protected function recurseExposeString($string, &$results)
    {
        if (substr_count($string, '[') !== substr_count($string, ']')) {
            throw InvalidExposeFieldsException::unableToParseExposeString($string);
        }

        $results = (array)$results;

        $parts = $this->parseStringParts($string);
        foreach ($parts->parts as $part) {
            $this->recurseExposeString($part['contents'], $results[$part['tagName']]);
        }

        $results = array_merge(
            array_filter(
                explode('|', $parts->remaining_string),
                function ($item) {
                    return (empty($item)) ? false : true;
                }
            ),
            $results
        );
    }

    /**
     * Get information on parsed (top-level) brackets
     * @param string $string
     * @return \stdClass $information contains parse information object containing a $parts array eg array(
     *    'openBracket' => xx,        - The position of the open bracket
     *    'closeBracket' => xx        - The position of the close bracket
     *  'contents' => xx            - The contents of the bracket
     *  'tagName' => xx                - The name of the accompanying tag
     * )
     */
    private function parseStringParts($string)
    {
        $information = new \stdClass();
        $information->parts = array();
        $openPos = null;
        $closePos = null;
        $bracketCounter = 0;
        foreach (str_split($string) as $key => $char) {
            if ($char === '[') {
                if (is_null($openPos) && $bracketCounter === 0) {
                    $openPos = $key;
                }
                $bracketCounter++;
            }
            if ($char === ']') {
                if (is_null($closePos) && $bracketCounter === 1) {
                    $closePos = $key;
                }
                $bracketCounter--;
            }

            if (is_numeric($openPos) && is_numeric($closePos)) {
                // Work backwards from openPos until we hit [|]
                $stopPos = 0;
                foreach (array('|', '[', ']', '%') as $stopChar) {
                    if (($pos = strrpos(substr($string, 0, $openPos), $stopChar)) !== false) {
                        $stopPos = (++$pos > $stopPos) ? $pos : $stopPos;
                    }
                }

                if (($openPos + 1 === $closePos)) {
                    // Where no offset has been defined, blank out the [] characters
                    $rangeSize = ($closePos - $openPos) + 1;
                    $string = substr_replace($string, str_repeat('%', $rangeSize), $openPos, $rangeSize);
                } else {
                    $information->parts[] = array(
                        'openBracket' => $openPos,
                        'closeBracket' => $closePos,
                        'contents' => substr($string, $openPos + 1, ($closePos - $openPos) - 1),
                        'tagName' => substr($string, $stopPos, ($openPos - $stopPos)),
                        'tagStart' => $stopPos,
                        'tagEnd' => ($openPos - 1)
                    );
                    $rangeSize = ($closePos - $stopPos) + 1;
                    $string = substr_replace($string, str_repeat('%', $rangeSize), $stopPos, $rangeSize);
                }
                $openPos = $closePos = null;
            }
        }

        $string = str_replace('%', '', $string);
        $string = str_replace('||', '|', $string);

        $information->remaining_string = trim($string, '|');
        return $information;
    }

    /**
     * Filter out requested expose fields against what's allowed
     * @param array $requested - The requested expose definition - invalid / not allowed data is stripped off
     * @param array $actual - current allowed expose definition
     */
    protected function filterRequestedExpose(&$requested, &$actual)
    {
        $actual = (array)$actual;
        foreach ($requested as $requestedKey => $requestedValue) {
            if (in_array($requestedValue, $actual)) {
                continue;
            }

            if (is_array($requestedValue)) {
                if (isset($actual[$requestedKey])) {
                    $this->filterRequestedExpose($requested[$requestedKey], $actual[$requestedKey]);
                    continue;
                }
            } else {
                if (isset($actual[$requestedValue]) && is_array($actual[$requestedValue]) && array_key_exists(
                        $requestedValue,
                        $actual
                    )
                ) {
                    continue;
                }
            }
            unset($requested[$requestedKey]);
        }
    }

    /**
     * Recursive function to generate default expose columns
     *
     * @param array $fields - array to be populated recursively (referenced)
     * @param string $class - name of the class to process
     * @param EntityManager $em - entity manager used to fetch class information
     * @param integer $depth - maximum depth you want to travel through the relations
     * @param integer $fetchType - The fetch type to be used
     * @param integer|null $fetchType - The required fetch type of the relation
     */
    protected function processExposeDepth(&$fields, $class, EntityManager $em, $depth = 0, $fetchType = null)
    {
        $this->registered_expose_classes[] = $class;
        if ($depth > 0) {
            $metaData = $em->getClassMetadata($class);
            $fields = $metaData->getColumnNames();

            if (($depth - 1) > 0) {
                --$depth;
                foreach ($metaData->getAssociationMappings() as $key => $assocMapping) {
                    if (!in_array($assocMapping['targetEntity'], $this->registered_expose_classes) && (is_null(
                                $fetchType
                            ) || ($assocMapping['fetch'] == $fetchType))
                    ) {
                        $this->processExposeDepth(
                            $fields[$key],
                            $assocMapping['targetEntity'],
                            $em,
                            $depth,
                            $fetchType
                        );
                    }
                }
            }
        }
    }


    /**
     * Get the expose fields
     * @return array $fields
     */
    public function toArray()
    {
        if (!empty($this->route_expose)) {
            return $this->route_expose;
        }
        return $this->fields;
    }

    public function current()
    {
        return $this->fields[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function valid()
    {
        return isset($this->fields[$this->position]);
    }

}