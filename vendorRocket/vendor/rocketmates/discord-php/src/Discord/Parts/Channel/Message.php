<?php

/*
 * This file is apart of the DiscordPHP project.
 *
 * Copyright (c) 2016 David Cole <david@team-reflex.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the LICENSE.md file.
 */

namespace Discord\Parts\Channel;

use Carbon\Carbon;
use Discord\Helpers\Collection;
use Discord\Parts\Part;
use Discord\Parts\User\Member;
use Discord\Parts\User\User;
use React\Promise\Deferred;

/**
 * A message which is posted to a Discord text channel.
 *
 * @property string                         $id               The unique identifier of the message.
 * @property \Discord\Parts\Channel\Channel $channel          The channel that the message was sent in.
 * @property string                         $channel_id       The unique identifier of the channel that the message was went in.
 * @property string                         $content          The content of the message if it is a normal message.
 * @property int                            $type             The type of message.
 * @property Collection[User]               $mentions         A collection of the users mentioned in the message.
 * @property \Discord\Parts\User\Member       $author           The author of the message.
 * @property bool                           $mention_everyone Whether the message contained an @everyone mention.
 * @property Carbon                         $timestamp        A timestamp of when the message was sent.
 * @property Carbon|null                    $edited_timestamp A timestamp of when the message was edited, or null.
 * @property bool                           $tts              Whether the message was sent as a text-to-speech message.
 * @property array                          $attachments      An array of attachment objects.
 * @property Collection[Embed]              $embeds           A collection of embed objects.
 * @property string|null                    $nonce            A randomly generated string that provides verification for the client. Not required.
 * @property Collection[Role]               $mention_roles    A collection of roles that were mentioned in the message.
 * @property bool                           $pinned           Whether the message is pinned to the channel.
 */
