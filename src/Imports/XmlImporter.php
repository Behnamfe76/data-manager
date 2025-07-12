<?php

namespace DataManager\Imports;

use DataManager\Contracts\ImporterInterface;

class XmlImporter implements ImporterInterface
{
    /**
     * Import data from an XML file.
     *
     * @param string $source Path to the XML file
     * @return iterable
     */
    public function import($source): iterable
    {
        if (!is_readable($source)) {
            throw new \InvalidArgumentException("XML file not readable: $source");
        }

        $xmlContent = file_get_contents($source);
        if ($xmlContent === false) {
            throw new \RuntimeException("Failed to read XML file: $source");
        }

        $xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($xml === false) {
            throw new \RuntimeException("Invalid XML in file: $source");
        }

        $json = json_encode($xml);
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Failed to convert XML to array: " . json_last_error_msg());
        }

        // If the XML root contains multiple items, yield each; otherwise yield the root
        if (is_array($data) && count($data) === 1) {
            $data = reset($data);
        }
        if (is_array($data)) {
            foreach ($data as $item) {
                yield $item;
            }
        } else {
            yield $data;
        }
    }
} 