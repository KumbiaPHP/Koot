<?php

/**
 * Parent class to use SQL with the advantages of ORM
 */
abstract class LiteRecord extends \Kumbia\ActiveRecord\LiteRecord
{
    /**
     * Alias de los campos.
     *
     * @return string[]
     */
    public function getAlias(): array
    {
        $humanize = function ($name) {
            return \ucwords(\str_replace('_', '  ', $name));
        };

        return \array_map($humanize, $this->getFields());
    }
}
