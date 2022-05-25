<?php

namespace Avocado\HTTP\JSON;
use Avocado\HTTP\JSON\JSONFactory;

class JSON {
    private string $serializedData;
    private bool $serializePrivateProperties;

    /**
     * @param mixed $data
     * @param bool $serializePrivateProperties
     */
    public function __construct(mixed $data, bool $serializePrivateProperties = false) {
        $this->serializedData = $this->serialize($data);
        $this->serializePrivateProperties = $serializePrivateProperties;
    }

    /**
     * @return string
     */
    public function getSerializedData(): string {
        return $this->serializedData;
    }

    /**
     * @param mixed $data
     * @return string
     */
    private function serialize(mixed $data): string {
        return match(gettype($data)) {
            "array" => isset($data[0]) ? match(gettype($data[0])) {
               "string", "boolean", "double", "integer", "NULL" => JSONFactory::serializePrimitive($data),
               "object" => JSONFactory::serializeObjects($data, JSONFactory::serializePrivateProperties)
            } : JSONFactory::serializePrimitive($data),
            "string", "boolean", "double", "integer", "NULL" => JSONFactory::serializePrimitive($data),
            "object" => $this->factory->serializeObjects($data, JSONFactory::serializePrivateProperties)
        };
    }
}
