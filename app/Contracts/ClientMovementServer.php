<?php

namespace App\Contracts;

interface ClientMovementServer
{
    /**
     * @param string $message
     * @return string Client Movement ID
     */
    public function consumeInvoiceSavedEvent(string $message): string;
}
