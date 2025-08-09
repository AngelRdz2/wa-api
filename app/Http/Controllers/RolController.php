<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolController extends Controller{

    //metodo para la vista principal de roles1
    public function index(){
        $roles = Role::get(["id", "name", "created_at"]);
        $permissions = Permission::get();
        return view('auth.roles.roles', compact('roles', 'permissions'));
    }
    //Metodo para guardar el rol C
    public function store(Request $request){
        if($request->id == null){ // significa que es nuevo
            Role::create([
                "name" => $request->name
            ]);
        }else{//es edicion
            $edit = Role::find($request->id);
            $edit->update([
                "name" => $request->name
            ]);
        }
        return redirect()->back();
    }
    // metodo para eliminar mi rl D
    public function destroy(Request $request){
        if (DB::table("model_has_roles")->where('role_id', $request->id)->exists()) {
            return response()->json('El rol esta asignado a un usuario', 400);
        }


        Role::where('id', $request->id)->delete();
        return "OK";
    }
    public function getPermissions(Request $request){
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        $role = Role::findOrFail($request->role_id);

        // Devolver IDs (porque tu select usa IDs)
        $permissions = $role->permissions()->pluck('id')->toArray();

        return response()->json([
            'permissions' => $permissions
        ]);
    }
    public function addPermissions(Request $request){
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::findOrFail($request->role_id);

        // Convertir IDs a nombres
        $permissionNames = Permission::whereIn('id', $request->permissions)
            ->pluck('name')
            ->toArray();

        $role->syncPermissions($permissionNames);

        return response()->json([
            'message' => 'Permisos asignados correctamente'
        ]);
    }


}