class Message extends Part
{
    const TYPE_NORMAL              = 0;
    const TYPE_USER_ADDED          = 1;
    const TYPE_USER_REMOVED        = 2;
    const TYPE_CALL                = 3;
    const TYPE_CHANNEL_NAME_CHANGE = 4;
    const TYPE_CHANNEL_ICON_CHANGE = 5;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'id',
        'channel_id',
        'content',
        'type',
        'mentions',
        'author',
        'mention_everyone',
        'timestamp',
        'edited_timestamp',
        'tts',
        'attachments',
        'embeds',
        'nonce',
        'mention_roles',
        'pinned',
    ];

    /**
     * Replies to the message.
     *
     * @param string $text The text to reply with.
     *
     * @return \React\Promise\Promise
     */
    public function reply($text)
    {
        return $this->channel->sendMessage("{$this->author}, {$text}");
    }
    
    /**
     * React to a message.
     *
     * @param Emoji $emoticon  The emoticon to react with. (example: '👎', custom: ':michael:251127796439449631')
     *
     * @return \React\Promise\Promise
     */
    public function react($emoticon)
    {
    	$deferred = new Deferred();
    	$this->http->put(
    		"channels/{$this->channel->id}/messages/{$this->id}/reactions/{$emoticon}/@me"
    	)->then(
    		function ($response) use ($deferred) {
    			$deferred->resolve($this);
    		},
    		\React\Partial\bind_right($this->reject, $deferred)
    	);
    	return $deferred->promise();
    }
    
    /**
     * Delete a reaction.
     *
     * @param array $settings  (id and emoticon)
     *
     * @return \React\Promise\Promise
     */
    public function deleteReaction($type, $emoticon = null, $id = null)
    {
    	$deferred = new Deferred();
    	
    	$types = ['all', 'me', 'id'];
    	
    	if (in_array($type, $types))
    	{
    		if ($type === 'all')
    		{
    			$url = "channels/{$this->channel->id}/messages/{$this->id}/reactions";
    		}
    		else
    			if ($type === 'me')
    			{
    				$url = "channels/{$this->channel->id}/messages/{$this->id}/reactions/{$emoticon}/@me";
    			}
    		else
    		{
    			$url = "channels/{$this->channel->id}/messages/{$this->id}/reactions/{$emoticon}/{$id}";
    		}
    		
    		$this->http->delete(
    			$url, []
    		)->then(
    			function ($response) use ($deferred) {
    				$deferred->resolve($this);
    			},
    			\React\Partial\bind_right($this->reject, $deferred)
    		);
    	}
    	else
    	{
    		$deferred->reject();
    	}
    	return $deferred->promise();
    }
    
    /**
     * Deletes the message from the channel.
     *
     * @return \React\Promise\Promise
     */
    public function delete()
    {
        $deferred = new Deferred();
        
        $this->http->delete("channels/{$this->channel->id}/messages/{$this->id}")->then(
            \React\Partial\bind_right($this->resolve, $deferred),
            \React\Partial\bind_right($this->reject, $deferred)
        );
        return $deferred->promise();
    }

    /**
     * Returns the channel attribute.
     *
     * @return Channel The channel the message was sent in.
     */
    public function getChannelAttribute()
    {
        foreach ($this->discord->guilds as $guild) {
        	$channel = $guild->channels->get('id', $this->channel_id);
        	if (!empty($channel)) {
        		return $channel;
        	}
        }

        if ($this->cache->has("pm_channels.{$this->channel_id}")) {
            return $this->cache->get("pm_channels.{$this->channel_id}");
        }

        return $this->factory->create(Channel::class, [
            'id'   => $this->channel_id,
            'type' => Channel::TYPE_DM,
        ], true);
    }

    /**
     * Returns the mention_roles attribute.
     *
     * @return Collection The roles that were mentioned.
     */
    public function getMentionRolesAttribute()
    {
        $roles = new Collection([], 'id');

        foreach ($this->channel->guild->roles as $role) {
            if (array_search($role->id, $this->attributes['mention_roles']) !== false) {
                $roles->push($role);
            }
        }

        return $roles;
    }

    /**
     * Returns the mention attribute.
     *
     * @return Collection The users that were mentioned.
     */
    public function getMentionsAttribute()
    {
        $users = new Collection([], 'id');

        foreach ($this->attributes['mentions'] as $mention) {
            $users->push($this->factory->create(User::class, $mention, true));
        }

        return $users;
    }

    /**
     * Returns the author attribute.
     *
     * @return Member|User The member that sent the message. Will return a User object if it is a PM.
     */
    public function getAuthorAttribute()
    {
        if ($this->channel->type != Channel::TYPE_TEXT) {
            return $this->factory->create(User::class, $this->attributes['author'], true);
        }

        return $this->channel->guild->members->get('id', $this->attributes['author']->id);
    }

    /**
     * Returns the embed attribute.
     *
     * @return Collection A collection of embeds.
     */
    public function getEmbedsAttribute()
    {
        $embeds = new Collection();

        foreach ($this->attributes['embeds'] as $embed) {
            $embeds->push($this->factory->create(Discord\Parts\Embed\Embed::class, $embed, true));
        }

        return $embeds;
    }

    /**
     * Returns the timestamp attribute.
     *
     * @return Carbon The time that the message was sent.
     */
    public function getTimestampAttribute()
    {
        return new Carbon($this->attributes['timestamp']);
    }

    /**
     * Returns the edited_timestamp attribute.
     *
     * @return Carbon|null The time that the message was edited.
     */
    public function getEditedTimestampAttribute()
    {
        if (! $this->attributes['edited_timestamp']) {
            return;
        }

        return new Carbon($this->attributes['edited_timestamp']);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatableAttributes()
    {
        return [
            'content'  => $this->content,
            'mentions' => $this->mentions,
            'tts'      => $this->tts,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatableAttributes()
    {
        return [
            'content'  => $this->content,
            'mentions' => $this->mentions,
        ];
    }
    
    /**
	  * Send message after delay
	  *
	  * @param string $text   Text to send after delay.
	  * @param float  $delay  Delay after text will be sent.
      */
    public function delayedReply($text, $delay)
	{
		$reply = function () use($text) {
			$this->reply($text);
		};
		$this->discord->addTimer($delay, $reply);
	}
}
