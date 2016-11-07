<?php

namespace BrianFaust\Messageable\Interfaces;

interface HasMessages
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
