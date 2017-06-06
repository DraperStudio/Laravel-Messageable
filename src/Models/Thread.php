<?php

/*
 * This file is part of Laravel Messageable.
 *
 * (c) Brian Faust <hello@brianfaust.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrianFaust\Messageable\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Thread extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'threads';

    /**
     * @var array
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    /**
     * @return mixed
     */
    public function creator(): Model
    {
        return $this->messages()->oldest()->first()->creator;
    }

    /**
     * @return mixed
     */
    public function getLatestMessage(): Message
    {
        return $this->messages()->latest()->first();
    }

    /**
     * @return mixed
     */
    public static function getAllLatest(): Collection
    {
        return static::latest('updated_at');
    }

    /**
     * @param null $participant
     *
     * @return array
     */
    public function participantsIdsAndTypes($participant = null): array
    {
        $participants = $this->participants()
                             ->withTrashed()
                             ->lists('participant_id', 'participant_type');

        if ($participant) {
            $participants[] = $participant;
        }

        return $participants;
    }

    /**
     * @param $query
     * @param $participant
     *
     * @return mixed
     */
    public function scopeForModel($query, $participant)
    {
        return $query->join('participants', 'threads.id', '=', 'participants.thread_id')
            ->where('participants.participant_id', $participant->id)
            ->where('participants.participant_type', get_class($participant))
            ->where('participants.deleted_at', null)
            ->select('threads.*');
    }

    /**
     * @param $query
     * @param $participant
     *
     * @return mixed
     */
    public function scopeForModelWithNewMessages($query, $participant)
    {
        return $query->join('participants', 'threads.id', '=', 'participants.thread_id')
            ->where('participants.participant_id', $participant->id)
            ->where('participants.participant_type', get_class($participant))
            ->whereNull('participants.deleted_at')
            ->where(function ($query) {
                $query->where('threads.updated_at', '>', 'participants.last_read')
                      ->orWhereNull('participants.last_read');
            })
            ->select('threads.*');
    }

    /**
     * @param $data
     * @param Model $creator
     *
     * @return $this
     */
    public function addMessage($data, Model $creator): bool
    {
        $message = (new Message())->fill(array_merge($data, [
            'creator_id' => $creator->id,
            'creator_type' => get_class($creator),
        ]));

        return (bool) $this->messages()->save($message);
    }

    /**
     * @param array $messages
     */
    public function addMessages(array $messages)
    {
        foreach ($messages as $message) {
            $this->addMessage($message['data'], $message['creator']);
        }
    }

    /**
     * @param Model $participant
     *
     * @return $this|Model
     */
    public function addParticipant(Model $participant): bool
    {
        $participant = (new Participant())->fill([
            'participant_id' => $participant->id,
            'participant_type' => get_class($participant),
            'last_read' => new Carbon(),
        ]);

        return (bool) $this->participants()->save($participant);
    }

    /**
     * @param array $participants
     */
    public function addParticipants(array $participants)
    {
        foreach ($participants as $participant) {
            $this->addParticipant($participant);
        }
    }

    /**
     * @param $userId
     */
    public function markAsRead($userId)
    {
        try {
            $participant = $this->getParticipantFromModel($userId);
            $participant->last_read = new Carbon();
            $participant->save();
        } catch (ModelNotFoundException $e) {
            // do nothing
        }
    }

    /**
     * @param $participant
     *
     * @return bool
     */
    public function isUnread($participant): bool
    {
        try {
            $participant = $this->getParticipantFromModel($participant);

            if ($this->updated_at > $participant->last_read) {
                return true;
            }
        } catch (ModelNotFoundException $e) {
            // do nothing
        }

        return false;
    }

    /**
     * @param $participant
     *
     * @return mixed
     */
    public function getParticipantFromModel($participant): Participant
    {
        return $this->participants()
                    ->where('participant_id', $participant->id)
                    ->where('participant_type', get_class($participant))
                    ->firstOrFail();
    }

    public function activateAllParticipants()
    {
        $participants = $this->participants()->withTrashed()->get();

        foreach ($participants as $participant) {
            $participant->restore();
        }
    }

    /**
     * @param $participant
     *
     * @return bool
     */
    public function hasParticipant($participant): bool
    {
        return $this->participants()
                    ->where('participant_id', '=', $participant->id)
                    ->where('participant_type', '=', get_class($participant))
                    ->count() > 0;
    }
}
