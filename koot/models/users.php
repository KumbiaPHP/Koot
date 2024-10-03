<?php
class Users extends LiteRecord
{
    public function _beforeCreate()
    {
        $this->status = 1;
    }
}
