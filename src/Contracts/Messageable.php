<?php

namespace BrianFaust\Messageable\Contracts;

interface Messageable
{
    /**
     * @return mixed
     */
    public function messages();

    /**
     * @return mixed
     */
    public function threads();

    /**
     * @return mixed
     */
    public function newMessagesCount();

    /**
     * @return mixed
     */
    public function threadsWithNewMessages();
}
