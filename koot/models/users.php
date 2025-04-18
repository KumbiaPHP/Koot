<?php

/**
 * Class Users extends LiteRecord
 */
class Users extends LiteRecord
{
    /**
     * Prepare the entity before it is created by setting the default status
     * and ensuring the email is unique.
     */
    public function _beforeCreate()
    {
        if (!$this->status) {
            $this->status = 1;
        }

        if (self::filter('WHERE email = ?', [$this->email])) {
            Flash::error(_('Email already exists'));
            return false;
        }
    }

    /**
     * Prepare the entity before it is updated by ensuring the email is unique
     * and different from the current user's email.
     */
    public function _beforeUpdate()
    {
        if (self::filter('WHERE email = ? AND id != ?', [$this->email, $this->id])) {
            Flash::error(_('Email already exists'));
            return false;
        }
    }
}
