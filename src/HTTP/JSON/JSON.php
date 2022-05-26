<?php

namespace Avocado\HTTP\JSON;

class JSON {
    private string $serializedData;
    private bool $serializePrivateProperties;

    /**
     * @param mixed $data
     * @param bool $serializePrivateProperties
     */
    public function __construct(mixed $data, bool $serializePrivateProperties = false) {
        $this->serializePrivateProperties = $serializePrivateProperties;
        $this->serializedData = $this->serialize($data);
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
               "object" => JSONFactory::serializeObjects($data, $this->serializePrivateProperties)
            } : JSONFactory::serializePrimitive($data),
            "string", "boolean", "double", "integer", "NULL" => JSONFactory::serializePrimitive($data),
            "object" => JSONFactory::serializeObjects($data, $this->serializePrivateProperties)
        };
    }
}
