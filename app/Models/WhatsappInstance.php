<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappInstance extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'area', 'instance_id', 'api_key', 'phone'];
    
    // Relación: Una instancia puede tener muchos mensajes (ClientMessage)
    public function clientMessages()
    {
        return $this->hasMany(ClientMessage::class);
    }
    // ✅ RELACIÓN INVERSA: Usuarios que manejan esta instancia
    public function users()
    {
        return $this->belongsToMany(User::class, 'instance_user', 'whatsapp_instance_id', 'user_id');
    }
}