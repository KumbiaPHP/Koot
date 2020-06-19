<?php
class Roles extends LiteRecord
{
    public function _beforeCreate()
    {
        $this->status = 1;
    }
}
