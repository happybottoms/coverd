<?php

namespace App\Entity\Traits;

trait PubliclyExposedEntity
{
    /**
     * Returns the identifier that should be shown to users
     *
     * @return mixed;
     */
    public function getPublicId()
    {
        return $this->{$this::getPublicIdProperty()};
    }

    /**
     * Returned the property name that should be used for the user viewable identifier
     *
     * @return string
     */
    public static function getPublicIdProperty(): string
    {
        return "id";
    }
}
