<?php
class Resources extends LiteRecord
{
    public function _beforeCreate()
    {
        $this->status = 1;
    }
}
