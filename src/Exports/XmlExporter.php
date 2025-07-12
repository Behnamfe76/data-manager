<?php

namespace DataManager\Exports;

use DataManager\Contracts\ExporterInterface;

class XmlExporter implements ExporterInterface
{
    /**
     * Export data to an XML file.
     *
     * @param iterable $data
     * @param string $target Path to the XML file
     * @return void
     */
    public function export(iterable $data, $target): void
    {
        $arrayData = is_array($data) ? $data : iterator_to_array($data);
        $xml = new \SimpleXMLElement('<?xml version="1.0"?><root></root>');
        $this->arrayToXml($arrayData, $xml);
        $result = $xml->asXML($target);
        if ($result === false) {
            throw new \RuntimeException("Failed to write XML file: $target");
        }
    }

    /**
     * Recursively convert an array to XML nodes.
     *
     * @param array $data
     * @param \SimpleXMLElement $xml
     * @return void
     */
    protected function arrayToXml(array $data, \SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $child = is_numeric($key)
                    ? $xml->addChild("item$key")
                    : $xml->addChild($key);
                $this->arrayToXml($value, $child);
            } else {
                $xml->addChild(is_numeric($key) ? "item$key" : $key, htmlspecialchars((string)$value));
            }
        }
    }
} 