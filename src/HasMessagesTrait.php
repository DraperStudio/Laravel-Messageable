<?php

namespace BrianFaust\Messageable;

use BrianFaust\Messageable\Message;
use BrianFaust\Messageable\Participant;
use BrianFaust\Messageable\Thread;

trait HasMessagesTrait
{
    /**
     * @return mixed
     */
    public function messages()
    {
        return $this->morphMany(Message::class, 'creator');
    }

    /**
     * @return mixed
     */
    public function threads()
    {
        return $this->belongsToMany(Thread::class, 'participants', 'participant_id');
    }

    /**
     * @return int
     */
    public function newMessagesCount()
    {
        return count($this->threadsWithNewMessages());
    }

    /**
     * @return array
     */
    public function threadsWithNewMessages()
    {
        $threadsWithNewMessages = [];
        $participants = Participant::where('participant_id', $this->id)
                                    ->where('participant_type', get_class($this))
                                    ->lists('last_read', 'thread_id');

        if ($participants) {
            $threads = Thread::whereIn('id', array_keys($participants->toArray()))->get();

            foreach ($threads as $thread) {
                if ($thread->updated_at > $participants[$thread->id]) {
                    $threadsWithNewMessages[] = $thread->id;
                }
            }
        }

        return $threadsWithNewMessages;
    }
}
