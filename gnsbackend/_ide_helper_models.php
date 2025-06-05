<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @mixin IdeHelperBulkProgress
 * @property int $id
 * @property int $user_id
 * @property int $total
 * @property int $sent
 * @property int $errors
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkProgress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkProgress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkProgress query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkProgress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkProgress whereErrors($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkProgress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkProgress whereSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkProgress whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkProgress whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkProgress whereUserId($value)
 */
	class BulkProgress extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @mixin IdeHelperEnvioProgresso
 * @property int $id
 * @property int $user_id
 * @property int $total
 * @property int $enviadas
 * @property int $totalLote
 * @property int $visto
 * @property string|null $Erro
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnvioProgresso newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnvioProgresso newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnvioProgresso query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnvioProgresso whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnvioProgresso whereEnviadas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnvioProgresso whereErro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnvioProgresso whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnvioProgresso whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnvioProgresso whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnvioProgresso whereTotalLote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnvioProgresso whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnvioProgresso whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EnvioProgresso whereVisto($value)
 */
	class EnvioProgresso extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @mixin IdeHelperHistoric
 * @property int $id
 * @property int $user_id
 * @property string $contact
 * @property string $status
 * @property string|null $name
 * @property string|null $errorType
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Historic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Historic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Historic query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Historic whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Historic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Historic whereErrorType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Historic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Historic whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Historic whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Historic whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Historic whereUserId($value)
 */
	class Historic extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @mixin IdeHelperInstances
 * @property int $id
 * @property int $user_id
 * @property string $instance_id
 * @property string $token
 * @property int $connected
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Instances newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Instances newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Instances query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Instances whereConnected($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Instances whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Instances whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Instances whereInstanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Instances whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Instances whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Instances whereUserId($value)
 */
	class Instances extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @mixin IdeHelperUser
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $number
 * @property int $msgLimit
 * @property int $sendedMsg
 * @property string $role
 * @property bool $enabled
 * @property bool $rightNumber
 * @property string|null $lastMessage
 * @property-read \App\Models\Instances|null $instance
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMsgLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRightNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSendedMsg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

