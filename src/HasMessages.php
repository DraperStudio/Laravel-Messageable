<?php



declare(strict_types=1);



namespace BrianFaust\Messageable;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasMessages
{
    /**
     * @return mixed
     */
    public function messages(): MorphMany
    {
        return $this->morphMany(Message::class, 'creator');
    }

    /**
     * @return mixed
     */
    public function threads(): BelongsToMany
    {
        return $this->belongsToMany(Thread::class, 'participants', 'participant_id');
    }

    /**
     * @return int
     */
    public function newMessagesCount(): int
    {
        return count($this->threadsWithNewMessages());
    }

    /**
     * @return array
     */
    public function threadsWithNewMessages(): array
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
