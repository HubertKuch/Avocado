<?php

namespace Avocado\AvocadoORM\Mappers;

use Avocado\ORM\AvocadoModel;

interface Mapper {
    public function entityToObject(AvocadoModel $model, object $entity): object;
}
