<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\WhatsappInstance;
use App\Models\Client;
use App\Models\ClientMessage;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'area',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'status' => 'boolean', // ✅ si usas true/false para estado
    ];

    // ----------------------------------------------------------------------
    // RELACIONES
    // ----------------------------------------------------------------------

    public function whatsappInstances()
    {
        return $this->belongsToMany(
            WhatsappInstance::class,
            'instance_user',
            'user_id',
            'whatsapp_instance_id'
        );
    }

    public function clients()
    {
        return $this->belongsToMany(
            Client::class,
            'user_client',
            'user_id',
            'client_id'
        );
    }

    public function clientMessages()
    {
        return $this->hasMany(ClientMessage::class, 'user_id');
    }

    // ----------------------------------------------------------------------
    // HELPERS PERSONALIZADOS
    // ----------------------------------------------------------------------

    public function isSuperAdmin()
    {
        return $this->email === 'supervisotiendas@gmail.com';
    }

    public function esAdmin()
    {
        return $this->hasRole('admin');
    }

    public function esEncargado()
    {
        return $this->hasRole('encargado');
    }

    public function esEncargadoDe(Client $client)
    {
        return $this->clients->contains($client);
    }
}
