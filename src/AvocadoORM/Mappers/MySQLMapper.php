<?php

namespace Avocado\AvocadoORM\Mappers;

use ReflectionEnum;
use ReflectionClass;
use ReflectionObject;
use ReflectionException;
use Avocado\ORM\AvocadoModel;
use Avocado\ORM\Attributes\Id;
use Avocado\ORM\Attributes\Field;
use Avocado\ORM\AvocadoModelException;

class MySQLMapper implements Mapper {

    /**
     * @throws AvocadoModelException
     * @throws ReflectionException
     */
    public function entityToObject(AvocadoModel $model, object $entity): object {
        $modelReflection = new ReflectionClass($model->getModel());
        $modelProperties = $modelReflection->getProperties();

        $entityReflection = new ReflectionObject($entity);

        $instance = $modelReflection->newInstanceWithoutConstructor();
        $instanceReflection = new ReflectionObject($instance);

        foreach ($modelProperties as $modelProperty) {

            $field = $modelProperty->getAttributes(Field::class)[0] ?? null;
            $primaryKey = $modelProperty->getAttributes(Id::class)[0] ?? null;
            $modelPropertyName = $modelProperty->getName();
            $entityPropertyName = $modelProperty->getName();

            if (($field && empty($field->getArguments())) || ($primaryKey && empty($primaryKey->getArguments()))) {
                $entityPropertyName = $modelProperty->getName();
            } else if ($field && !empty($field->getArguments())) {
                $entityPropertyName = $field->getArguments()[0];
            } else if ($primaryKey && !empty($primaryKey->getArguments())) {
                $entityPropertyName = $primaryKey->getArguments()[0];
            }

            $entityPropertyValue = $entityReflection -> getProperty($entityPropertyName) -> getValue($entity);

            $instanceProperty = $instanceReflection -> getProperty($modelPropertyName);

//            var_dump($modelProperty, $model->isPropertyIsEnum($modelPropertyName));

            if ($model->isPropertyIsEnum($modelPropertyName)) {
                $enumPropertyReflection = new ReflectionEnum($modelProperty->getType()->getName());

                foreach ($enumPropertyReflection->getCases() as $case) {
                    if ($case->getBackingValue() === $entityPropertyValue) {
                        $instanceProperty -> setValue($instance, $case->getValue());
                        break;
                    }
                }

                if (!$instanceProperty -> isInitialized($instance)) {
                    $message = sprintf("`%s` enum property on `%s` model do not have `%s` type.", $entityPropertyName, $model->getModel(), $entityPropertyValue);

                    throw new AvocadoModelException($message);
                }
            } else {
                $instanceProperty -> setValue($instance, $entityPropertyValue);
            }
        }

        return $instance;
    }
}
